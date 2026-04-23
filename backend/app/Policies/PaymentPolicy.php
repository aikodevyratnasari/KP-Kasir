<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Semua role boleh membuat pembayaran (tetap)
     */
    public function create(User $user): bool
    {
        return in_array($user->role?->slug, ['admin', 'manager', 'cashier']);
    }

    /**
     * Semua role boleh refund (tetap),
     * tapi ditambahkan validasi baru
     */
    public function refund(User $user, Payment $payment): bool
    {
        // Role tetap sama (tidak dibatasi hanya admin/manager)
        if (! in_array($user->role?->slug, ['admin', 'manager', 'cashier'])) {
            return false;
        }

        // Tambahan: hanya payment 'paid' yang bisa di-refund
        if ($payment->status !== 'paid') {
            return false;
        }

        // Tambahan: harus dalam store yang sama
        return $payment->order?->store_id === $user->store_id;
    }

    /**
     * Semua role boleh melihat riwayat (tetap)
     */
    public function viewHistory(User $user): bool
    {
        return in_array($user->role?->slug, ['admin', 'manager', 'cashier']);
    }

    /**
     * Semua role boleh melihat detail payment (ditambahkan),
     * dengan validasi store
     */
    public function view(User $user, Payment $payment): bool
    {
        if (! in_array($user->role?->slug, ['admin', 'manager', 'cashier'])) {
            return false;
        }

        // Tambahan: hanya bisa lihat payment dari store sendiri
        return $payment->order?->store_id === $user->store_id;
    }
}