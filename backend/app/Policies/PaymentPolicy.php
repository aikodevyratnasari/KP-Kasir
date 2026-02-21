<?php
namespace App\Policies;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function create(User $user): bool { return in_array($user->role->slug, ['admin', 'manager', 'cashier']); }
    public function refund(User $user, Payment $payment): bool { return in_array($user->role->slug, ['admin', 'manager']); }
    public function viewHistory(User $user): bool { return in_array($user->role->slug, ['admin', 'manager', 'cashier']); }
}
