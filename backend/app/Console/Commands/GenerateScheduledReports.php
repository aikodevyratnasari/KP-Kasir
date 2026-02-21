<?php

namespace App\Console\Commands;

use App\Models\ReportSchedule;
use App\Models\User;
use App\Notifications\DailyReportNotification;
use App\Services\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class GenerateScheduledReports extends Command
{
    protected $signature   = 'depos:reports:generate';
    protected $description = 'Generate and email all active scheduled reports (run via scheduler every hour).';

    public function __construct(private ReportService $reportService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $now       = now();
        $schedules = ReportSchedule::active()
            ->where('send_at', '<=', $now->format('H:i:s'))
            ->where(function ($q) use ($now) {
                $q->whereNull('last_sent_at')
                  ->orWhereDate('last_sent_at', '<', $now->toDateString());
            })
            ->with('store', 'createdBy')
            ->get();

        foreach ($schedules as $schedule) {
            try {
                [$from, $to] = $this->periodRange($schedule->frequency);

                $data = match ($schedule->report_type) {
                    'sales'    => $this->reportService->salesReport($schedule->store_id, $from, $to),
                    'product'  => $this->reportService->productReport($schedule->store_id, $from, $to),
                    'cashier'  => $this->reportService->cashierReport($schedule->store_id, $from, $to),
                    'revenue'  => $this->reportService->revenueAnalytics($schedule->store_id, 'daily', $from, $to),
                    default    => [],
                };

                $summary = $this->summarize($data);

                Notification::route('mail', $schedule->recipients)
                    ->notify(new DailyReportNotification(
                        $summary,
                        ucfirst($schedule->report_type),
                        "{$from->toDateString()} – {$to->toDateString()}",
                    ));

                $schedule->update(['last_sent_at' => $now]);
                $this->info("Sent {$schedule->report_type} report for store #{$schedule->store_id}");

            } catch (\Throwable $e) {
                $this->error("Failed schedule #{$schedule->id}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }

    private function periodRange(string $frequency): array
    {
        return match ($frequency) {
            'daily'   => [now()->subDay()->startOfDay(),  now()->subDay()->endOfDay()],
            'weekly'  => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'monthly' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            default   => [now()->startOfDay(), now()->endOfDay()],
        };
    }

    private function summarize(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $out[ucwords(str_replace('_', ' ', $key))] = is_numeric($value)
                    ? 'Rp ' . number_format((float)$value, 0, ',', '.')
                    : $value;
            }
        }
        return $out;
    }
}