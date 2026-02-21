<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = [
        'name', 'address', 'phone', 'email',
        'tax_number', 'tax_rate', 'receipt_footer', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean', 'tax_rate' => 'decimal:2'];

    public function users(): HasMany      { return $this->hasMany(User::class); }
    public function categories(): HasMany { return $this->hasMany(Category::class); }
    public function products(): HasMany   { return $this->hasMany(Product::class); }
    public function tables(): HasMany     { return $this->hasMany(Table::class); }
    public function orders(): HasMany     { return $this->hasMany(Order::class); }
    public function reportSchedules(): HasMany { return $this->hasMany(ReportSchedule::class); }
}