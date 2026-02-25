<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'store_id', 'name', 'slug', 'description',
        'image', 'price', 'stock', 'low_stock_alert',
        'is_available', 'track_stock',
    ];

    protected $casts = [
        'price'           => 'decimal:2',
        'is_available'    => 'boolean',
        'track_stock'     => 'boolean',
        'stock'           => 'integer',
        'low_stock_alert' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn($m) => $m->slug ??= Str::slug($m->name) . '-' . $m->store_id);
    }

    public function category(): BelongsTo      { return $this->belongsTo(Category::class); }
    public function store(): BelongsTo         { return $this->belongsTo(Store::class); }
    public function orderItems(): HasMany       { return $this->hasMany(OrderItem::class); }
    public function stockLogs(): HasMany        { return $this->hasMany(StockLog::class); }
    public function variants(): HasMany         { return $this->hasMany(ProductVariant::class)->orderBy('sort_order'); }
    public function discounts(): HasMany        { return $this->hasMany(ProductDiscount::class)->latest(); }
    public function activeDiscount(): ?ProductDiscount
    {
        return $this->hasMany(ProductDiscount::class)->active()->first();
    }

    public function isLowStock(): bool   { return $this->stock <= $this->low_stock_alert && $this->stock > 0; }
    public function isOutOfStock(): bool { return $this->stock <= 0; }

    public function scopeAvailable($q)  { return $q->where('is_available', true)->where('stock', '>', 0); }
    public function scopeLowStock($q)   { return $q->whereColumn('stock', '<=', 'low_stock_alert')->where('stock', '>', 0); }
    public function scopeForStore($q, int $storeId) { return $q->where('store_id', $storeId); }
}