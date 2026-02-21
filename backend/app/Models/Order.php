<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'store_id', 'cashier_id', 'table_id', 'order_number',
        'order_type', 'status', 'subtotal', 'tax_rate', 'tax_amount',
        'total_amount', 'notes', 'cancel_reason', 'cancelled_by',
        'cancelled_at', 'cooking_at', 'ready_at', 'completed_at',
    ];

    protected $casts = [
        'subtotal'      => 'decimal:2',
        'tax_rate'      => 'decimal:2',
        'tax_amount'    => 'decimal:2',
        'total_amount'  => 'decimal:2',
        'cancelled_at'  => 'datetime',
        'cooking_at'    => 'datetime',
        'ready_at'      => 'datetime',
        'completed_at'  => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────
    public function store(): BelongsTo          { return $this->belongsTo(Store::class); }
    public function cashier(): BelongsTo        { return $this->belongsTo(User::class, 'cashier_id'); }
    public function table(): BelongsTo          { return $this->belongsTo(Table::class); }
    public function cancelledBy(): BelongsTo    { return $this->belongsTo(User::class, 'cancelled_by'); }
    public function items(): HasMany            { return $this->hasMany(OrderItem::class); }
    public function payments(): HasMany         { return $this->hasMany(Payment::class); }
    public function kitchenOrder(): HasOne      { return $this->hasOne(KitchenOrder::class); }
    public function stockLogs(): HasMany        { return $this->hasMany(StockLog::class); }

    // ── Status helpers ───────────────────────────────────────────────────
    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isCooking(): bool    { return $this->status === 'cooking'; }
    public function isReady(): bool      { return $this->status === 'ready'; }
    public function isCompleted(): bool  { return $this->status === 'completed'; }
    public function isCancelled(): bool  { return $this->status === 'cancelled'; }
    public function isDineIn(): bool     { return $this->order_type === 'dine_in'; }

    public function totalPaid(): float
    {
        return (float) $this->payments()->where('status', 'paid')->sum('amount');
    }

    public function remainingBalance(): float
    {
        return max(0, (float) $this->total_amount - $this->totalPaid());
    }

    public function isFullyPaid(): bool  { return $this->remainingBalance() <= 0; }

    // ── Scopes ───────────────────────────────────────────────────────────
    public function scopeForStore($q, int $storeId) { return $q->where('store_id', $storeId); }
    public function scopeActive($q)                 { return $q->whereIn('status', ['pending','cooking','ready']); }
    public function scopeToday($q)                  { return $q->whereDate('created_at', today()); }
}