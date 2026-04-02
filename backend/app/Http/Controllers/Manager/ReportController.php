<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    // ── Dashboard ────────────────────────────────────────────────────────
    public function dashboard(Request $request): View
    {
        $storeId = $request->get('_store_id');
        $raw     = $this->reportService->dashboardToday($storeId);

        // Rename agar cocok dengan variable yang dipakai dashboard view
        $data = array_merge($raw, [
            'totalSalesToday'  => $raw['totalSales']  ?? 0,
            'totalOrdersToday' => $raw['totalOrders'] ?? 0,
            'avgOrderValue'    => $raw['avgOrder']    ?? 0,
            'weeklyTrend'      => collect($raw['trend'] ?? [])->map(fn($r) => (object)[
                'date'  => $r->date,
                'total' => $r->total,
            ])->values()->toArray(),
            'lowStockProducts' => $raw['lowStock'] ?? collect(),
        ]);

        return view('manager.dashboard', $data);
    }

    // ── Dashboard filter (AJAX) ──────────────────────────────────────────
    public function dashboardFilter(Request $request): JsonResponse
    {
        $storeId = $request->get('_store_id');
        $period  = $request->period ?? 'today';

        [$from, $to] = match ($period) {
            'week'   => [now()->startOfWeek(),  now()->endOfWeek()],
            'month'  => [now()->startOfMonth(), now()->endOfMonth()],
            'year'   => [now()->startOfYear(),  now()->endOfYear()],
            'all'    => [now()->subYears(10)->startOfDay(), now()->endOfDay()],
            'custom' => [
                Carbon::parse($request->from)->startOfDay(),
                Carbon::parse($request->to)->endOfDay(),
            ],
            default  => [now()->startOfDay(), now()->endOfDay()],
        };

        $raw = $this->reportService->dashboardAnalytics($storeId, $from, $to);

        return response()->json([
            'totalSales'   => $raw['totalSales']  ?? 0,
            'totalOrders'  => $raw['totalOrders'] ?? 0,
            'avgOrder'     => $raw['avgOrder']    ?? 0,
            'activeOrders' => $raw['activeOrders'] ?? [],
            'topProducts'  => $raw['topProducts']  ?? [],
            'recentOrders' => $raw['recentOrders'] ?? [],
            'trend'        => collect($raw['trend'] ?? [])->map(fn($r) => [
                'date'  => $r->date,
                'total' => $r->total,
            ])->values(),
        ]);
    }

    // ── Sales Report ─────────────────────────────────────────────────────
    public function sales(Request $request): View
    {
        [$from, $to] = $this->parseDates($request);
        $raw = $this->reportService->salesReport($request->get('_store_id'), $from, $to);

        // Rename agar cocok dengan sales view
        $data = [
            'from'             => $from,
            'to'               => $to,
            'totalSales'       => $raw['totalSales']  ?? 0,
            'totalOrders'      => $raw['orderCount']  ?? 0,   // view pakai $totalOrders
            'avgTransaction'   => $raw['avgSale']     ?? 0,   // view pakai $avgTransaction
            'byPaymentMethod'  => collect($raw['byMethod'] ?? [])->values(), // view pakai $byPaymentMethod
            'byOrderType'      => $raw['byType']      ?? collect(), // view pakai $byOrderType
            'dailyTrend'       => $raw['daily']       ?? collect(), // view pakai $dailyTrend
        ];

        return view('manager.reports.sales', $data);
    }

    // ── Product Report ───────────────────────────────────────────────────
    public function products(Request $request): View
    {
        [$from, $to] = $this->parseDates($request);
        $data = $this->reportService->productReport($request->get('_store_id'), $from, $to);
        return view('manager.reports.products', array_merge($data, compact('from', 'to')));
    }

    // ── Revenue Report ───────────────────────────────────────────────────
    public function revenue(Request $request): View
    {
        [$from, $to] = $this->parseDates($request);
        $period = $request->period ?? 'daily';
        $data   = $this->reportService->revenueAnalytics($request->get('_store_id'), $period, $from, $to);
        return view('manager.reports.revenue', compact('data', 'from', 'to', 'period'));
    }

    // ── Cashier Report ───────────────────────────────────────────────────
    public function cashiers(Request $request): View
    {
        [$from, $to] = $this->parseDates($request);
        $data = $this->reportService->cashierReport($request->get('_store_id'), $from, $to);
        return view('manager.reports.cashiers', compact('data', 'from', 'to'));
    }

    // ── Helpers ──────────────────────────────────────────────────────────
    private function parseDates(Request $request): array
    {
        $from = $request->from
            ? Carbon::parse($request->from)->startOfDay()
            : now()->copy()->startOfMonth();
        $to = $request->to
            ? Carbon::parse($request->to)->endOfDay()
            : now()->copy()->endOfDay();
        return [$from, $to];
    }
}