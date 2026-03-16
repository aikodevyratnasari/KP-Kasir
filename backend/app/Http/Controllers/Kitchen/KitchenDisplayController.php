<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\KitchenOrder;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitchenDisplayController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request): View
    {
        $storeId = $request->get('_store_id');
        $orders  = Order::forStore($storeId)
            ->whereIn('status', ['pending', 'cooking'])
            ->whereNotNull('sent_to_kitchen_at')
            ->with('items', 'table', 'kitchenOrder')
            ->oldest('sent_to_kitchen_at')
            ->get();

        return view('kitchen.display', compact('orders'));
    }

    /** Dapur mulai masak: pending → cooking */
    public function start(KitchenOrder $kitchenOrder): RedirectResponse
    {
        abort_if($kitchenOrder->status !== 'queued', 422, 'Pesanan sudah diproses.');
        $this->orderService->updateStatus($kitchenOrder->order, 'cooking', auth()->id());
        return back()->with('success', "Order #{$kitchenOrder->order->order_number} mulai dimasak.");
    }

    /** Dapur selesai masak: cooking → ready */
    public function ready(KitchenOrder $kitchenOrder): RedirectResponse
    {
        abort_if($kitchenOrder->status !== 'cooking', 422, 'Pesanan belum dimasak.');
        $this->orderService->updateStatus($kitchenOrder->order, 'ready', auth()->id());
        return back()->with('success', "Order #{$kitchenOrder->order->order_number} siap disajikan!");
    }

    /** Poll untuk auto-refresh data (JSON) */
    public function poll(Request $request): JsonResponse
    {
        $storeId = $request->get('_store_id');
        $orders  = Order::forStore($storeId)
            ->whereIn('status', ['pending', 'cooking'])
            ->whereNotNull('sent_to_kitchen_at')
            ->with('items', 'table', 'kitchenOrder')
            ->oldest('sent_to_kitchen_at')
            ->get()
            ->map(fn($o) => [
                'id'              => $o->id,
                'order_number'    => $o->order_number,
                'table'           => $o->table?->number,
                'order_type'      => $o->order_type,
                'status'          => $o->status,
                'waiting_color'   => $o->kitchenOrder?->waitingColor(),
                'waiting_minutes' => $o->kitchenOrder?->waitingMinutes(),
                'items'           => $o->items->map(fn($i) => [
                    'name'  => $i->product_name,
                    'qty'   => $i->quantity,
                    'notes' => $i->special_notes,
                ]),
            ]);

        return response()->json($orders);
    }
}