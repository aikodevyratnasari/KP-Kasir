<?php

namespace App\Services;

use App\Events\PaymentProcessed;
use App\Models\KitchenOrder;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use App\Services\Gateway\GatewayInterface;
use App\Services\Gateway\MidtransGateway;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * ────────────────────────────────────────────────────────────────────
     * ALUR PER METODE PEMBAYARAN
     * ────────────────────────────────────────────────────────────────────
     *
     * CASH (Tunai)
     *   Kasir input jumlah → process() → Payment status='paid' langsung.
     *
     * KARTU (via EDC fisik)
     *   Kasir konfirmasi setelah EDC approve → process() → paid langsung.
     *
     * QRIS
     *   initiate() → Core API → qr_string → render QRCode.js
     *   → pelanggan scan → webhook → handleWebhook() → paid
     *
     * E-WALLET (GoPay, OVO, Dana, ShopeePay)
     *   initiate() → Snap token → popup Snap
     *   → pelanggan bayar → webhook → handleWebhook() → paid
     *
     * TRANSFER BANK (BCA, BNI, BRI, Mandiri, Permata)
     *   initiate() → Core API → virtual account number ditampilkan kasir
     *   → pelanggan transfer → webhook → handleWebhook() → paid
     *   Catatan sandbox: gunakan Midtrans Simulator untuk simulasi payment
     *   https://simulator.sandbox.midtrans.com
     * ────────────────────────────────────────────────────────────────────
     */

    // ── CASH & KARTU (Manual/Offline) ────────────────────────────────────

    public function process(Order $order, array $data): Payment
    {
        $method = $data['payment_method'];

        if (in_array($method, ['qris', 'ewallet', 'bank_transfer'])) {
            throw new \InvalidArgumentException(
                "Gunakan initiate() untuk pembayaran {$method}. " .
                "process() hanya untuk cash dan card."
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
                'card_type'        => $data['card_type']      ?? null,
                'card_last_four'   => $data['card_last_four'] ?? null,
                'approval_code'    => $data['approval_code']  ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'amount'           => $amount,
                'amount_received'  => $data['amount_received'] ?? null,
                'change_amount'    => $change,
                'status'           => 'paid',
                'settled_at'       => now(),
            ]);

            $this->confirmPaymentAndActivateKitchen($order->fresh());

            event(new PaymentProcessed($payment));

            return $payment;
        });
    }

    // ── QRIS, E-WALLET & TRANSFER BANK (via Midtrans) ────────────────────

    /**
     * Inisiasi pembayaran gateway.
     *
     * Metode yang didukung: qris, ewallet, bank_transfer
     *
     * Untuk bank_transfer, $ewalletType diisi dengan nama bank:
     *   'bca', 'bni', 'bri', 'mandiri', 'permata'
     *
     * Return: Payment dengan snap_token / qr_string / va_number terisi.
     */
    public function initiate(Order $order, string $method, ?string $ewalletType = null): Payment
    {
        if (! in_array($method, ['qris', 'ewallet', 'bank_transfer'])) {
            throw new \InvalidArgumentException(
                "initiate() hanya untuk qris, ewallet, dan bank_transfer."
            );
        }
 
        // Cek reuse: hanya jika method DAN ewallet_type sama persis
        $existingPending = Payment::where('order_id', $order->id)
            ->where('payment_method', $method)
            ->where('status', 'pending')
            ->where('gateway', 'midtrans')
            ->when($ewalletType, fn($q) => $q->where('ewallet_type', $ewalletType))
            ->where('created_at', '>=', now()->subMinutes(13))
            ->latest()
            ->first();
 
        if ($existingPending && ($existingPending->snap_token || $existingPending->qr_string || $existingPending->va_number)) {
            return $existingPending; // reuse yang lama, tidak buat baru
        }
 
        // ── FIX: Cancel semua pending lama untuk method ini ──────────────
        // Kasir bisa mencoba ShopeePay lalu ganti GoPay — pending lama
        // harus di-cancel agar tidak menumpuk di halaman detail pesanan.
        // Status 'cancelled' berbeda dari 'refunded' — tidak ada uang yang
        // perlu dikembalikan karena pembayaran belum terjadi.
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
     * Poll status dari Midtrans API (fallback jika webhook belum diterima).
     */
    public function pollGatewayStatus(Payment $payment): bool
    {
        if (! $payment->isGateway() || ! $payment->isPending()) {
            return false;
        }

        if (! $payment->gateway_trx_id) {
            return false;
        }

        $gateway = app(MidtransGateway::class);
        $status  = $gateway->getTransactionStatus($payment->gateway_trx_id);

        if (empty($status)) {
            return false;
        }

        $newStatus = MidtransGateway::parseWebhookStatus($status);

        if ($newStatus === 'paid' && $payment->status !== 'paid') {
            DB::transaction(function () use ($payment, $status) {
                $payment->update([
                    'status'           => 'paid',
                    'gateway_status'   => $status['transaction_status'] ?? null,
                    'gateway_response' => $status,
                    'settled_at'       => now(),
                ]);

                $this->confirmPaymentAndActivateKitchen($payment->order->fresh());
                event(new PaymentProcessed($payment->fresh()));
            });

            return true;
        }

        return false;
    }

    // ── WEBHOOK HANDLER ───────────────────────────────────────────────────

    public function handleWebhook(array $payload): void
    {
        $payment = Payment::where('gateway_trx_id', $payload['transaction_id'] ?? '')
            ->orWhere(function ($q) use ($payload) {
                $midtransOrderId = $payload['order_id'] ?? '';
                $orderNumber = preg_replace('/-\d+$/', '', $midtransOrderId);
                $q->whereHas('order', fn($oq) => $oq->where('order_number', $orderNumber))
                  ->where('gateway', 'midtrans')
                  ->whereIn('status', ['pending']);
            })
            ->first();

        if (! $payment) {
            Log::warning('Midtrans webhook: payment tidak ditemukan', [
                'transaction_id' => $payload['transaction_id'] ?? null,
                'order_id'       => $payload['order_id']       ?? null,
            ]);
            return;
        }

        if ($payment->status === 'paid') {
            Log::info('Midtrans webhook: payment sudah paid, skip', ['payment_id' => $payment->id]);
            return;
        }

        $newStatus     = MidtransGateway::parseWebhookStatus($payload);
        $gatewayStatus = $payload['transaction_status'] ?? null;

        DB::transaction(function () use ($payment, $payload, $newStatus, $gatewayStatus) {
            $payment->update([
                'status'           => $newStatus,
                'gateway_trx_id'   => $payload['transaction_id'] ?? $payment->gateway_trx_id,
                'gateway_status'   => $gatewayStatus,
                'gateway_response' => $payload,
                'settled_at'       => $newStatus === 'paid' ? now() : null,
            ]);

            if ($newStatus === 'paid') {
                $this->confirmPaymentAndActivateKitchen($payment->order->fresh());
                event(new PaymentProcessed($payment->fresh()));

                Log::info('Midtrans webhook: payment confirmed', [
                    'payment_id'     => $payment->id,
                    'order_number'   => $payment->order->order_number,
                    'gateway_status' => $gatewayStatus,
                    'method'         => $payment->payment_method,
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
                    throw new \RuntimeException(
                        'Refund via Midtrans gagal. Coba lagi atau hubungi support.'
                    );
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

    // ── PRIVATE HELPERS ───────────────────────────────────────────────────

    private function confirmPaymentAndActivateKitchen(Order $order): void
    {
        if (! $order->isFullyPaid()) {
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