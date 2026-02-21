<?php
namespace App\Policies;
use App\Models\User;

class TablePolicy
{
    public function viewAny(User $user): bool { return in_array($user->role->slug, ['admin', 'manager', 'cashier']); }
    public function manage(User $user): bool { return in_array($user->role->slug, ['admin', 'manager']); }
    public function transfer(User $user): bool { return in_array($user->role->slug, ['admin', 'manager']); }
    public function reserve(User $user): bool { return in_array($user->role->slug, ['admin', 'manager', 'cashier']); }
}
