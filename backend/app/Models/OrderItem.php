<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'variant_id', 'product_name', 'variant_name',
        'original_price', 'discount_amount', 'discount_label',
        'unit_price', 'quantity', 'subtotal', 'special_notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'original_price'  => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal'   => 'decimal:2',
        'quantity'   => 'integer',
    ];

    public function order(): BelongsTo   { return $this->belongsTo(Order::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function variant(): BelongsTo { return $this->belongsTo(\App\Models\ProductVariant::class, 'variant_id'); }

    protected static function boot(): void
    {
        parent::boot();
        static::saving(function (self $item) {
            $item->subtotal = $item->unit_price * $item->quantity;
        });
    }
}