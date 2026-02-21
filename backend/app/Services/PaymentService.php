<?php

namespace App\Services;

use App\Events\PaymentProcessed;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Process a single payment (or one leg of a split bill).
     *
     * @param array $data {payment_method, amount, amount_received?, card_type?, card_last_four?,
     *                     approval_code?, ewallet_type?, reference_number?}
     */
    public function process(Order $order, array $data): Payment
    {
        abort_if($order->isCancelled(), 422, 'Pesanan telah dibatalkan.');
        abort_if($order->isCompleted() && $order->isFullyPaid(), 422, 'Pesanan sudah lunas.');

        return DB::transaction(function () use ($order, $data) {
            $amount = (float) $data['amount'];

            $change = null;
            if ($data['payment_method'] === 'cash') {
                $received = (float) ($data['amount_received'] ?? 0);
                abort_if($received < $amount, 422, 'Jumlah yang diterima kurang dari total pembayaran.');
                $change = $received - $amount;
            }

            $payment = Payment::create([
                'order_id'         => $order->id,
                'cashier_id'       => Auth::id(),
                'payment_method'   => $data['payment_method'],
                'ewallet_type'     => $data['ewallet_type'] ?? null,
                'card_type'        => $data['card_type'] ?? null,
                'card_last_four'   => $data['card_last_four'] ?? null,
                'approval_code'    => $data['approval_code'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'amount'           => $amount,
                'amount_received'  => $data['amount_received'] ?? null,
                'change_amount'    => $change,
                'status'           => 'paid',
            ]);

            // Mark order completed if fully paid
            if ($order->fresh()->isFullyPaid()) {
                $order->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);
            }

            event(new PaymentProcessed($payment));

            return $payment;
        });
    }

    /**
     * Refund a payment (full or partial). Only within 24 hours.
     */
    public function refund(Payment $payment, float $amount, string $reason): Payment
    {
        abort_if($payment->isRefunded(), 422, 'Pembayaran sudah dikembalikan.');
        abort_if($payment->created_at->lt(now()->subDay()), 422, 'Pengembalian dana hanya bisa dilakukan dalam 24 jam.');
        abort_if($amount > (float) $payment->amount, 422, 'Jumlah refund melebihi pembayaran awal.');

        $payment->update([
            'status'        => 'refunded',
            'refund_amount' => $amount,
            'refund_reason' => $reason,
            'refunded_at'   => now(),
            'refunded_by'   => Auth::id(),
        ]);

        return $payment->fresh();
    }
}