<?php
namespace App\Policies;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Product $product): bool { return true; }
    public function create(User $user): bool { return in_array($user->role->slug, ['admin', 'manager']); }
    public function update(User $user, Product $product): bool
    {
        return in_array($user->role->slug, ['admin', 'manager']) && ($user->role->slug === 'admin' || $user->store_id === $product->store_id);
    }
    public function delete(User $user, Product $product): bool { return $this->update($user, $product); }
    public function adjustStock(User $user, Product $product): bool { return $this->update($user, $product); }
}
