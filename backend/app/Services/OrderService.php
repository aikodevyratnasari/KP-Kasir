<?php

namespace App\Services;

use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Models\KitchenOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private StockService $stock) {}

    public function create(array $data, int $storeId): Order
    {
        return DB::transaction(function () use ($data, $storeId) {

            $taxRate = auth()->user()->store->tax_rate ?? 10;
            $items   = $this->resolveItems($data['items']);

            $subtotal  = collect($items)->sum(fn($i) => $i['unit_price'] * $i['quantity']);
            $taxAmount = round($subtotal * $taxRate / 100, 2);
            $total     = $subtotal + $taxAmount;

            $order = Order::create([
                'store_id'     => $storeId,
                'cashier_id'   => Auth::id(),
                'table_id'     => $data['table_id'] ?? null,
                'order_number' => $this->generateOrderNumber($storeId),
                'order_type'   => $data['order_type'],
                'status'       => 'pending',
                'subtotal'     => $subtotal,
                'tax_rate'     => $taxRate,
                'tax_amount'   => $taxAmount,
                'total_amount' => $total,
                'notes'        => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $item['product_id'],
                    'product_name'  => $item['product_name'],
                    'unit_price'    => $item['unit_price'],
                    'quantity'      => $item['quantity'],
                    'subtotal'      => $item['unit_price'] * $item['quantity'],
                    'special_notes' => $item['special_notes'] ?? null,
                ]);
            }

            // Meja belum occupied saat order dibuat —
            // occupied setelah makanan ready (tamu mulai makan)
            // Tapi jika dine_in, tandai meja "reserved" agar tidak bisa dibuat order lain
            if (($data['order_type'] ?? '') === 'dine_in' && ! empty($data['table_id'])) {
                Table::where('id', $data['table_id'])->update(['status' => 'occupied']);
            }

            // Buat kitchen_order dengan status 'waiting_payment' — belum muncul di dapur
            // queued_at diisi now() karena kolom NOT NULL,
            // akan di-update ulang saat pembayaran lunas
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

            $taxRate = $order->tax_rate;
            $items   = $this->resolveItems($data['items']);

            $subtotal  = collect($items)->sum(fn($i) => $i['unit_price'] * $i['quantity']);
            $taxAmount = round($subtotal * $taxRate / 100, 2);
            $total     = $subtotal + $taxAmount;

            $order->update([
                'subtotal'     => $subtotal,
                'tax_amount'   => $taxAmount,
                'total_amount' => $total,
                'notes'        => $data['notes'] ?? $order->notes,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $item['product_id'],
                    'product_name'  => $item['product_name'],
                    'unit_price'    => $item['unit_price'],
                    'quantity'      => $item['quantity'],
                    'subtotal'      => $item['unit_price'] * $item['quantity'],
                    'special_notes' => $item['special_notes'] ?? null,
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

        return DB::transaction(function () use ($order, $reason, $cancelledBy) {
            $old = $order->status;

            $order->update([
                'status'        => 'cancelled',
                'cancel_reason' => $reason,
                'cancelled_by'  => $cancelledBy,
                'cancelled_at'  => now(),
            ]);

            // Bebaskan meja saat dibatalkan
            if ($order->table_id) {
                Table::where('id', $order->table_id)->update(['status' => 'available']);
            }

            $this->stock->restoreForOrder($order->load('items.product'));
            event(new OrderStatusChanged($order, $old, 'cancelled'));

            return $order->fresh();
        });
    }

    /**
     * Transisi status:
     *  pending  → cooking  : dapur mulai masak
     *  cooking  → ready    : dapur selesai masak (makanan siap, tamu makan di meja)
     *  ready    → completed: kasir confirm meja kosong → meja available kembali
     */
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

        $old = $order->status;

        $timestamps = [
            'cooking'   => 'cooking_at',
            'ready'     => 'ready_at',
            'completed' => 'completed_at',
        ];

        $updateData = ['status' => $newStatus];
        if (isset($timestamps[$newStatus])) {
            $updateData[$timestamps[$newStatus]] = now();
        }

        $order->update($updateData);

        // Sync kitchen_orders
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

        // ready → meja tetap occupied (tamu sedang makan)
        // completed → meja kembali available (tamu sudah pergi)
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

    private function resolveItems(array $rawItems): array
    {
        return collect($rawItems)->map(function ($item) {
            $product = Product::findOrFail($item['product_id']);
            abort_if($product->isOutOfStock() && $product->track_stock, 422, "{$product->name} habis.");

            return [
                'product_id'    => $product->id,
                'product_name'  => $product->name,
                'unit_price'    => $product->price,
                'quantity'      => $item['quantity'],
                'special_notes' => $item['special_notes'] ?? null,
            ];
        })->toArray();
    }

    private function generateOrderNumber(int $storeId): string
    {
        $date   = now()->format('Ymd');
        $prefix = "ORD-{$date}-";

        $last = Order::where('order_number', 'like', $prefix . '%')
            ->where('store_id', $storeId)
            ->orderByDesc('order_number')
            ->value('order_number');

        $seq = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}