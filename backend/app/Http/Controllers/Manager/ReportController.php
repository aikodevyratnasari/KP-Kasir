<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\ReportFilterRequest;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function dashboard(Request $request): View
    {
        // 1. Ambil tanggal dari input (pake fungsi parseDates yang sudah kamu punya di bawah)
    [$from, $to] = $this->parseDates($request);

    // 2. Suruh Service (koki) memasak data berdasarkan tanggal tersebut
    // Pastikan nanti di ReportService, function dashboardToday diubah agar menerima $from dan $to
    $data = $this->reportService->dashboardAnalytics($request->get('_store_id'), $from, $to);

    // 3. Kirim ke view beserta variabel tanggalnya agar filter tidak hilang saat di-refresh
    return view('manager.dashboard', array_merge($data, compact('from', 'to')));
    }

    public function sales(ReportFilterRequest $request): View
    {
        [$from, $to] = $this->parseDates($request);
        $data = $this->reportService->salesReport($request->get('_store_id'), $from, $to);
        return view('manager.reports.sales', array_merge($data, compact('from', 'to')));
    }

    public function products(ReportFilterRequest $request): View
    {
        [$from, $to] = $this->parseDates($request);
        $data = $this->reportService->productReport($request->get('_store_id'), $from, $to);
        return view('manager.reports.products', array_merge($data, compact('from', 'to')));
    }

    public function revenue(ReportFilterRequest $request): View
    {
        [$from, $to] = $this->parseDates($request);
        $period = $request->period ?? 'daily';
        $data   = $this->reportService->revenueAnalytics($request->get('_store_id'), $period, $from, $to);
        return view('manager.reports.revenue', compact('data', 'from', 'to', 'period'));
    }

    public function cashiers(ReportFilterRequest $request): View
    {
        [$from, $to] = $this->parseDates($request);
        $data = $this->reportService->cashierReport($request->get('_store_id'), $from, $to);
        return view('manager.reports.cashiers', compact('data', 'from', 'to'));
    }
    public function dashboardFilter(Request $request)
{
    $period  = $request->query('period', 'today');
    $storeId = $request->get('_store_id');

    [$from, $to] = match($period) {
        'week'  => [now()->startOfWeek(),  now()->endOfWeek()],
        'month' => [now()->startOfMonth(), now()->endOfMonth()],
        default => [now()->startOfDay(),   now()->endOfDay()],
    };

    $data = $this->reportService->dashboardAnalytics($storeId, $from, $to);

    return response()->json($data);
}
private function parseDates(Request $request): array
{
    try {
        $from = $request->from
            ? Carbon::parse($request->from)->startOfDay()
            : now()->startOfMonth();

        $to = $request->to
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();

        // Pastikan from tidak lebih besar dari to
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }
    } catch (\Exception $e) {
        $from = now()->startOfMonth();
        $to   = now()->endOfDay();
    }

    return [$from, $to];
}
}