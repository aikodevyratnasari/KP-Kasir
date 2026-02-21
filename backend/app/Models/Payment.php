<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'cashier_id', 'payment_method',
        'ewallet_type', 'card_type', 'card_last_four', 'approval_code',
        'reference_number', 'amount', 'amount_received', 'change_amount',
        'status', 'refund_amount', 'refund_reason', 'refunded_at', 'refunded_by',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'amount_received' => 'decimal:2',
        'change_amount'   => 'decimal:2',
        'refund_amount'   => 'decimal:2',
        'refunded_at'     => 'datetime',
    ];

    public function order(): BelongsTo      { return $this->belongsTo(Order::class); }
    public function cashier(): BelongsTo    { return $this->belongsTo(User::class, 'cashier_id'); }
    public function refundedBy(): BelongsTo { return $this->belongsTo(User::class, 'refunded_by'); }

    public function isCash(): bool     { return $this->payment_method === 'cash'; }
    public function isCard(): bool     { return $this->payment_method === 'card'; }
    public function isEwallet(): bool  { return $this->payment_method === 'ewallet'; }
    public function isRefunded(): bool { return $this->status === 'refunded'; }

    public function methodLabel(): string
    {
        return match ($this->payment_method) {
            'cash'    => 'Tunai',
            'card'    => ucfirst($this->card_type ?? 'Kartu') . ' ****' . $this->card_last_four,
            'ewallet' => $this->ewallet_type ?? 'E-Wallet',
            default   => $this->payment_method,
        };
    }

    public function scopePaid($q)  { return $q->where('status', 'paid'); }
    public function scopeToday($q) { return $q->whereDate('created_at', today()); }
}