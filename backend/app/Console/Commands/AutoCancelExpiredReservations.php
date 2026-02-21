<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoCancelExpiredReservations extends Command
{
    protected $signature   = 'depos:reservations:expire';
    protected $description = 'Auto-cancel reservations that have passed their expiry time (run every minute).';

    public function handle(): int
    {
        $expired = Reservation::where('status', 'active')
            ->where('expires_at', '<', now())
            ->with('table')
            ->get();

        foreach ($expired as $reservation) {
            DB::transaction(function () use ($reservation) {
                $reservation->update([
                    'status'       => 'expired',
                    'cancelled_at' => now(),
                    'cancel_reason' => 'Auto-expired: meja tidak digunakan dalam 30 menit.',
                ]);

                if ($reservation->table && $reservation->table->status === 'reserved') {
                    Table::where('id', $reservation->table_id)->update(['status' => 'available']);
                }
            });

            $this->info("Expired reservation #{$reservation->id} (table {$reservation->table?->number})");
        }

        $this->info("Total expired: {$expired->count()}");
        return self::SUCCESS;
    }
}