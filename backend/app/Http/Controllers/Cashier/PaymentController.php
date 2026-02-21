<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\ProcessPaymentRequest;
use App\Http\Requests\Payment\RefundPaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\ActivityLogService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function create(Order $order): View
    {
        $order->load('items', 'payments', 'table');
        return view('cashier.payments.create', compact('order'));
    }

    public function store(ProcessPaymentRequest $request, Order $order): RedirectResponse
    {
        $payment = $this->paymentService->process($order, $request->validated());
        ActivityLogService::logCreated($payment, ['method' => $payment->payment_method, 'amount' => $payment->amount]);
        return redirect()->route('cashier.receipts.show', $payment)->with('success', 'Pembayaran berhasil diproses.');
    }

    public function history(Request $request): View
    {
        $storeId  = $request->get('_store_id');
        $payments = Payment::whereHas('order', fn($q) => $q->where('store_id', $storeId))
            ->with('order', 'cashier')
            ->when($request->method,    fn($q, $m) => $q->where('payment_method', $m))
            ->when($request->cashier,   fn($q, $c) => $q->where('cashier_id', $c))
            ->when($request->search,    fn($q, $s) => $q->whereHas('order', fn($q2) => $q2->where('order_number', 'like', "%{$s}%")))
            ->when($request->date_from, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to,   fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('cashier.payments.history', compact('payments'));
    }

    public function refund(RefundPaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->authorize('refund', $payment);
        $payment = $this->paymentService->refund($payment, $request->refund_amount, $request->refund_reason);
        ActivityLogService::log('payment_refunded', $payment, description: "Refund Rp{$request->refund_amount} for payment #{$payment->id}");
        return back()->with('success', 'Pengembalian dana berhasil diproses.');
    }
}