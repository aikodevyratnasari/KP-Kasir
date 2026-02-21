<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────
    public function isAdmin(): bool    { return $this->slug === 'admin'; }
    public function isManager(): bool  { return $this->slug === 'manager'; }
    public function isCashier(): bool  { return $this->slug === 'cashier'; }
    public function isKitchen(): bool  { return $this->slug === 'kitchen_staff'; }
}