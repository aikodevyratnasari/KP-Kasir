<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = [
        'name', 'address', 'phone', 'email',
        'tax_number', 'tax_rate', 'receipt_footer',
        'is_active', 'has_kitchen', 'logo_path',
        'is_headquarters',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'has_kitchen'      => 'boolean',
        'is_headquarters'  => 'boolean',
        'tax_rate'         => 'decimal:2',
    ];

    // Accessor URL logo cabang ini (fallback ke default)
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo_path && \Storage::disk('public')->exists($this->logo_path)) {
            return asset('storage/' . $this->logo_path);
        }
        return asset('images/image.png');
    }

    /**
     * Ambil logo cabang pusat (HQ).
     * Digunakan oleh guest layout dan tempat lain yang butuh logo global.
     * Fallback: toko aktif manapun → logo default.
     */
    public static function headquartersLogoUrl(): string
    {
        $hq = static::where('is_headquarters', true)->where('is_active', true)->first()
            ?? static::where('is_active', true)->first();

        return $hq?->logo_url ?? asset('images/image.png');
    }

    /**
     * Tetapkan toko ini sebagai HQ.
     * Otomatis mencabut status HQ dari toko lain.
     */
    public function setAsHeadquarters(): void
    {
        static::where('id', '!=', $this->id)->update(['is_headquarters' => false]);
        $this->update(['is_headquarters' => true]);
    }

    public function users(): HasMany           { return $this->hasMany(User::class); }
    public function categories(): HasMany      { return $this->hasMany(Category::class); }
    public function products(): HasMany        { return $this->hasMany(Product::class); }
    public function tables(): HasMany          { return $this->hasMany(Table::class); }
    public function orders(): HasMany          { return $this->hasMany(Order::class); }
    public function reportSchedules(): HasMany { return $this->hasMany(ReportSchedule::class); }
}