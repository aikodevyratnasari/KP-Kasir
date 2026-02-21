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
        $data = $this->reportService->dashboardToday($request->get('_store_id'));
        return view('manager.dashboard', $data);
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

    private function parseDates(Request $request): array
    {
        $from = $request->from ? Carbon::parse($request->from)->startOfDay() : now()->startOfMonth();
        $to   = $request->to   ? Carbon::parse($request->to)->endOfDay()     : now()->endOfDay();
        return [$from, $to];
    }
}