<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Models\Category;
use App\Models\Order;
use App\Models\Reservation;
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
            ->with('cashier', 'table', 'payments') // ← tambah 'payments' agar badge Refund/Lunas/Sebagian tidak N+1
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
        $storeId = $request->get('_store_id');

        $categories = Category::where('store_id', $storeId)
            ->with(['products' => function ($q) {
                $q->where('is_available', true)
                  ->with([
                      'variants' => fn($q2) => $q2->where('is_available', true)->orderBy('sort_order'),
                      'discounts',
                  ]);
            }])
            ->where('is_active', true)
            ->get();

        $tables = Table::where('store_id', $storeId)->available()->orderBy('number')->get();

        $prefilledCustomerName = null;
        if ($request->table) {
            $selectedTable = Table::where('store_id', $storeId)->find($request->table);
            if ($selectedTable && !$tables->contains('id', $selectedTable->id)) {
                $tables = $tables->push($selectedTable)->sortBy('number')->values();
            }
            $activeReservation = Reservation::where('table_id', $request->table)->where('status', 'active')->first();
            if ($activeReservation) $prefilledCustomerName = $activeReservation->customer_name;
        }

        return view('cashier.orders.create', compact('categories', 'tables', 'prefilledCustomerName'));
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = $this->orderService->create($request->validated(), $request->get('_store_id'));
        ActivityLogService::logCreated($order, ['order_number' => $order->order_number]);
        return redirect()->route('cashier.orders.show', $order)
            ->with('success', "Pesanan #{$order->order_number} berhasil dibuat. Silakan proses pembayaran.");
    }

    public function show(Order $order): View
    {
        $order->load('items.product', 'items.variant', 'table', 'cashier', 'payments', 'kitchenOrder');
        return view('cashier.orders.show', compact('order'));
    }

    public function edit(Order $order): View
    {
        abort_if(! $order->isPending(), 403, 'Hanya pesanan Pending yang dapat diubah.');
        $storeId = auth()->user()->store_id;

        $categories = Category::where('store_id', $storeId)
            ->with(['products' => function ($q) {
                $q->where('is_available', true)
                  ->with([
                      'variants'  => fn($q2) => $q2->where('is_available', true)->orderBy('sort_order'),
                      'discounts',
                  ]);
            }])
            ->where('is_active', true)
            ->get();

        $tables = Table::where('store_id', $storeId)->available()->orderBy('number')->get();
        if ($order->table_id) {
            $t = Table::find($order->table_id);
            if ($t && !$tables->contains($t)) $tables = $tables->push($t)->sortBy('number')->values();
        }

        $order->load('items.variant');

        return view('cashier.orders.edit', compact('order', 'categories', 'tables'));
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->update($order, $request->validated());
        ActivityLogService::log('order_updated', $order, description: "Order #{$order->order_number} modified.");
        return redirect()->route('cashier.orders.show', $order)->with('success', 'Pesanan berhasil diperbarui.');
    }

    public function cancel(CancelOrderRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->cancel($order, $request->cancel_reason, auth()->id());
        return redirect()->route('cashier.orders.index')->with('success', "Pesanan #{$order->order_number} dibatalkan.");
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate(['status' => 'required|in:cooking,ready,completed']);
        $order = $this->orderService->updateStatus($order, $request->status, auth()->id());
        return response()->json(['success' => true, 'status' => $order->status]);
    }

    public function complete(Order $order): RedirectResponse
    {
        abort_if($order->status !== 'ready', 422, 'Pesanan belum siap untuk diselesaikan.');
        $this->orderService->updateStatus($order, 'completed', auth()->id());
        ActivityLogService::log('order_completed', $order, description: "Order #{$order->order_number} selesai, meja dikosongkan.");
        return redirect()->route('cashier.tables.index')
            ->with('success', "Pesanan #{$order->order_number} selesai. Meja {$order->table?->number} kembali tersedia.");
    }

    /**
     * Poll endpoint untuk live update tabel pesanan.
     * Mengembalikan status terbaru beserta info pembayaran (is_paid, has_refund, has_partial).
     */
    public function poll(Request $request): JsonResponse
    {
        $storeId = $request->get('_store_id');

        $orders = Order::forStore($storeId)
            ->with('payments')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn(Order $o) => [
                'id'          => $o->id,
                'status'      => $o->status,
                'is_paid'     => $o->isFullyPaid(),
                'has_refund'  => $o->payments->contains('status', 'refunded'),
                'has_partial' => $o->payments->contains('status', 'paid') && ! $o->isFullyPaid(),
            ]);

        return response()->json($orders);
    }
}