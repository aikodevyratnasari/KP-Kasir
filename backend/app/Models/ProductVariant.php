<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'name', 'type', 'price_adjustment',
        'stock', 'is_available', 'sort_order',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'is_available'     => 'boolean',
        'stock'            => 'integer',
        'sort_order'       => 'integer',
    ];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }

    public function finalPrice(): float
    {
        return max(0, (float) $this->product->price + (float) $this->price_adjustment);
    }
}