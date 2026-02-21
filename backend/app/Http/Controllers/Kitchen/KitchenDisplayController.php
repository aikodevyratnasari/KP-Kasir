<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\KitchenOrder;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
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
            ->with('items', 'table', 'kitchenOrder')
            ->oldest()
            ->get();

        return view('kitchen.display', compact('orders'));
    }

    public function start(KitchenOrder $kitchenOrder): JsonResponse
    {
        abort_if($kitchenOrder->status !== 'queued', 422, 'Pesanan sudah diproses.');
        $order = $kitchenOrder->order;
        $this->orderService->updateStatus($order, 'cooking', auth()->id());
        return response()->json(['success' => true, 'order_number' => $order->order_number]);
    }

    public function ready(KitchenOrder $kitchenOrder): JsonResponse
    {
        abort_if($kitchenOrder->status !== 'cooking', 422, 'Pesanan belum dimasak.');
        $order = $kitchenOrder->order;
        $this->orderService->updateStatus($order, 'ready', auth()->id());
        return response()->json(['success' => true, 'order_number' => $order->order_number]);
    }

    public function poll(Request $request): JsonResponse
    {
        $storeId = $request->get('_store_id');
        $orders  = Order::forStore($storeId)
            ->whereIn('status', ['pending', 'cooking'])
            ->with('items', 'table', 'kitchenOrder')
            ->oldest()
            ->get()
            ->map(fn($o) => [
                'id'             => $o->id,
                'order_number'   => $o->order_number,
                'table'          => $o->table?->number,
                'order_type'     => $o->order_type,
                'status'         => $o->status,
                'waiting_color'  => $o->kitchenOrder?->waitingColor(),
                'waiting_minutes'=> $o->kitchenOrder?->waitingMinutes(),
                'items'          => $o->items->map(fn($i) => ['name' => $i->product_name, 'qty' => $i->quantity, 'notes' => $i->special_notes]),
            ]);

        return response()->json($orders);
    }
}