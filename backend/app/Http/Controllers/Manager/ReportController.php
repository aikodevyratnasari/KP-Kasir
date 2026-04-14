<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

use App\Exports\SalesReportExport;
use App\Exports\ProductReportExport;
use App\Exports\RevenueReportExport;
use App\Exports\CashierReportExport;
use App\Mail\ReportMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    // ── Reports Index ────────────────────────────────────────────────────
    public function index(): View
    {
        return view('manager.reports.index');
    }

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

        // FIX: [$a,$b] = match(){} causes ParseError when match arms contain arrays.
        // The parser misreads the ']' inside match arms as closing the outer '['
        // of the destructuring. Assign to $range first, then destructure.
        $range = match ($period) {
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
        [$from, $to] = $range;

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

        return view('manager.reports.sales', [
            'from'            => $from,
            'to'              => $to,
            'totalSales'      => $raw['totalSales']  ?? 0,
            'totalOrders'     => $raw['orderCount']  ?? 0,
            'avgTransaction'  => $raw['avgSale']     ?? 0,
            'byPaymentMethod' => collect($raw['byMethod'] ?? [])->values(),
            'byOrderType'     => $raw['byType']      ?? collect(),
            'dailyTrend'      => $raw['daily']       ?? collect(),
        ]);
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
    public function download(Request $request, string $type): mixed
    {
        $storeId = $request->get('_store_id');
        $from    = $request->from   ?? now()->startOfMonth()->format('Y-m-d');
        $to      = $request->to     ?? now()->format('Y-m-d');
        $format  = $request->format ?? 'xlsx';
        $period  = $request->period ?? 'daily';

        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate   = Carbon::parse($to)->endOfDay();

        // ── PDF ──────────────────────────────────────────────────────────
        if ($format === 'pdf') {
            $viewData = $this->buildPdfData($type, $storeId, $fromDate, $toDate, $from, $to, $period);
            $fileName = "laporan-{$type}-{$from}-{$to}.pdf";

            return Pdf::loadView('manager.reports.pdf', $viewData)
                ->setPaper('a4', 'portrait')
                ->download($fileName);
        }

        // ── XLSX / CSV ────────────────────────────────────────────────────
        $export = $this->buildExport($type, $storeId, $fromDate, $toDate, $from, $to, $period);

        if ($format === 'csv') {
            $fileName   = "laporan-{$type}-{$from}-{$to}.csv";
            $writerType = \Maatwebsite\Excel\Excel::CSV;
        } else {
            $fileName   = "laporan-{$type}-{$from}-{$to}.xlsx";
            $writerType = \Maatwebsite\Excel\Excel::XLSX;
        }

        return Excel::download($export, $fileName, $writerType);
    }

    // ── SEND EMAIL REPORT ────────────────────────────────────────────────
    public function sendReportEmail(Request $request, string $type): JsonResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);

        $storeId = $request->get('_store_id');
        $from    = $request->from   ?? now()->startOfMonth()->format('Y-m-d');
        $to      = $request->to     ?? now()->format('Y-m-d');
        $period  = $request->period ?? 'daily';
        $format  = $request->format ?? 'xlsx';   // ← ambil format dari request

        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate   = Carbon::parse($to)->endOfDay();

        $ext      = match($format) { 'csv' => 'csv', 'pdf' => 'pdf', default => 'xlsx' };
        $fileName = "laporan-{$type}-{$from}-{$to}.{$ext}";
        $tempDir  = storage_path('app/temp');
        $filePath = "{$tempDir}/{$fileName}";

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        try {
            if ($format === 'pdf') {
                $viewData = $this->buildPdfData($type, $storeId, $fromDate, $toDate, $from, $to, $period);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('manager.reports.pdf', $viewData)
                    ->setPaper('a4', 'portrait');
                file_put_contents($filePath, $pdf->output());
            } else {
                $export     = $this->buildExport($type, $storeId, $fromDate, $toDate, $from, $to, $period);
                $writerType = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
                \Maatwebsite\Excel\Facades\Excel::store($export, "temp/{$fileName}", 'local', $writerType);
            }

            Mail::to($request->email)->send(new ReportMail($type, $from, $to, $filePath, $fileName));
            @unlink($filePath);
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            @unlink($filePath);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Build Export Object (XLSX/CSV) ────────────────────────────────────
    private function buildExport(string $type, int $storeId, $fromDate, $toDate, string $from, string $to, string $period): mixed
    {
        return match ($type) {
            'sales' => new SalesReportExport(
                dailyTrend:      $this->getDailyTrend($storeId, $fromDate, $toDate),
                byPaymentMethod: $this->getByPaymentMethod($storeId, $fromDate, $toDate),
                byOrderType:     $this->getByOrderType($storeId, $fromDate, $toDate),
                totalSales:      $this->getTotalSales($storeId, $fromDate, $toDate),
                totalOrders:     $this->getTotalOrders($storeId, $fromDate, $toDate),
                avgTransaction:  $this->getAvgTransaction($storeId, $fromDate, $toDate),
                from: $from, to: $to,
            ),
            'products' => new ProductReportExport(
                topByQty:     $this->getTopByQty($storeId, $fromDate, $toDate),
                topByRevenue: $this->getTopByRevenue($storeId, $fromDate, $toDate),
                from: $from, to: $to,
            ),
            // FIXED: was getRevenueData() which returned 'total' column.
            // Now uses revenueAnalytics() consistent with Blade view and RevenueReportExport.
            'revenue' => new RevenueReportExport(
                data:   $this->reportService->revenueAnalytics($storeId, $period, $fromDate, $toDate)['data'] ?? collect(),
                period: $period,
                from: $from, to: $to,
            ),
            'cashiers' => new CashierReportExport(
                cashiers: $this->getCashiers($storeId, $fromDate, $toDate),
                from: $from, to: $to,
            ),
            default => abort(404),
        };
    }

    // ── Build PDF Data ────────────────────────────────────────────────────
    private function buildPdfData(string $type, int $storeId, $fromDate, $toDate, string $from, string $to, string $period): array
    {
        $base = compact('type', 'from', 'to', 'period');

        return match ($type) {
            'sales' => array_merge($base, [
                'totalSales'      => $this->getTotalSales($storeId, $fromDate, $toDate),
                'totalOrders'     => $this->getTotalOrders($storeId, $fromDate, $toDate),
                'avgTransaction'  => $this->getAvgTransaction($storeId, $fromDate, $toDate),
                'byPaymentMethod' => $this->getByPaymentMethod($storeId, $fromDate, $toDate),
                'byOrderType'     => $this->getByOrderType($storeId, $fromDate, $toDate),
                'dailyTrend'      => $this->getDailyTrend($storeId, $fromDate, $toDate),
            ]),
            'products' => array_merge($base, [
                'topByQty'     => $this->getTopByQty($storeId, $fromDate, $toDate),
                'topByRevenue' => $this->getTopByRevenue($storeId, $fromDate, $toDate),
            ]),
            'revenue' => array_merge($base, [
                'data' => $this->reportService->revenueAnalytics($storeId, $period, $fromDate, $toDate),
            ]),
            'cashiers' => array_merge($base, [
                'data' => $this->reportService->cashierReport($storeId, $fromDate, $toDate),
            ]),
            default => abort(404),
        };
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
        return (float) (\DB::table('orders')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$from, $to])
            ->whereNotIn('status', ['cancelled'])
            ->avg('total_amount') ?? 0);
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
        $groupBy = match ($period) {
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

    private function getCashiers(int $storeId, $from, $to)
    {
        return \DB::table('orders')
            ->join('users', 'orders.cashier_id', '=', 'users.id')
            ->where('orders.store_id', $storeId)
            ->whereNotIn('orders.status', ['cancelled'])
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw("
                users.id AS cashier_id,
                users.name AS cashier,
                COUNT(orders.id) AS order_count,
                COALESCE(SUM(orders.total_amount), 0) AS total_sales,
                COALESCE(AVG(orders.total_amount), 0) AS avg_order_value,
                AVG(CASE WHEN orders.completed_at IS NOT NULL
                    THEN EXTRACT(EPOCH FROM (orders.completed_at - orders.created_at)) / 60
                    ELSE NULL END) AS avg_processing_min
            ")
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_sales')
            ->get();
    }
}