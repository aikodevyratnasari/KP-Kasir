<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class DailyReportNotification extends Notification
{
    public function __construct(public array $report = []) {}

    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(mixed $notifiable): array
    {
        return array_merge($this->report, [
            'message' => 'Laporan harian tersedia.',
        ]);
    }
}