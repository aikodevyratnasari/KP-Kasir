<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = [
        'name', 'address', 'phone', 'email',
        'tax_number', 'tax_rate', 'receipt_footer',
        'is_active', 'has_kitchen', 'logo_path', // tambah logo_path
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'has_kitchen' => 'boolean',
        'tax_rate'    => 'decimal:2',
    ];

    // Accessor untuk URL logo (fallback ke default jika belum diupload)
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo_path && \Storage::disk('public')->exists($this->logo_path)) {
            return asset('storage/' . $this->logo_path);
        }
        return asset('images/image.png'); // fallback ke logo default
    }

    public function users(): HasMany      { return $this->hasMany(User::class); }
    public function categories(): HasMany { return $this->hasMany(Category::class); }
    public function products(): HasMany   { return $this->hasMany(Product::class); }
    public function tables(): HasMany     { return $this->hasMany(Table::class); }
    public function orders(): HasMany     { return $this->hasMany(Order::class); }
    public function reportSchedules(): HasMany { return $this->hasMany(ReportSchedule::class); }
}