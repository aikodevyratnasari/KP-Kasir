<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id', 'store_id', 'name', 'email', 'password',
        'phone', 'status', 'failed_login_attempts',
        'locked_until', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'locked_until'          => 'datetime',
        'last_login_at'         => 'datetime',
        'email_verified_at'     => 'datetime',
        'password'              => 'hashed',
        'failed_login_attempts' => 'integer',
    ];

    // ── Relationships ───────────────────────────────────────────────────
    public function role(): BelongsTo    { return $this->belongsTo(Role::class); }
    public function store(): BelongsTo   { return $this->belongsTo(Store::class); }
    public function orders(): HasMany    { return $this->hasMany(Order::class, 'cashier_id'); }
    public function payments(): HasMany  { return $this->hasMany(Payment::class, 'cashier_id'); }
    public function activityLogs(): HasMany { return $this->hasMany(ActivityLog::class); }

    // ── Helpers ─────────────────────────────────────────────────────────
    public function isAdmin(): bool    { return $this->role->slug === 'admin'; }
    public function isManager(): bool  { return $this->role->slug === 'manager'; }
    public function isCashier(): bool  { return $this->role->slug === 'cashier'; }
    public function isKitchen(): bool  { return $this->role->slug === 'kitchen_staff'; }
    public function isActive(): bool   { return $this->status === 'active'; }

    public function isLocked(): bool
    {
        return $this->locked_until && now()->lt($this->locked_until);
    }

    public function dashboardRoute(): string
    {
        return match ($this->role->slug) {
            'admin'         => route('admin.dashboard'),
            'manager'       => route('manager.dashboard'),
            'cashier'       => route('cashier.orders.index'),
            'kitchen_staff' => route('kitchen.display'),
            default         => '/',
        };
    }
}