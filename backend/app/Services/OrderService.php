<?php
// ============================================================
// FILE: app/Services/OrderService.php
// PERUBAHAN:
//   1. resolveItems() — tambahkan support bundle_id.
//      Saat item adalah bundle, expand menjadi OrderItem per
//      produk dalam bundle dengan harga proporsional.
//   2. cancel() — payment 'pending' → cancelled, paid → refunded.
// ============================================================

namespace App\Services;

use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Models\BundlePackage;
use App\Models\KitchenOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Reservation;
use App\Models\Store;
use App\Models\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private StockService $stock) {}

    public function create(array $data, int $storeId): Order
    {
        return DB::transaction(function () use ($data, $storeId) {

            $store   = Store::select('id', 'tax_rate')->find($storeId);
            $taxRate = $store ? (float) $store->tax_rate : 0;

            $items   = $this->resolveItems($data['items']);

            $subtotal  = collect($items)->sum(fn($i) => $i['unit_price'] * $i['quantity']);
            $taxAmount = round($subtotal * $taxRate / 100, 2);
            $total     = $subtotal + $taxAmount;

            $customerName = ! empty($data['customer_name']) ? trim($data['customer_name']) : null;
            if (
                empty($customerName) &&
                ($data['order_type'] ?? '') === 'dine_in' &&
                ! empty($data['table_id'])
            ) {
                $activeReservation = Reservation::where('table_id', $data['table_id'])
                    ->where('status', 'active')
                    ->first();
                if ($activeReservation) {
                    $customerName = $activeReservation->customer_name;
                }
            }

            $order = Order::create([
                'store_id'      => $storeId,
                'cashier_id'    => Auth::id(),
                'table_id'      => $data['table_id'] ?? null,
                'order_number'  => $this->generateOrderNumber($storeId),
                'order_type'    => $data['order_type'],
                'status'        => 'pending',
                'subtotal'      => $subtotal,
                'tax_rate'      => $taxRate,
                'tax_amount'    => $taxAmount,
                'total_amount'  => $total,
                'notes'         => $data['notes'] ?? null,
                'customer_name' => $customerName,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id'        => $order->id,
                    'product_id'      => $item['product_id'],
                    'variant_id'      => $item['variant_id'] ?? null,
                    'bundle_id'       => $item['bundle_id'] ?? null,
                    'product_name'    => $item['product_name'],
                    'variant_name'    => $item['variant_name'] ?? null,
                    'unit_price'      => $item['unit_price'],
                    'quantity'        => $item['quantity'],
                    'subtotal'        => $item['unit_price'] * $item['quantity'],
                    'special_notes'   => $item['special_notes'] ?? null,
                    'original_price'  => $item['original_price'],
                    'discount_amount' => $item['discount_amount'],
                    'discount_label'  => $item['discount_label'],
                ]);
            }

            if (($data['order_type'] ?? '') === 'dine_in' && ! empty($data['table_id'])) {
                Table::where('id', $data['table_id'])->update(['status' => 'occupied']);

                Reservation::where('table_id', $data['table_id'])
                    ->where('status', 'active')
                    ->update([
                        'status'        => 'converted',
                        'cancelled_at'  => now(),
                        'cancel_reason' => 'Pesanan telah dibuat.',
                    ]);
            }

            KitchenOrder::create([
                'order_id'  => $order->id,
                'status'    => 'waiting_payment',
                'queued_at' => now(),
            ]);

            $this->stock->deductForOrder($order->load('items.product'));

            event(new OrderCreated($order));

            return $order->load('items', 'table', 'cashier');
        });
    }

    public function update(Order $order, array $data): Order
    {
        abort_if(! $order->isPending(), 422, 'Hanya pesanan dengan status Pending yang dapat diubah.');

        return DB::transaction(function () use ($order, $data) {
            $this->stock->restoreForOrder($order);
            $order->items()->delete();

            $store   = Store::select('id', 'tax_rate')->find($order->store_id);
            $taxRate = $store ? (float) $store->tax_rate : (float) $order->tax_rate;

            $items   = $this->resolveItems($data['items']);

            $subtotal  = collect($items)->sum(fn($i) => $i['unit_price'] * $i['quantity']);
            $taxAmount = round($subtotal * $taxRate / 100, 2);
            $total     = $subtotal + $taxAmount;

            $order->update([
                'subtotal'     => $subtotal,
                'tax_rate'     => $taxRate,
                'tax_amount'   => $taxAmount,
                'total_amount' => $total,
                'notes'        => $data['notes'] ?? $order->notes,
                'customer_name' => array_key_exists('customer_name', $data)
                    ? ($data['customer_name'] ?: null)
                    : $order->customer_name,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id'        => $order->id,
                    'product_id'      => $item['product_id'],
                    'variant_id'      => $item['variant_id'] ?? null,
                    'bundle_id'       => $item['bundle_id'] ?? null,
                    'product_name'    => $item['product_name'],
                    'variant_name'    => $item['variant_name'] ?? null,
                    'unit_price'      => $item['unit_price'],
                    'quantity'        => $item['quantity'],
                    'subtotal'        => $item['unit_price'] * $item['quantity'],
                    'special_notes'   => $item['special_notes'] ?? null,
                    'original_price'  => $item['original_price'],
                    'discount_amount' => $item['discount_amount'],
                    'discount_label'  => $item['discount_label'],
                ]);
            }

            $this->stock->deductForOrder($order->fresh()->load('items.product'));

            return $order->fresh()->load('items', 'table', 'cashier');
        });
    }

    public function cancel(Order $order, string $reason, int $cancelledBy): Order
    {
        abort_if($order->isCompleted(), 422, 'Pesanan yang sudah selesai tidak dapat dibatalkan.');
        abort_if($order->isCancelled(), 422, 'Pesanan sudah dibatalkan.');

        $kitchenOrder = $order->kitchenOrder;
        if ($kitchenOrder && in_array($kitchenOrder->status, ['cooking', 'ready'])) {
            abort(422, 'Pesanan tidak dapat dibatalkan karena dapur sudah mulai memasak.');
        }

        return DB::transaction(function () use ($order, $reason, $cancelledBy) {
            $old = $order->status;

            $order->update([
                'status'        => 'cancelled',
                'cancel_reason' => $reason,
                'cancelled_by'  => $cancelledBy,
                'cancelled_at'  => now(),
            ]);

            if ($order->table_id) {
                Table::where('id', $order->table_id)->update(['status' => 'available']);
            }

            if ($order->kitchenOrder) {
                $order->kitchenOrder->update(['status' => 'cancelled']);
            }

            // Refund payment yang sudah paid
            $paidPayments = $order->payments()->where('status', 'paid')->get();
            foreach ($paidPayments as $payment) {
                $payment->update([
                    'status'        => 'refunded',
                    'refund_amount' => $payment->amount,
                    'refund_reason' => 'Pesanan dibatalkan: ' . $reason,
                    'refunded_at'   => now(),
                    'refunded_by'   => $cancelledBy,
                ]);
            }

            // Cancel payment yang masih pending
            $order->payments()->where('status', 'pending')->update([
                'status' => 'cancelled',
            ]);

            $this->stock->restoreForOrder($order->load('items.product'));
            event(new OrderStatusChanged($order, $old, 'cancelled'));

            return $order->fresh();
        });
    }

    public function updateStatus(Order $order, string $newStatus, int $userId): Order
    {
        $transitions = [
            'pending'  => 'cooking',
            'cooking'  => 'ready',
            'ready'    => 'completed',
        ];

        abort_if(
            ($transitions[$order->status] ?? null) !== $newStatus,
            422,
            "Tidak dapat pindah dari {$order->status} ke {$newStatus}."
        );

        abort_if(
            $newStatus === 'cooking' && ! $order->isFullyPaid(),
            422,
            'Pesanan harus lunas sebelum mulai dimasak.'
        );

        $old        = $order->status;
        $timestamps = ['cooking' => 'cooking_at', 'ready' => 'ready_at', 'completed' => 'completed_at'];

        $updateData = ['status' => $newStatus];
        if (isset($timestamps[$newStatus])) {
            $updateData[$timestamps[$newStatus]] = now();
        }

        $order->update($updateData);

        $kitchenStatMap = ['cooking' => 'cooking', 'ready' => 'ready'];
        if (isset($kitchenStatMap[$newStatus])) {
            $kitchenUpdate = ['status' => $kitchenStatMap[$newStatus]];
            if ($newStatus === 'cooking') {
                $kitchenUpdate['cooking_started_at'] = now();
                $kitchenUpdate['started_by']         = $userId;
            } elseif ($newStatus === 'ready') {
                $kitchenUpdate['ready_at']     = now();
                $kitchenUpdate['completed_by'] = $userId;
            }
            $order->kitchenOrder?->update($kitchenUpdate);
        }

        if ($newStatus === 'completed' && $order->table_id) {
            Table::where('id', $order->table_id)->update(['status' => 'available']);
        }

        event(new OrderStatusChanged($order, $old, $newStatus));

        return $order->fresh();
    }

    public function transferTable(Order $order, int $newTableId): Order
    {
        abort_if($order->isCompleted() || $order->isCancelled(), 422, 'Tidak dapat memindahkan pesanan ini.');

        DB::transaction(function () use ($order, $newTableId) {
            if ($order->table_id) {
                Table::where('id', $order->table_id)->update(['status' => 'available']);
            }
            Table::where('id', $newTableId)->update(['status' => 'occupied']);
            $order->update(['table_id' => $newTableId]);
        });

        return $order->fresh();
    }

    /**
     * Resolve raw items dari request menjadi array siap simpan ke OrderItem.
     *
     * Mendukung dua jenis item:
     *   1. Produk biasa  → harus ada product_id
     *   2. Bundle        → harus ada bundle_id, di-expand per produk dalam bundle
     *      dengan harga proporsional (harga bundle dibagi sesuai bobot normal price).
     */
    private function resolveItems(array $rawItems): array
    {
        $resolved = [];

        foreach ($rawItems as $item) {
            // ── Bundle item ───────────────────────────────────────────────
            if (! empty($item['bundle_id'])) {
                $bundle = BundlePackage::with('items.product', 'items.variant')->find($item['bundle_id']);
                abort_if(! $bundle, 422, 'Paket bundling tidak ditemukan.');
                abort_if(! $bundle->isCurrentlyActive(), 422, "Paket bundling '{$bundle->name}' sudah tidak aktif.");

                $qty         = (int) ($item['quantity'] ?? 1);
                $bundlePrice = (float) $bundle->bundle_price;
                $normalTotal = (float) $bundle->items->sum(fn($i) => (float) $i->product->price * $i->quantity);

                // Setiap produk dalam bundle disimpan sebagai OrderItem terpisah
                // dengan harga proporsional berdasarkan bobot harga normal masing-masing.
                foreach ($bundle->items as $bundleItem) {
                    $productNormalPrice = (float) $bundleItem->product->price;
                    $itemNormalTotal    = $productNormalPrice * $bundleItem->quantity;

                    // Harga proporsional: harga_bundle × (bobot item / total normal)
                    $ratio     = $normalTotal > 0 ? $itemNormalTotal / $normalTotal : 1;
                    $unitPrice = $normalTotal > 0
                        ? round($bundlePrice * $ratio / $bundleItem->quantity, 2)
                        : $productNormalPrice;

                    $discountAmount = round($productNormalPrice - $unitPrice, 2);
                    $discountLabel  = "Paket: {$bundle->name}";

                    $variantName = null;
                    if ($bundleItem->variant) {
                        $variantName = $bundleItem->variant->name;
                    }

                    $resolved[] = [
                        'product_id'      => $bundleItem->product_id,
                        'product_name'    => $bundleItem->product->name,
                        'variant_id'      => $bundleItem->product_variant_id,
                        'variant_name'    => $variantName,
                        'bundle_id'       => $bundle->id,
                        'original_price'  => round($productNormalPrice, 2),
                        'discount_amount' => max(0, $discountAmount),
                        'discount_label'  => $discountLabel,
                        'unit_price'      => round($unitPrice, 2),
                        'quantity'        => $bundleItem->quantity * $qty,
                        'special_notes'   => $item['special_notes'] ?? null,
                    ];
                }

                continue;
            }

            // ── Produk biasa ──────────────────────────────────────────────
            $product = Product::with('discounts')->findOrFail($item['product_id']);
            abort_if($product->isOutOfStock() && $product->track_stock, 422, "{$product->name} habis.");

            $originalPrice = (float) $product->price;
            $unitPrice     = $originalPrice;
            $variantId     = $item['variant_id'] ?? null;
            $variantName   = null;

            if ($variantId) {
                $variant = ProductVariant::find($variantId);
                if ($variant) {
                    $unitPrice     += (float) $variant->price_adjustment;
                    $originalPrice += (float) $variant->price_adjustment;
                    $variantName    = $variant->name;
                }
            }

            $discountAmount = 0;
            $discountLabel  = null;
            $activeDiscount = $product->discounts->first(fn($d) => $d->isCurrentlyActive());

            if ($activeDiscount) {
                $discounted     = $activeDiscount->discountedPrice($unitPrice);
                $discountAmount = round($unitPrice - $discounted, 2);
                $unitPrice      = $discounted;
                $discountLabel  = $activeDiscount->type === 'percentage'
                    ? $activeDiscount->name . ' (' . number_format($activeDiscount->value, 0) . '% OFF)'
                    : $activeDiscount->name . ' (Rp ' . number_format($activeDiscount->value, 0, ',', '.') . ' OFF)';
            }

            $resolved[] = [
                'product_id'      => $product->id,
                'product_name'    => $product->name,
                'variant_id'      => $variantId,
                'variant_name'    => $variantName,
                'bundle_id'       => null,
                'original_price'  => round($originalPrice, 2),
                'discount_amount' => $discountAmount,
                'discount_label'  => $discountLabel,
                'unit_price'      => round($unitPrice, 2),
                'quantity'        => $item['quantity'],
                'special_notes'   => $item['special_notes'] ?? null,
            ];
        }

        return $resolved;
    }

    private function generateOrderNumber(int $storeId): string
    {
        $date   = now()->format('Ymd');
        $prefix = "ORD-{$date}-";

        $lockKey = crc32("order_number_{$storeId}_{$date}");
        \DB::statement("SELECT pg_advisory_xact_lock({$lockKey})");

        $last = Order::where('order_number', 'like', $prefix . '%')
            ->where('store_id', $storeId)
            ->orderByDesc('order_number')
            ->value('order_number');

        $seq = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    public static function canCancel(Order $order): bool
    {
        if ($order->isCompleted() || $order->isCancelled()) {
            return false;
        }
        $kitchen = $order->kitchenOrder;
        if ($kitchen && in_array($kitchen->status, ['cooking', 'ready'])) {
            return false;
        }
        return true;
    }
}
