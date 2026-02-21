<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Table extends Model
{
    protected $fillable = ['store_id', 'number', 'capacity', 'section', 'status'];

    public function store(): BelongsTo         { return $this->belongsTo(Store::class); }
    public function orders(): HasMany          { return $this->hasMany(Order::class); }
    public function reservations(): HasMany    { return $this->hasMany(Reservation::class); }
    public function activeOrder(): HasOne      { return $this->hasOne(Order::class)->whereIn('status', ['pending','cooking','ready']); }
    public function activeReservation(): HasOne { return $this->hasOne(Reservation::class)->where('status', 'active'); }

    public function isAvailable(): bool  { return $this->status === 'available'; }
    public function isOccupied(): bool   { return $this->status === 'occupied'; }
    public function isReserved(): bool   { return $this->status === 'reserved'; }

    public function scopeAvailable($q)   { return $q->where('status', 'available'); }
    public function scopeForStore($q, int $storeId) { return $q->where('store_id', $storeId); }
}