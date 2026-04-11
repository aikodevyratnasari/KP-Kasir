<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PollController extends Controller
{
    /**
     * Status semua meja di store.
     * GET /api/poll/tables
     */
    public function tables(Request $request): JsonResponse
    {
        $storeId = $request->get('_store_id');

        $tables = Table::forStore($storeId)
            ->with(['activeOrder', 'activeReservation'])
            ->get()
            ->map(fn($t) => [
                'id'             => $t->id,
                'status'         => $t->status,
                'order_number'   => $t->activeOrder?->order_number,
                'order_id'       => $t->activeOrder?->id,
                'order_status'   => $t->activeOrder?->status,
                'order_total'    => $t->activeOrder
                    ? number_format($t->activeOrder->total_amount, 0, ',', '.')
                    : null,
                // Nilai numerik mentah untuk diformat ulang di JS jika perlu
                'order_total_raw' => $t->activeOrder?->total_amount,
                'is_paid'        => $t->activeOrder?->isFullyPaid() ?? false,
                'reservation'    => $t->activeReservation ? [
                    // FIX: sertakan id agar JS bisa render form Batalkan dengan route yang benar
                    'id'   => $t->activeReservation->id,
                    'name' => $t->activeReservation->customer_name,
                    'time' => \Carbon\Carbon::parse($t->activeReservation->reserved_at)->format('H:i'),
                ] : null,
            ]);

        return response()->json($tables);
    }

    /**
     * Status satu order.
     * GET /api/poll/orders/{order}
     */
    public function order(Request $request, Order $order): JsonResponse
    {
        return response()->json([
            'id'                 => $order->id,
            'status'             => $order->status,
            'is_paid'            => $order->isFullyPaid(),
            'sent_to_kitchen_at' => $order->sent_to_kitchen_at?->format('H:i'),
            'kitchen_status'     => $order->kitchenOrder?->status,
            'remaining_balance'  => $order->remainingBalance(),
        ]);
    }

    /**
     * Daftar order aktif di store.
     * GET /api/poll/orders
     */
    public function orders(Request $request): JsonResponse
    {
        $storeId = $request->get('_store_id');

        $orders = Order::forStore($storeId)
            ->with('table')
            ->whereDate('created_at', today())
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn($o) => [
                'id'            => $o->id,
                'order_number'  => $o->order_number,
                'status'        => $o->status,
                'is_paid'       => $o->isFullyPaid(),
                'table'         => $o->table?->number,
                'customer_name' => $o->customer_name,
            ]);

        return response()->json($orders);
    }

    /**
     * Counter untuk kitchen display (badge di sidebar).
     * GET /api/poll/kitchen
     */
    public function kitchen(Request $request): JsonResponse
    {
        $storeId = $request->get('_store_id');

        $counts = Order::forStore($storeId)
            ->whereNotNull('sent_to_kitchen_at')
            ->whereIn('status', ['pending', 'cooking'])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'queued'  => $counts['pending'] ?? 0,
            'cooking' => $counts['cooking'] ?? 0,
            'total'   => ($counts['pending'] ?? 0) + ($counts['cooking'] ?? 0),
        ]);
    }
}