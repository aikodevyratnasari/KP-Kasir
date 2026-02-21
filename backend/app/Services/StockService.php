<?php

namespace App\Services;

use App\Events\LowStockAlert;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Deduct stock for all items in a newly placed order.
     */
    public function deductForOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $item->product;
            if (! $product || ! $product->track_stock) {
                continue;
            }

            $before = $product->stock;
            $change = -$item->quantity;
            $after  = max(0, $before + $change);

            $product->update(['stock' => $after]);

            StockLog::create([
                'product_id'      => $product->id,
                'user_id'         => Auth::id(),
                'order_id'        => $order->id,
                'type'            => 'out',
                'quantity_before' => $before,
                'quantity_change' => $change,
                'quantity_after'  => $after,
                'notes'           => "Order #{$order->order_number}",
            ]);

            // Fire alert if low/out
            if ($product->fresh()->isLowStock() || $product->fresh()->isOutOfStock()) {
                event(new LowStockAlert($product));
            }
        }
    }

    /**
     * Restore stock when an order is cancelled.
     */
    public function restoreForOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $item->product;
            if (! $product || ! $product->track_stock) {
                continue;
            }

            $before = $product->stock;
            $after  = $before + $item->quantity;

            $product->update(['stock' => $after]);

            StockLog::create([
                'product_id'      => $product->id,
                'user_id'         => Auth::id(),
                'order_id'        => $order->id,
                'type'            => 'cancel_restore',
                'quantity_before' => $before,
                'quantity_change' => $item->quantity,
                'quantity_after'  => $after,
                'notes'           => "Cancelled order #{$order->order_number}",
            ]);
        }
    }

    /**
     * Manual adjustment by manager/admin.
     */
    public function adjust(Product $product, int $newQuantity, string $notes = ''): StockLog
    {
        $before = $product->stock;
        $change = $newQuantity - $before;

        $product->update(['stock' => $newQuantity]);

        $log = StockLog::create([
            'product_id'      => $product->id,
            'user_id'         => Auth::id(),
            'order_id'        => null,
            'type'            => 'adjustment',
            'quantity_before' => $before,
            'quantity_change' => $change,
            'quantity_after'  => $newQuantity,
            'notes'           => $notes ?: 'Manual adjustment',
        ]);

        if ($product->fresh()->isLowStock() || $product->fresh()->isOutOfStock()) {
            event(new LowStockAlert($product));
        }

        return $log;
    }
}