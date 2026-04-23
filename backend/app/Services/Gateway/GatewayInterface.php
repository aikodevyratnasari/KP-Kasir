<?php

namespace App\Services\Gateway;

use App\Models\Order;
use App\Models\Payment;

/**
 * Kontrak untuk semua payment gateway adapter.
 *
 * Cash dan kartu manual (via EDC) TIDAK mengimplementasikan interface ini —
 * keduanya diproses langsung di PaymentService::process() tanpa gateway.
 */
interface GatewayInterface
{
    /**
     * Buat transaksi baru di gateway dan kembalikan data yang diperlukan
     * untuk menampilkan QR / membuka Snap widget kepada pelanggan.
     *
     * @return array{
     *   snap_token: string|null,
     *   payment_url: string|null,
     *   qr_string: string|null,
     *   gateway_trx_id: string|null,
     * }
     */
    public function createTransaction(Order $order, string $method, ?string $ewalletType = null): array;

    /**
     * Verifikasi signature webhook agar tidak bisa dipalsukan.
     * Selalu panggil ini SEBELUM memproses payload webhook.
     */
    public function verifyWebhook(array $payload): bool;

    /**
     * Ambil status transaksi terbaru dari API gateway.
     * Digunakan untuk polling status jika webhook belum diterima.
     */
    public function getTransactionStatus(string $gatewayTrxId): array;

    /**
     * Proses refund via gateway (hanya untuk transaksi gateway).
     * Transaksi manual di-refund langsung oleh PaymentService tanpa memanggil ini.
     */
    public function refund(string $gatewayTrxId, float $amount, string $reason): bool;
}