<?php
namespace App\Policies;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool { return $user->role->slug === 'admin'; }
    public function view(User $user, User $model): bool { return $user->role->slug === 'admin' || $user->id === $model->id; }
    public function create(User $user): bool { return $user->role->slug === 'admin'; }
    public function update(User $user, User $model): bool { return $user->role->slug === 'admin'; }
    public function delete(User $user, User $model): bool { return $user->role->slug === 'admin' && $user->id !== $model->id; }
    public function deactivate(User $user, User $model): bool { return $user->role->slug === 'admin' && $user->id !== $model->id; }
}
