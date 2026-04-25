<?php

namespace App\Services\Gateway;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class MidtransGateway implements GatewayInterface
{
    public function __construct()
    {
        \Midtrans\Config::$serverKey    = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.production', false);
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;
    }

    public function createTransaction(Order $order, string $method, ?string $ewalletType = null): array
    {
        $midtransOrderId = $order->order_number . '-' . time();

        $transactionDetails = [
            'order_id'     => $midtransOrderId,
            'gross_amount' => (int) round($order->total_amount),
        ];

        $customerDetails = [
            'first_name' => $order->customer_name ?? 'Pelanggan',
        ];

        if ($method === 'qris') {
            return $this->createQrisTransaction($transactionDetails, $customerDetails);
        }

        if ($method === 'bank_transfer') {
            return $this->createBankTransferTransaction($transactionDetails, $customerDetails, $ewalletType);
        }

        return $this->createSnapTransaction($transactionDetails, $customerDetails, $ewalletType);
    }

    private function createQrisTransaction(array $transactionDetails, array $customerDetails): array
    {
        $payload = [
            'payment_type'        => 'qris',
            'transaction_details' => $transactionDetails,
            'customer_details'    => $customerDetails,
            'qris'                => ['acquirer' => 'gopay'],
        ];

        try {
            $response = \Midtrans\CoreApi::charge($payload);

            return [
                'snap_token'     => null,
                'payment_url'    => null,
                'qr_string'      => $response->qr_string      ?? null,
                'gateway_trx_id' => $response->transaction_id ?? null,
                'va_number'      => null,
                'bank'           => null,
            ];

        } catch (\Exception $e) {
            Log::error('Midtrans QRIS charge failed', [
                'order_id' => $transactionDetails['order_id'],
                'error'    => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function createBankTransferTransaction(array $transactionDetails, array $customerDetails, ?string $bank): array
    {
        $bank = strtolower($bank ?? 'bca');

        try {
            if ($bank === 'mandiri') {
                $payload = [
                    'payment_type'        => 'echannel',
                    'transaction_details' => $transactionDetails,
                    'customer_details'    => $customerDetails,
                    'echannel'            => [
                        'bill_info1' => 'Pembayaran:',
                        'bill_info2' => 'Pesanan #' . ($transactionDetails['order_id'] ?? ''),
                    ],
                ];
            } else {
                $payload = [
                    'payment_type'        => 'bank_transfer',
                    'transaction_details' => $transactionDetails,
                    'customer_details'    => $customerDetails,
                    'bank_transfer'       => ['bank' => $bank],
                ];
            }

            $response = \Midtrans\CoreApi::charge($payload);

            $vaNumber = null;
            if ($bank === 'mandiri') {
                $vaNumber = ($response->biller_code ?? '') . $response->bill_key ?? null;
            } elseif (! empty($response->va_numbers) && is_array($response->va_numbers)) {
                $vaNumber = $response->va_numbers[0]->va_number ?? null;
            } elseif (! empty($response->permata_va_number)) {
                $vaNumber = $response->permata_va_number;
            }

            return [
                'snap_token'     => null,
                'payment_url'    => $bank === 'mandiri'
                    ? "mandiri:{$response->biller_code}:{$response->bill_key}"
                    : null,
                'qr_string'      => null,
                'gateway_trx_id' => $response->transaction_id ?? null,
                'va_number'      => $vaNumber,
                'bank'           => $bank,
            ];

        } catch (\Exception $e) {
            Log::error('Midtrans Bank Transfer charge failed', [
                'order_id' => $transactionDetails['order_id'],
                'bank'     => $bank,
                'error'    => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * E-Wallet via Snap.
     *
     * FIX: Tambahkan callbacks.finish agar Midtrans redirect kembali ke app,
     * bukan ke example.com (default Midtrans jika tidak di-set).
     *
     * Alur setelah kasir klik "Selesai" di popup Snap:
     *   Midtrans → GET /cashier/snap/finish?order_id=ORD-xxx-timestamp
     *   → route 'cashier.snap.finish' → redirect ke detail pesanan
     */
    private function createSnapTransaction(array $transactionDetails, array $customerDetails, ?string $ewalletType): array
    {
        $enabledPayments = $ewalletType
            ? [$this->mapEwalletType($ewalletType)]
            : ['gopay', 'shopeepay'];

        $params = [
            'transaction_details' => $transactionDetails,
            'customer_details'    => $customerDetails,
            'enabled_payments'    => $enabledPayments,
            // ── FIX: set finish URL agar tidak redirect ke example.com ──
            'callbacks'           => [
                'finish' => url('/cashier/snap/finish'),
            ],
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            return [
                'snap_token'     => $snapToken,
                'payment_url'    => 'https://' . (config('services.midtrans.production') ? 'app' : 'app.sandbox') . ".midtrans.com/snap/v2/vtweb/{$snapToken}",
                'qr_string'      => null,
                'gateway_trx_id' => null,
                'va_number'      => null,
                'bank'           => null,
            ];

        } catch (\Exception $e) {
            Log::error('Midtrans Snap token failed', [
                'order_id' => $transactionDetails['order_id'],
                'error'    => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function mapEwalletType(string $type): string
    {
        return match (strtolower($type)) {
            'gopay'     => 'gopay',
            'ovo'       => 'ovo',
            'dana'      => 'dana',
            'shopeepay' => 'shopeepay',
            default     => 'gopay',
        };
    }

    public function verifyWebhook(array $payload): bool
    {
        if (empty($payload['signature_key'])) {
            return false;
        }

        $serverKey = config('services.midtrans.server_key');
        $computed  = hash('sha512',
            ($payload['order_id']     ?? '') .
            ($payload['status_code']  ?? '') .
            ($payload['gross_amount'] ?? '') .
            $serverKey
        );

        return hash_equals($computed, $payload['signature_key']);
    }

    public function getTransactionStatus(string $gatewayTrxId): array
    {
        try {
            $status = \Midtrans\Transaction::status($gatewayTrxId);
            return (array) $status;
        } catch (\Exception $e) {
            Log::error('Midtrans status check failed', [
                'gateway_trx_id' => $gatewayTrxId,
                'error'          => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function refund(string $gatewayTrxId, float $amount, string $reason): bool
    {
        try {
            \Midtrans\Transaction::refund($gatewayTrxId, [
                'refund_key' => 'refund-' . $gatewayTrxId . '-' . time(),
                'amount'     => (int) round($amount),
                'reason'     => $reason,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Midtrans refund failed', [
                'gateway_trx_id' => $gatewayTrxId,
                'amount'         => $amount,
                'error'          => $e->getMessage(),
            ]);
            return false;
        }
    }

    public static function parseWebhookStatus(array $payload): string
    {
        $txStatus    = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status']       ?? null;

        if (in_array($txStatus, ['settlement', 'capture'])) {
            if ($txStatus === 'capture' && $fraudStatus !== null && $fraudStatus !== 'accept') {
                return 'pending';
            }
            return 'paid';
        }

        return 'pending';
    }
}