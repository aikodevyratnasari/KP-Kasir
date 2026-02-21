<?php

use App\Console\Commands\AutoCancelExpiredReservations;
use App\Console\Commands\GenerateScheduledReports;
use App\Console\Commands\UnlockAccounts;
use Illuminate\Support\Facades\Schedule;

/*
|───────────────────────────────────────────────────────────────────────────────
|  TASK SCHEDULER  (routes/console.php  — Laravel 11 style)
|
|  Add this to server crontab:
|  * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
|───────────────────────────────────────────────────────────────────────────────
*/

// Auto-cancel reservations expired >30 min — runs every minute
Schedule::command(AutoCancelExpiredReservations::class)
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Unlock user accounts whose lockout period ended — runs every minute
Schedule::command(UnlockAccounts::class)
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Generate & email scheduled reports — runs every hour at :00
Schedule::command(GenerateScheduledReports::class)
    ->hourly()
    ->withoutOverlapping();