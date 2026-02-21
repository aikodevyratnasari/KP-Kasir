<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id', 'name', 'slug', 'description',
        'image', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn($m) => $m->slug ??= Str::slug($m->name) . '-' . $m->store_id);
    }

    public function store(): BelongsTo    { return $this->belongsTo(Store::class); }
    public function products(): HasMany   { return $this->hasMany(Product::class); }

    public function hasProducts(): bool   { return $this->products()->exists(); }
}