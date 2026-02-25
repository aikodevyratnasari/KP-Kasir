<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BundlePackageItem extends Model
{
    protected $fillable = ['bundle_package_id', 'product_id', 'product_variant_id', 'quantity'];
    protected $casts    = ['quantity' => 'integer'];

    public function bundle(): BelongsTo  { return $this->belongsTo(BundlePackage::class, 'bundle_package_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
}