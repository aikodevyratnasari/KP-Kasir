<?php
// ============================================================
// FILE: app/Services/PaymentService.php
// PERUBAHAN:
//   1. pollGatewayStatus() — perbaiki urutan pengecekan DB vs API
//      Untuk QRIS: gunakan gateway_trx_id → query Midtrans API,
//      tapi jika gagal fallback ke DB (webhook mungkin sudah masuk).
//      Untuk Snap ewallet: karena gateway_trx_id null saat initiate,
//      tambahkan pengecekan DB berdasarkan order_id + method.
//   2. handleWebhook() — pastikan snap_token ewallet bisa dicocokkan
//      via fallback order_number + method filter
// ============================================================

namespace App\Services;

use App\Events\PaymentProcessed;
use App\Models\KitchenOrder;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use App\Services\Gateway\MidtransGateway;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    // ── CASH & KARTU ─────────────────────────────────────────────────────

    public function process(Order $order, array $data): Payment
    {
        $method = $data['payment_method'];

        if (in_array($method, ['qris', 'ewallet', 'bank_transfer'])) {
            throw new \InvalidArgumentException(
                "Gunakan initiate() untuk pembayaran {$method}. process() hanya untuk cash dan card."
            );
        }

        return DB::transaction(function () use ($order, $data, $method) {
            $amount = (float) $data['amount'];
            $change = null;

            if ($method === 'cash') {
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
                'payment_method'   => $method,
                'card_type'        => $data['card_type']        ?? null,
                'card_last_four'   => $data['card_last_four']   ?? null,
                'approval_code'    => $data['approval_code']    ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'amount'           => $amount,
                'amount_received'  => $data['amount_received']  ?? null,
                'change_amount'    => $change,
                'status'           => 'paid',
                'settled_at'       => now(),
            ]);

            $this->confirmPaymentAndActivateKitchen($order->fresh());
            event(new PaymentProcessed($payment));

            return $payment;
        });
    }

    // ── GATEWAY (QRIS / E-Wallet / Transfer Bank) ─────────────────────────

    public function initiate(Order $order, string $method, ?string $ewalletType = null): Payment
    {
        if (! in_array($method, ['qris', 'ewallet', 'bank_transfer'])) {
            throw new \InvalidArgumentException("initiate() hanya untuk qris, ewallet, dan bank_transfer.");
        }

        // Cek reuse pending yang masih segar
        $existingPending = Payment::where('order_id', $order->id)
            ->where('payment_method', $method)
            ->where('status', 'pending')
            ->where('gateway', 'midtrans')
            ->when($ewalletType, fn($q) => $q->where('ewallet_type', $ewalletType))
            ->where('created_at', '>=', now()->subMinutes(13))
            ->latest()
            ->first();

        if ($existingPending && ($existingPending->snap_token || $existingPending->qr_string || $existingPending->va_number)) {
            return $existingPending;
        }

        // Cancel pending lama untuk method ini
        Payment::where('order_id', $order->id)
            ->where('payment_method', $method)
            ->where('status', 'pending')
            ->where('gateway', 'midtrans')
            ->update(['status' => 'cancelled']);

        $gateway = app(MidtransGateway::class);
        $result  = $gateway->createTransaction($order, $method, $ewalletType);

        return DB::transaction(function () use ($order, $method, $ewalletType, $result) {
            return Payment::create([
                'order_id'        => $order->id,
                'cashier_id'      => Auth::id(),
                'payment_method'  => $method,
                'ewallet_type'    => $method === 'ewallet' ? $ewalletType : null,
                'bank'            => $result['bank'] ?? ($method === 'bank_transfer' ? strtolower($ewalletType ?? 'bca') : null),
                'amount'          => $order->remainingBalance(),
                'status'          => 'pending',
                'gateway'         => 'midtrans',
                'gateway_trx_id'  => $result['gateway_trx_id'] ?? null,
                'snap_token'      => $result['snap_token']      ?? null,
                'payment_url'     => $result['payment_url']     ?? null,
                'qr_string'       => $result['qr_string']       ?? null,
                'va_number'       => $result['va_number']       ?? null,
            ]);
        });
    }

    /**
     * Poll status pembayaran dari gateway atau DB.
     *
     * FIX UTAMA:
     * - Cek DB dulu (webhook mungkin sudah mengupdate) — untuk SEMUA method
     * - Untuk QRIS & bank_transfer (punya gateway_trx_id): query Midtrans API jika DB belum paid
     * - Untuk Snap ewallet (gateway_trx_id null): hanya cek DB, tidak bisa query API
     * - Jika order sudah fully paid dari payment lain: kembalikan true
     */
    public function pollGatewayStatus(Payment $payment): bool
    {
        if (! $payment->isGateway() || ! $payment->isPending()) {
            return false;
        }

        // ── Step 1: Cek DB terlebih dahulu ───────────────────────────────
        // Webhook mungkin sudah masuk dan mengubah status payment ini
        $freshPayment = Payment::find($payment->id);
        if ($freshPayment && $freshPayment->status === 'paid') {
            $this->confirmPaymentAndActivateKitchen($freshPayment->order->fresh());
            return true;
        }

        // ── Step 2: Cek apakah order sudah fully paid dari payment lain ──
        // (misalnya ada 2 payment pending, salah satunya diproses webhook)
        $order = $payment->order->fresh(['payments']);
        if ($order && $order->isFullyPaid()) {
            $this->confirmPaymentAndActivateKitchen($order);
            return true;
        }

        // ── Step 3: Cek paid payment lain di order yang sama ─────────────
        // Bisa terjadi jika user initiate baru, lalu payment lama juga di-confirm
        $hasPaidSibling = Payment::where('order_id', $payment->order_id)
            ->where('status', 'paid')
            ->exists();
        if ($hasPaidSibling) {
            $this->confirmPaymentAndActivateKitchen($order);
            return true;
        }

        // ── Step 4: Untuk QRIS & bank_transfer — query Midtrans API ──────
        // Snap ewallet tidak punya gateway_trx_id saat initiate, jadi skip
        if ($payment->gateway_trx_id) {
            try {
                $gateway = app(MidtransGateway::class);
                $status  = $gateway->getTransactionStatus($payment->gateway_trx_id);

                if (empty($status)) {
                    return false;
                }

                $newStatus = MidtransGateway::parseWebhookStatus($status);

                if ($newStatus === 'paid') {
                    DB::transaction(function () use ($payment, $status) {
                        $payment->update([
                            'status'           => 'paid',
                            'gateway_status'   => $status['transaction_status'] ?? null,
                            'gateway_response' => $status,
                            'settled_at'       => now(),
                        ]);

                        Payment::where('order_id', $payment->order_id)
                            ->where('id', '!=', $payment->id)
                            ->where('status', 'pending')
                            ->update(['status' => 'cancelled']);

                        $this->confirmPaymentAndActivateKitchen($payment->order->fresh());
                        event(new PaymentProcessed($payment->fresh()));
                    });

                    return true;
                }

            } catch (\Exception $e) {
                Log::warning('pollGatewayStatus: Midtrans API error, fallback ke DB saja', [
                    'payment_id'     => $payment->id,
                    'gateway_trx_id' => $payment->gateway_trx_id,
                    'error'          => $e->getMessage(),
                ]);
            }

            return false;
        }

        // ── Step 5: Snap ewallet tanpa gateway_trx_id ────────────────────
        // Hanya mengandalkan webhook. DB sudah dicek di atas.
        // Tambahan: coba cari payment ewallet lain di order ini yang sudah paid
        // (bisa terjadi jika user buka Snap 2x dan salah satunya berhasil)
        $paidEwallet = Payment::where('order_id', $payment->order_id)
            ->where('payment_method', $payment->payment_method)
            ->where('status', 'paid')
            ->where('gateway', 'midtrans')
            ->where('id', '!=', $payment->id) // ← jangan hitung diri sendiri
            ->exists();

        if ($paidEwallet) {
            $this->confirmPaymentAndActivateKitchen($payment->order->fresh());
            return true;
        }

        return false;
    }

    // ── WEBHOOK HANDLER ───────────────────────────────────────────────────

    /**
     * Handle Midtrans webhook notification.
     *
     * FIX tambahan untuk Snap ewallet:
     * - Saat query fallback, filter berdasarkan payment_type dari payload
     *   agar tidak salah cocok jika ada pending QRIS dan ewallet sekaligus
     */
    public function handleWebhook(array $payload): void
    {
        $transactionId   = $payload['transaction_id'] ?? '';
        $midtransOrderId = $payload['order_id']       ?? '';
        $paymentType     = $payload['payment_type']   ?? '';

        Log::info('Midtrans webhook received', [
            'transaction_id'    => $transactionId,
            'midtrans_order_id' => $midtransOrderId,
            'transaction_status'=> $payload['transaction_status'] ?? null,
            'payment_type'      => $paymentType,
        ]);

        // ── Query 1: by gateway_trx_id (QRIS, bank_transfer) ─────────────
        $payment = null;

        if ($transactionId) {
            $payment = Payment::where('gateway_trx_id', $transactionId)
                ->where('gateway', 'midtrans')
                ->first();
        }

        // ── Query 2: fallback by order_number + method (Snap ewallet) ────
        // Di handleWebhook(), Query 2 — tambahkan fallback tanpa filter method
        if (! $payment && $midtransOrderId) {
            $orderNumber = preg_replace('/-\d+$/', '', $midtransOrderId);

            $methodMap = [
                'qris'          => 'qris',
                'bank_transfer' => 'bank_transfer',
                'echannel'      => 'bank_transfer',
                'gopay'         => 'ewallet',
                'shopeepay'     => 'ewallet',
                'ovo'           => 'ewallet',
                'dana'          => 'ewallet',
            ];
            $ourMethod = $methodMap[strtolower($paymentType)] ?? null;

            // Coba dengan filter method dulu
            $query = Payment::whereHas('order', fn($q) => $q->where('order_number', $orderNumber))
                ->where('gateway', 'midtrans')
                ->whereIn('status', ['pending', 'cancelled', 'paid']);

            if ($ourMethod) {
                $withMethod = (clone $query)->where('payment_method', $ourMethod)->latest('created_at')->first();
                // Jika tidak ketemu, coba tanpa filter method (QRIS bisa datang untuk payment ewallet)
                $payment = $withMethod ?? $query->latest('created_at')->first();
            } else {
                $payment = $query->latest('created_at')->first();
            }
        }

        // ── Query 3: mungkin sudah paid (webhook dikirim 2x) ─────────────
        if (! $payment && $transactionId) {
            $payment = Payment::where('gateway_trx_id', $transactionId)->first();
        }

        if (! $payment) {
            Log::warning('Midtrans webhook: payment tidak ditemukan sama sekali', [
                'transaction_id'    => $transactionId,
                'midtrans_order_id' => $midtransOrderId,
            ]);
            return;
        }

        // FIX: Simpan gateway_trx_id dari webhook pending agar webhook settlement bisa match
        if (! $payment->gateway_trx_id && $transactionId) {
            $payment->update(['gateway_trx_id' => $transactionId]);
        }

        // Idempoten
        if ($payment->status === 'paid') {
            return;
        }

        $newStatus     = MidtransGateway::parseWebhookStatus($payload);
        $gatewayStatus = $payload['transaction_status'] ?? null;

        DB::transaction(function () use ($payment, $payload, $newStatus, $gatewayStatus, $transactionId) {
            $payment->update([
                'status'           => $newStatus,
                'gateway_trx_id'   => $transactionId ?: $payment->gateway_trx_id,
                'gateway_status'   => $gatewayStatus,
                'gateway_response' => $payload,
                'settled_at'       => $newStatus === 'paid' ? now() : null,
            ]);

            if ($newStatus === 'paid') {
                Payment::where('order_id', $payment->order_id)
                    ->where('id', '!=', $payment->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'cancelled']);

                // FIX: Load fresh order dengan payments baru, hindari cached relation
                $freshOrder = \App\Models\Order::with('payments')->find($payment->order_id);
                $this->confirmPaymentAndActivateKitchen($freshOrder);
                event(new PaymentProcessed($payment->fresh()));

                Log::info('Midtrans webhook: payment confirmed paid', [
                    'payment_id'   => $payment->id,
                    'order_number' => $freshOrder->order_number,
                    'method'       => $payment->payment_method,
                    'trx_id'       => $transactionId,
                ]);
            }
        });
    }

    // ── REFUND ────────────────────────────────────────────────────────────

    public function refund(Payment $payment, float $amount, string $reason): Payment
    {
        abort_if(
            $payment->status !== 'paid',
            422,
            $payment->status === 'refunded'
                ? 'Pembayaran ini sudah pernah dikembalikan.'
                : 'Hanya pembayaran berstatus Lunas yang dapat di-refund.'
        );
        abort_if(
            $amount > (float) $payment->amount,
            422,
            'Jumlah refund tidak boleh melebihi jumlah pembayaran awal.'
        );

        return DB::transaction(function () use ($payment, $amount, $reason) {
            if ($payment->isGateway() && $payment->gateway_trx_id) {
                $gateway = app(MidtransGateway::class);
                $success = $gateway->refund($payment->gateway_trx_id, $amount, $reason);
                if (! $success) {
                    throw new \RuntimeException('Refund via Midtrans gagal. Coba lagi atau hubungi support.');
                }
            }

            $payment->update([
                'status'        => 'refunded',
                'refund_amount' => $amount,
                'refund_reason' => $reason,
                'refunded_at'   => now(),
                'refunded_by'   => Auth::id(),
            ]);

            $order     = $payment->order->fresh(['payments', 'table', 'kitchenOrder']);
            $stillPaid = $order->payments->where('status', 'paid')->sum('amount');

            if ($stillPaid <= 0 && ! $order->isCancelled() && ! $order->isCompleted()) {
                $order->update([
                    'status'        => 'cancelled',
                    'cancel_reason' => 'Refund: ' . $reason,
                    'cancelled_by'  => Auth::id(),
                    'cancelled_at'  => now(),
                ]);

                if ($order->table_id) {
                    Table::where('id', $order->table_id)->update(['status' => 'available']);
                }

                if ($order->kitchenOrder && ! in_array($order->kitchenOrder->status, ['cooking', 'ready'])) {
                    $order->kitchenOrder->update(['status' => 'cancelled']);
                }

                app(StockService::class)->restoreForOrder($order->load('items.product'));
            }

            return $payment->fresh();
        });
    }

    // ── CANCEL PENDING ────────────────────────────────────────────────────

    public function cancelPendingPayment(Payment $payment): void
    {
        abort_if(
            $payment->status !== 'pending',
            422,
            'Hanya pembayaran berstatus Menunggu yang dapat dibatalkan.'
        );

        $payment->update(['status' => 'cancelled']);
    }

    // ── PRIVATE HELPERS ───────────────────────────────────────────────────

    private function confirmPaymentAndActivateKitchen(Order $order): void
    {
        // FIX: Pastikan payments ter-load dari DB, bukan dari cache
        if (! $order->relationLoaded('payments')) {
            $order->load('payments');
        }

        if (! $order->isFullyPaid()) {
            return;
        }

        if ($order->sent_to_kitchen_at) {
            return;
        }

        $order->update(['sent_to_kitchen_at' => now()]);

        if ($order->kitchenOrder) {
            $order->kitchenOrder->update([
                'status'    => 'queued',
                'queued_at' => now(),
            ]);
        } else {
            KitchenOrder::create([
                'order_id'  => $order->id,
                'status'    => 'queued',
                'queued_at' => now(),
            ]);
        }
    }
}