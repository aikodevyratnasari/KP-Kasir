<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function show(Payment $payment): View
    {
        $payment->load('order.items', 'order.table', 'cashier', 'order.cashier', 'order.store');
        return view('cashier.receipts.show', compact('payment'));
    }

    public function print(Payment $payment): View
    {
        $payment->load('order.items', 'order.table', 'order.store', 'order.cashier');
        return view('cashier.receipts.print', compact('payment'));
    }
}