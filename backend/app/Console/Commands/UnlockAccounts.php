<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UnlockAccounts extends Command
{
    protected $signature   = 'depos:accounts:unlock';
    protected $description = 'Unlock user accounts whose lockout period has expired (run every minute).';

    public function handle(): int
    {
        $unlocked = User::where('locked_until', '<', now())
            ->whereNotNull('locked_until')
            ->update([
                'locked_until'          => null,
                'failed_login_attempts' => 0,
                'updated_at'            => now(),
            ]);

        $this->info("Unlocked {$unlocked} account(s).");
        return self::SUCCESS;
    }
}