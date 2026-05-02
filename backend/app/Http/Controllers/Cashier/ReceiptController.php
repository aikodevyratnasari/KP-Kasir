<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Mail\ReceiptMail;
use App\Models\Payment;           
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function show(Payment $payment): View
    {
        $payment->load('order.items.bundlePackage.items.product', 'order.items.bundlePackage.items.variant', 'order.table', 'cashier', 'order.cashier', 'order.store');
        return view('cashier.receipts.show', compact('payment'));
    }

    public function print(Payment $payment): View
    {
        $payment->load('order.items.bundlePackage.items.product', 'order.items.bundlePackage.items.variant', 'order.table', 'order.store', 'order.cashier');
        return view('cashier.receipts.print', compact('payment'));
    }

    /**
     * Kirim struk ke email.
     * POST /cashier/receipts/{payment}/email
     */
    public function sendEmail(Request $request, Payment $payment): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:100'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $payment->load('order.items.bundlePackage.items.product', 'order.items.bundlePackage.items.variant', 'order.table', 'order.store', 'order.cashier');

        // $recipientName = $payment->order->customer_name ?? '';

        try {
            Mail::to($request->email)->send(new ReceiptMail($payment));
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim email: ' . $e->getMessage()], 500);
        }
    }

}
