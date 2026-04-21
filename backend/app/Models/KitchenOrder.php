<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitchenOrder extends Model
{
    protected $fillable = [
        'order_id', 'status', 'priority',
        'queued_at', 'cooking_started_at', 'ready_at',
        'started_by', 'completed_by', 'kitchen_notes',
    ];

    protected $casts = [
        'queued_at'          => 'datetime',
        'cooking_started_at' => 'datetime',
        'ready_at'           => 'datetime',
        'priority'           => 'integer',
    ];

    // Status constants — gunakan ini agar tidak ada typo di seluruh codebase
    const STATUS_WAITING_PAYMENT = 'waiting_payment';
    const STATUS_QUEUED          = 'queued';
    const STATUS_COOKING         = 'cooking';
    const STATUS_READY           = 'ready';
    const STATUS_CANCELLED       = 'cancelled';

    public function order(): BelongsTo       { return $this->belongsTo(Order::class); }
    public function startedBy(): BelongsTo   { return $this->belongsTo(User::class, 'started_by'); }
    public function completedBy(): BelongsTo { return $this->belongsTo(User::class, 'completed_by'); }

    public function waitingMinutes(): int
    {
        return (int) ($this->queued_at ?? now())->diffInMinutes(now());
    }

    public function waitingColor(): string
    {
        $mins = $this->waitingMinutes();
        if ($mins < 10)  return 'green';
        if ($mins <= 20) return 'yellow';
        return 'red';
    }

    // Scope: hanya yang aktif di tampilan dapur (belum selesai/batal)
    public function scopeActive($q)
    {
        return $q->whereIn('status', [self::STATUS_QUEUED, self::STATUS_COOKING]);
    }

    // Helper status
    public function isWaitingPayment(): bool { return $this->status === self::STATUS_WAITING_PAYMENT; }
    public function isQueued(): bool         { return $this->status === self::STATUS_QUEUED; }
    public function isCooking(): bool        { return $this->status === self::STATUS_COOKING; }
    public function isReady(): bool          { return $this->status === self::STATUS_READY; }
    public function isCancelled(): bool      { return $this->status === self::STATUS_CANCELLED; }
}