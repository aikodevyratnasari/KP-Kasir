<?php
namespace App\Policies;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool { return in_array($user->role->slug, ['admin', 'manager', 'cashier']); }
    public function view(User $user, Order $order): bool { return $this->sameStore($user, $order->store_id); }
    public function create(User $user): bool { return in_array($user->role->slug, ['admin', 'manager', 'cashier']); }
    public function update(User $user, Order $order): bool
    {
        if (!$order->isPending()) return false;
        if (in_array($user->role->slug, ['admin', 'manager'])) return $this->sameStore($user, $order->store_id);
        return $user->role->slug === 'cashier' && $order->cashier_id === $user->id && $this->sameStore($user, $order->store_id);
    }
    public function cancel(User $user, Order $order): bool
    {
        if ($order->isCompleted() || $order->isCancelled()) return false;
        if (in_array($user->role->slug, ['admin', 'manager'])) return $this->sameStore($user, $order->store_id);
        return $user->role->slug === 'cashier' && $order->cashier_id === $user->id && $order->isPending();
    }
    public function updateStatus(User $user, Order $order): bool
    {
        return in_array($user->role->slug, ['kitchen_staff', 'manager', 'admin']) && $this->sameStore($user, $order->store_id);
    }
    private function sameStore(User $user, int $storeId): bool { return $user->role->slug === 'admin' || $user->store_id === $storeId; }
}
