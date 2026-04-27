<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'cashier_id', 'payment_method',
        'ewallet_type', 'card_type', 'card_last_four', 'approval_code',
        'reference_number',
        // Gateway columns (null = transaksi manual/offline)
        'gateway', 'gateway_trx_id', 'gateway_status',
        'snap_token', 'payment_url', 'qr_string', 'gateway_response',
        // Amounts
        'amount', 'amount_received', 'change_amount',
        // Status & refund
        'status', 'refund_amount', 'refund_reason', 'refunded_at', 'refunded_by',
        'settled_at',
        // Transfer bank (added in v2)
        'va_number', 'bank',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',  // decimal:2 dipertahankan (lebih presisi dari float)
        'amount_received'  => 'decimal:2',
        'change_amount'    => 'decimal:2',
        'refund_amount'    => 'decimal:2',
        'gateway_response' => 'array',
        'refunded_at'      => 'datetime',
        'settled_at'       => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────
    public function order(): BelongsTo      { return $this->belongsTo(Order::class); }
    public function cashier(): BelongsTo    { return $this->belongsTo(User::class, 'cashier_id'); }
    public function refundedBy(): BelongsTo { return $this->belongsTo(User::class, 'refunded_by'); }

    // ── Status helpers ───────────────────────────────────────────────────
    public function isPaid(): bool     { return $this->status === 'paid'; }
    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isRefunded(): bool { return $this->status === 'refunded'; }

    // ── Method helpers ───────────────────────────────────────────────────
    public function isCash(): bool    { return $this->payment_method === 'cash'; }
    public function isCard(): bool    { return $this->payment_method === 'card'; }
    public function isEwallet(): bool { return $this->payment_method === 'ewallet'; }
    public function isQris(): bool    { return $this->payment_method === 'qris'; }

    // ── Gateway helpers ──────────────────────────────────────────────────

    /**
     * Apakah transaksi ini melalui payment gateway (bukan manual/offline)?
     * Cash dan kartu via EDC selalu null (manual).
     * QRIS, ewallet, dan bank_transfer via Midtrans akan punya nilai gateway.
     */
    public function isGateway(): bool { return ! empty($this->gateway); }

    /**
     * Apakah transaksi gateway ini menunggu konfirmasi dari webhook?
     * Status 'pending' hanya muncul pada transaksi gateway yang belum settlement.
     * Dipertahankan dari v1.
     */
    public function isAwaitingGateway(): bool
    {
        return $this->isGateway() && $this->status === 'pending';
    }

    // ── Label helpers ────────────────────────────────────────────────────

    /**
     * Label tampilan yang konsisten untuk semua metode pembayaran.
     *
     * Contoh output:
     *   cash              → "Tunai"
     *   card (Visa, 1234) → "Kartu Visa (...1234)"
     *   qris              → "QRIS"
     *   ewallet gopay     → "GoPay"
     *   bank_transfer bca → "Transfer BCA (VA: 123456)"
     */
    public function methodLabel(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Tunai',

            'card' => collect([
                'Kartu',
                $this->card_type,
                $this->card_last_four ? "(...{$this->card_last_four})" : null,
            ])->filter()->implode(' '),

            'qris' => 'QRIS',

            'ewallet' => $this->ewalletLabel(),

            'bank_transfer' => $this->bankTransferLabel(),

            default => ucfirst(str_replace('_', ' ', $this->payment_method)),
        };
    }

    /**
     * Label singkat tanpa VA number — untuk kolom tabel laporan.
     */
    public function methodLabelShort(): string
    {
        if ($this->payment_method === 'bank_transfer') {
            $bankName = $this->bank ?? $this->ewallet_type ?? null;
            if (! $bankName) return 'Transfer Bank';
            $map = ['bca' => 'BCA', 'bni' => 'BNI', 'bri' => 'BRI', 'mandiri' => 'Mandiri', 'permata' => 'Permata'];
            return 'Transfer ' . ($map[strtolower($bankName)] ?? strtoupper($bankName));
        }

        return $this->methodLabel();
    }

    /**
     * Label untuk filter dropdown laporan.
     * Key = nilai di database, value = teks tampilan.
     */
    public static function methodOptions(): array
    {
        return [
            'cash'          => 'Tunai',
            'card'          => 'Kartu',
            'qris'          => 'QRIS',
            'ewallet'       => 'E-Wallet',
            'bank_transfer' => 'Transfer Bank',
        ];
    }

    public function ewalletLabel(): string
    {
        $map = [
            'gopay'     => 'GoPay',
            'ovo'       => 'OVO',
            'dana'      => 'Dana',
            'shopeepay' => 'ShopeePay',
        ];

        $type = strtolower($this->ewallet_type ?? '');
        return $map[$type] ?? ($this->ewallet_type ? ucfirst($this->ewallet_type) : 'E-Wallet');
    }

    public function bankTransferLabel(): string
    {
        $bankName = $this->bank ?? $this->ewallet_type ?? null;
        if (! $bankName) {
            return 'Transfer Bank';
        }

        $map = [
            'bca'     => 'BCA',
            'bni'     => 'BNI',
            'bri'     => 'BRI',
            // 'mandiri' => 'Mandiri', // Mandiri punya format unik dengan biller code & bill key di payment_url, jadi diproses terpisah di bawah
            'permata' => 'Permata',
        ];

        $normalized = strtolower($bankName);
        $label      = $map[$normalized] ?? strtoupper($bankName);

        return "Transfer {$label}" . ($this->va_number ? " (VA: {$this->va_number})" : '');
    }

    public function mandiriBillerCode(): ?string
    {
        if ($this->bank !== 'mandiri' || ! $this->payment_url) return null;
        $parts = explode(':', $this->payment_url);
        return $parts[1] ?? null;
    }

    public function mandiriBillKey(): ?string
    {
        if ($this->bank !== 'mandiri' || ! $this->payment_url) return null;
        $parts = explode(':', $this->payment_url);
        return $parts[2] ?? null;
    }

    // ── Scopes ───────────────────────────────────────────────────────────
    public function scopePaid($q)    { return $q->where('status', 'paid'); }
    public function scopeToday($q)   { return $q->whereDate('created_at', today()); }   // dipertahankan dari v1
    public function scopePending($q) { return $q->where('status', 'pending'); }
    public function scopeGateway($q) { return $q->whereNotNull('gateway'); }             // dipertahankan dari v1
}