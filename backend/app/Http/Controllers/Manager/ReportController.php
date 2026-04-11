<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

// Tambah use statements:
use App\Exports\SalesReportExport;
use App\Exports\ProductReportExport;
use App\Exports\RevenueReportExport;
use App\Mail\ReportMail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    // ── Dashboard ────────────────────────────────────────────────────────
    public function dashboard(Request $request): View
    {
        $storeId = $request->get('_store_id');
        $raw     = $this->reportService->dashboardToday($storeId);

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

        $data = [
            'from'             => $from,
            'to'               => $to,
            'totalSales'       => $raw['totalSales']  ?? 0,
            'totalOrders'      => $raw['orderCount']  ?? 0,
            'avgTransaction'   => $raw['avgSale']     ?? 0,
            'byPaymentMethod'  => collect($raw['byMethod'] ?? [])->values(),
            'byOrderType'      => $raw['byType']      ?? collect(),
            'dailyTrend'       => $raw['daily']       ?? collect(),
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

    // ── DOWNLOAD REPORT ──────────────────────────────────────────────────
    public function download(Request $request, string $type): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
    {
        $storeId = $request->get('_store_id');
        $from    = $request->from ?? now()->startOfMonth()->format('Y-m-d');
        $to      = $request->to   ?? now()->format('Y-m-d');
        $format  = $request->format ?? 'xlsx';

        $fromDate = \Carbon\Carbon::parse($from)->startOfDay();
        $toDate   = \Carbon\Carbon::parse($to)->endOfDay();

        $writerType = match($format) {
            'csv'  => \Maatwebsite\Excel\Excel::CSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        $ext      = $format === 'csv' ? 'csv' : 'xlsx';
        $fileName = "laporan-{$type}-{$from}-{$to}.{$ext}";

        $export = match($type) {
            'sales' => new SalesReportExport(
                dailyTrend:       $this->getDailyTrend($storeId, $fromDate, $toDate),
                byPaymentMethod:  $this->getByPaymentMethod($storeId, $fromDate, $toDate),
                byOrderType:      $this->getByOrderType($storeId, $fromDate, $toDate),
                totalSales:       $this->getTotalSales($storeId, $fromDate, $toDate),
                totalOrders:      $this->getTotalOrders($storeId, $fromDate, $toDate),
                avgTransaction:   $this->getAvgTransaction($storeId, $fromDate, $toDate),
                from: $from, to: $to,
            ),
            'products' => new ProductReportExport(
                topByQty:     $this->getTopByQty($storeId, $fromDate, $toDate),
                topByRevenue: $this->getTopByRevenue($storeId, $fromDate, $toDate),
                from: $from, to: $to,
            ),
            'revenue' => new RevenueReportExport(
                data:   $this->getRevenueData($storeId, $fromDate, $toDate, $request->period ?? 'daily'),
                period: $request->period ?? 'daily',
                from: $from, to: $to,
            ),
            default => abort(404),
        };

        return Excel::download($export, $fileName, $writerType);
    }

    // ── SEND EMAIL REPORT ────────────────────────────────────────────────
    public function sendReportEmail(Request $request, string $type): JsonResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);

        $storeId = $request->get('_store_id');
        $from    = $request->from ?? now()->startOfMonth()->format('Y-m-d');
        $to      = $request->to   ?? now()->format('Y-m-d');

        $fromDate = \Carbon\Carbon::parse($from)->startOfDay();
        $toDate   = \Carbon\Carbon::parse($to)->endOfDay();

        $fileName = "laporan-{$type}-{$from}-{$to}.xlsx";
        $filePath = storage_path("app/temp/{$fileName}");

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $export = match($type) {
            'sales' => new SalesReportExport(
                dailyTrend:       $this->getDailyTrend($storeId, $fromDate, $toDate),
                byPaymentMethod:  $this->getByPaymentMethod($storeId, $fromDate, $toDate),
                byOrderType:      $this->getByOrderType($storeId, $fromDate, $toDate),
                totalSales:       $this->getTotalSales($storeId, $fromDate, $toDate),
                totalOrders:      $this->getTotalOrders($storeId, $fromDate, $toDate),
                avgTransaction:   $this->getAvgTransaction($storeId, $fromDate, $toDate),
                from: $from, to: $to,
            ),
            'products' => new ProductReportExport(
                topByQty:     $this->getTopByQty($storeId, $fromDate, $toDate),
                topByRevenue: $this->getTopByRevenue($storeId, $fromDate, $toDate),
                from: $from, to: $to,
            ),
            'revenue' => new RevenueReportExport(
                data:   $this->getRevenueData($storeId, $fromDate, $toDate, $request->period ?? 'daily'),
                period: $request->period ?? 'daily',
                from: $from, to: $to,
            ),
            default => abort(404),
        };

        Excel::store($export, "temp/{$fileName}", 'local');

        try {
            Mail::to($request->email)->send(new ReportMail($type, $from, $to, $filePath, $fileName));
            @unlink($filePath);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            @unlink($filePath);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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

    private function getDailyTrend(int $storeId, $from, $to)
    {
        return \DB::table('orders')
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as count')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$from, $to])
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('date')->orderBy('date')->get();
    }

    private function getByPaymentMethod(int $storeId, $from, $to)
    {
        return \DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->selectRaw('payment_method, SUM(payments.amount) as total, COUNT(*) as count')
            ->where('orders.store_id', $storeId)
            ->where('payments.status', 'paid')
            ->whereBetween('payments.created_at', [$from, $to])
            ->groupBy('payment_method')->get();
    }

    private function getByOrderType(int $storeId, $from, $to)
    {
        return \DB::table('orders')
            ->selectRaw('order_type, SUM(total_amount) as total, COUNT(*) as count')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$from, $to])
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('order_type')->get();
    }

    private function getTotalSales(int $storeId, $from, $to): float
    {
        return (float) \DB::table('orders')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$from, $to])
            ->whereNotIn('status', ['cancelled'])
            ->sum('total_amount');
    }

    private function getTotalOrders(int $storeId, $from, $to): int
    {
        return \DB::table('orders')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$from, $to])
            ->whereNotIn('status', ['cancelled'])
            ->count();
    }

    private function getAvgTransaction(int $storeId, $from, $to): float
    {
        return (float) \DB::table('orders')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$from, $to])
            ->whereNotIn('status', ['cancelled'])
            ->avg('total_amount') ?? 0;
    }

    private function getTopByQty(int $storeId, $from, $to)
    {
        return \DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(order_items.subtotal) as total_revenue')
            ->where('orders.store_id', $storeId)
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('orders.status', ['cancelled'])
            ->groupBy('product_name')->orderByDesc('total_qty')->limit(20)->get();
    }

    private function getTopByRevenue(int $storeId, $from, $to)
    {
        return \DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->selectRaw('product_name, SUM(order_items.subtotal) as total_revenue, SUM(quantity) as total_qty')
            ->where('orders.store_id', $storeId)
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('orders.status', ['cancelled'])
            ->groupBy('product_name')->orderByDesc('total_revenue')->limit(20)->get();
    }

    private function getRevenueData(int $storeId, $from, $to, string $period)
    {
        $groupBy = match($period) {
            'weekly'  => "TO_CHAR(created_at, 'IYYY-IW')",
            'monthly' => "TO_CHAR(created_at, 'YYYY-MM')",
            'yearly'  => "TO_CHAR(created_at, 'YYYY')",
            default   => 'DATE(created_at)',
        };

        return \DB::table('orders')
            ->selectRaw("{$groupBy} as period, SUM(total_amount) as total, COUNT(*) as count")
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$from, $to])
            ->whereNotIn('status', ['cancelled'])
            ->groupByRaw($groupBy)->orderByRaw($groupBy)->get();
    }
}