<?php
// ── KitchenOrder ────────────────────────────────────────────────────────
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

    public function scopeActive($q) { return $q->whereIn('status', ['queued','cooking']); }
}