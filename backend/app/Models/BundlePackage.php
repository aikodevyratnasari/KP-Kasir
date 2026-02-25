<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BundlePackage extends Model
{
    protected $fillable = [
        'store_id', 'name', 'description', 'image',
        'bundle_price', 'starts_at', 'ends_at', 'is_active',
    ];

    protected $casts = [
        'bundle_price' => 'decimal:2',
        'starts_at'    => 'datetime',
        'ends_at'      => 'datetime',
        'is_active'    => 'boolean',
    ];

    public function store(): BelongsTo { return $this->belongsTo(Store::class); }
    public function items(): HasMany   { return $this->hasMany(BundlePackageItem::class)->with('product', 'variant'); }

    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) return false;
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->ends_at   && $now->gt($this->ends_at))   return false;
        return true;
    }

    public function normalPrice(): float
    {
        return (float) $this->items->sum(fn($i) => (float) $i->product->price * $i->quantity);
    }

    public function savings(): float
    {
        return max(0, $this->normalPrice() - (float) $this->bundle_price);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function scopeForStore($q, int $storeId) { return $q->where('store_id', $storeId); }
}