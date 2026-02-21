<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'table_id', 'created_by', 'customer_name', 'customer_phone',
        'reserved_at', 'expires_at', 'guest_count', 'notes',
        'status', 'cancelled_at', 'cancel_reason',
    ];

    protected $casts = [
        'reserved_at'  => 'datetime',
        'expires_at'   => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function table(): BelongsTo    { return $this->belongsTo(Table::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function isActive(): bool    { return $this->status === 'active'; }
    public function isExpired(): bool   { return $this->expires_at && now()->gt($this->expires_at); }

    public function scopeActive($q)     { return $q->where('status', 'active'); }
    public function scopeExpired($q)    { return $q->where('status', 'active')->where('expires_at', '<', now()); }
}