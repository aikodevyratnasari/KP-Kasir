<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Models\Category;
use App\Models\Order;
use App\Models\Table;
use App\Services\ActivityLogService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request): View
    {
        $storeId = $request->get('_store_id');
        $orders  = Order::forStore($storeId)
            ->with('cashier', 'table')
            ->when($request->status,     fn($q, $s) => $q->where('status', $s))
            ->when($request->order_type, fn($q, $t) => $q->where('order_type', $t))
            ->when($request->search,     fn($q, $s) => $q->where('order_number', 'like', "%{$s}%"))
            ->when($request->date_from,  fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to,    fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('cashier.orders.index', compact('orders'));
    }

    public function create(Request $request): View
    {
        $storeId    = $request->get('_store_id');
        $categories = Category::where('store_id', $storeId)->with('products')->where('is_active', true)->get();
        $tables     = Table::where('store_id', $storeId)->available()->orderBy('number')->get();
        return view('cashier.orders.create', compact('categories', 'tables'));
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = $this->orderService->create($request->validated(), $request->get('_store_id'));
        ActivityLogService::logCreated($order, ['order_number' => $order->order_number]);
        return redirect()->route('cashier.orders.show', $order)->with('success', "Pesanan #{$order->order_number} berhasil dibuat.");
    }

    public function show(Order $order): View
    {
        $order->load('items.product', 'table', 'cashier', 'payments', 'kitchenOrder');
        return view('cashier.orders.show', compact('order'));
    }

    public function edit(Order $order): View
    {
        abort_if(! $order->isPending(), 403, 'Hanya pesanan Pending yang dapat diubah.');
        $storeId    = auth()->user()->store_id;
        $categories = Category::where('store_id', $storeId)->with('products')->where('is_active', true)->get();
        return view('cashier.orders.edit', compact('order', 'categories'));
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);
        $this->orderService->update($order, $request->validated());
        ActivityLogService::log('order_updated', $order, description: "Order #{$order->order_number} modified.");
        return redirect()->route('cashier.orders.show', $order)->with('success', 'Pesanan berhasil diperbarui.');
    }

    public function cancel(CancelOrderRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);
        $this->orderService->cancel($order, $request->cancel_reason, auth()->id());
        return redirect()->route('cashier.orders.index')->with('success', "Pesanan #{$order->order_number} dibatalkan.");
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $this->authorize('updateStatus', $order);
        $request->validate(['status' => 'required|in:cooking,ready,completed']);
        $order = $this->orderService->updateStatus($order, $request->status, auth()->id());
        return response()->json(['success' => true, 'status' => $order->status, 'order_number' => $order->order_number]);
    }
}