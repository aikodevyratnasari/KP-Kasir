<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSchedule extends Model
{
    protected $fillable = [
        'created_by', 'store_id', 'report_type', 'frequency',
        'send_at', 'recipients', 'is_active', 'last_sent_at',
    ];

    protected $casts = [
        'recipients'   => 'array',
        'is_active'    => 'boolean',
        'last_sent_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function store(): BelongsTo     { return $this->belongsTo(Store::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
}