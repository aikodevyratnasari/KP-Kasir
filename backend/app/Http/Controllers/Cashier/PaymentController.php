<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\ProcessPaymentRequest;
use App\Http\Requests\Payment\RefundPaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\ActivityLogService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function create(Order $order): View
    {
        abort_if($order->isCancelled(), 403, 'Pesanan ini sudah dibatalkan.');
        abort_if($order->remainingBalance() <= 0, 403, 'Pesanan ini sudah lunas.');

        $order->load('items', 'payments', 'table');
        return view('cashier.payments.create', compact('order'));
    }

    public function store(ProcessPaymentRequest $request, Order $order): RedirectResponse
    {
        $method = $request->payment_method;

        if (in_array($method, ['qris', 'ewallet', 'bank_transfer'])) {
            return $this->redirectToGateway($request, $order);
        }

        $payment = $this->paymentService->process($order, $request->validated());

        ActivityLogService::logCreated($payment, [
            'method' => $payment->payment_method,
            'amount' => $payment->amount,
        ]);

        return redirect()
            ->route('cashier.orders.show', $order)
            ->with('success', 'Pembayaran berhasil diproses.');
    }

    public function initiate(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'method'       => ['required', 'in:qris,ewallet,bank_transfer'],
            'ewallet_type' => ['nullable', 'string'],
        ]);

        if ($request->method === 'ewallet') {
            $request->validate([
                'ewallet_type' => ['required', 'in:GoPay,OVO,Dana,ShopeePay'],
            ]);
        }

        if ($request->method === 'bank_transfer') {
            $request->validate([
                'ewallet_type' => ['required', 'in:bca,bni,bri,mandiri,permata'],
            ]);
        }

        abort_if($order->remainingBalance() <= 0, 422, 'Pesanan sudah lunas.');
        abort_if($order->isCancelled(), 422, 'Pesanan sudah dibatalkan.');

        try {
            $payment = $this->paymentService->initiate(
                $order,
                $request->method,
                $request->ewallet_type,
            );

        return response()->json([
            'success'      => true,
            'payment_id'   => $payment->id,
            'snap_token'   => $payment->snap_token,
            'payment_url'  => $payment->payment_url,
            'qr_string'    => $payment->qr_string,
            'va_number'    => $payment->va_number,
            'bank'         => $payment->bank,
            'method'       => $payment->payment_method,
            'amount'       => $payment->amount,
            // Mandiri: kirim biller_code dan bill_key terpisah
            'biller_code'  => $payment->mandiriBillerCode(),
            'bill_key'     => $payment->mandiriBillKey(),
        ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi payment gateway. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function pollStatus(Payment $payment): JsonResponse
    {
        $confirmed = $this->paymentService->pollGatewayStatus($payment);
        $payment   = $payment->fresh();

        return response()->json([
            'status'    => $payment->status,
            'is_paid'   => $payment->isPaid(),
            'confirmed' => $confirmed,
        ]);
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

        $payment = $this->paymentService->refund(
            $payment,
            (float) $request->refund_amount,
            $request->refund_reason,
        );

        ActivityLogService::log(
            'payment_refunded',
            $payment,
            description: sprintf(
                "Refund Rp%s untuk payment #%d (%s)%s",
                number_format($request->refund_amount, 0, ',', '.'),
                $payment->id,
                $payment->methodLabelShort(),
                $payment->isGateway() ? ' via Midtrans' : ' (manual)',
            )
        );

        return back()->with('success', 'Pengembalian dana berhasil diproses.');
    }

    /**
     * Cancel satu payment pending secara manual (kasir ganti metode / batalkan QR).
     *
     * - Status menjadi 'cancelled' — tidak tercatat sebagai transaksi nyata.
     * - Tidak ada uang yang dikembalikan (pembayaran belum terjadi).
     * - Hanya boleh dilakukan untuk payment milik store kasir yang login.
     * - Baris payment yang di-cancel tidak muncul di laporan dan detail pesanan.
     */
    public function cancelPending(Payment $payment): JsonResponse
    {
        abort_if(
            $payment->order->store_id !== auth()->user()->store_id,
            403,
            'Akses ditolak.'
        );

        $this->paymentService->cancelPendingPayment($payment);

        return response()->json(['success' => true]);
    }

    private function redirectToGateway(Request $request, Order $order): RedirectResponse
    {
        return redirect()
            ->route('cashier.orders.show', $order)
            ->with('info', 'Silakan pilih metode dari halaman detail pesanan.');
    }
}