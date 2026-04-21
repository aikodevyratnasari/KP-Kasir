<?php

namespace App\Services;

use App\Events\PaymentProcessed;
use App\Models\KitchenOrder;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function process(Order $order, array $data): Payment
    {
        return DB::transaction(function () use ($order, $data) {
            $amount = (float) $data['amount'];

            $change = null;
            if ($data['payment_method'] === 'cash') {
                $received = (float) ($data['amount_received'] ?? 0);
                if ($received < $amount) {
                    throw ValidationException::withMessages([
                        'amount_received' => 'Jumlah yang diterima kurang dari total pembayaran.',
                    ]);
                }
                $change = $received - $amount;
            }

            $payment = Payment::create([
                'order_id'         => $order->id,
                'cashier_id'       => Auth::id(),
                'payment_method'   => $data['payment_method'],
                'ewallet_type'     => $data['ewallet_type']     ?? null,
                'card_type'        => $data['card_type']        ?? null,
                'card_last_four'   => $data['card_last_four']   ?? null,
                'approval_code'    => $data['approval_code']    ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'amount'           => $amount,
                'amount_received'  => $data['amount_received']  ?? null,
                'change_amount'    => $change,
                'status'           => 'paid',
            ]);

            // Jika sudah lunas → aktifkan ke dapur
            $freshOrder = $order->fresh();
            if ($freshOrder->isFullyPaid()) {
                $freshOrder->update(['sent_to_kitchen_at' => now()]);

                if ($freshOrder->kitchenOrder) {
                    $freshOrder->kitchenOrder->update([
                        'status'    => 'queued',
                        'queued_at' => now(),
                    ]);
                } else {
                    KitchenOrder::create([
                        'order_id'  => $freshOrder->id,
                        'status'    => 'queued',
                        'queued_at' => now(),
                    ]);
                }
            }

            event(new PaymentProcessed($payment));

            return $payment;
        });
    }

    /**
     * Proses refund pembayaran secara manual (dari halaman laporan/manager).
     *
     * Aturan:
     * 1. Payment HARUS berstatus 'paid' — tidak bisa refund yang belum lunas atau sudah direfund.
     * 2. Jumlah refund tidak boleh melebihi amount pembayaran.
     * 3. Jika semua payment pada order sudah direfund → order dikembalikan ke 'cancelled'
     *    dan meja dibebaskan.
     * 4. Stok dikembalikan jika order ikut dibatalkan.
     *
     * Catatan: Refund otomatis saat cancel order ditangani di OrderService::cancel().
     */
    public function refund(Payment $payment, float $amount, string $reason): Payment
    {
        abort_if(
            $payment->status !== 'paid',
            422,
            $payment->status === 'refunded'
                ? 'Pembayaran ini sudah pernah dikembalikan.'
                : 'Hanya pembayaran berstatus Lunas yang dapat di-refund.'
        );
        abort_if($amount > (float) $payment->amount, 422, 'Jumlah refund melebihi jumlah pembayaran awal.');

        return DB::transaction(function () use ($payment, $amount, $reason) {
            $payment->update([
                'status'        => 'refunded',
                'refund_amount' => $amount,
                'refund_reason' => $reason,
                'refunded_at'   => now(),
                'refunded_by'   => Auth::id(),
            ]);

            // Cek apakah semua payment pada order sudah di-refund / tidak ada yang paid
            $order     = $payment->order->fresh(['payments', 'table', 'kitchenOrder']);
            $stillPaid = $order->payments->where('status', 'paid')->sum('amount');

            // Jika tidak ada sisa pembayaran lunas dan order belum cancelled/completed
            if ($stillPaid <= 0 && ! $order->isCancelled() && ! $order->isCompleted()) {
                $order->update([
                    'status'        => 'cancelled',
                    'cancel_reason' => 'Refund: ' . $reason,
                    'cancelled_by'  => Auth::id(),
                    'cancelled_at'  => now(),
                ]);

                // Bebaskan meja
                if ($order->table_id) {
                    Table::where('id', $order->table_id)->update(['status' => 'available']);
                }

                // Cancel kitchen order jika belum cooking/ready
                if ($order->kitchenOrder && ! in_array($order->kitchenOrder->status, ['cooking', 'ready'])) {
                    $order->kitchenOrder->update(['status' => 'cancelled']);
                }

                // Kembalikan stok
                app(\App\Services\StockService::class)->restoreForOrder($order->load('items.product'));
            }

            return $payment->fresh();
        });
    }
}