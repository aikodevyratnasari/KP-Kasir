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
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(): View
    {
        return view('manager.reports.index');
    }

    public function dashboard(Request $request): View
    {
        $storeId = $request->get('_store_id');
        $raw     = $this->reportService->dashboardToday($storeId);
        $data    = array_merge($raw, [
            'totalSalesToday'  => $raw['totalSales']  ?? 0,
            'totalOrdersToday' => $raw['totalOrders'] ?? 0,
            'avgOrderValue'    => $raw['avgOrder']    ?? 0,
            'weeklyTrend'      => collect($raw['trend'] ?? [])->map(fn($r) => (object)['date' => $r->date, 'total' => $r->total])->values()->toArray(),
            'lowStockProducts' => $raw['lowStock'] ?? collect(),
        ]);
        return view('manager.dashboard', $data);
    }

    public function dashboardFilter(Request $request): JsonResponse
    {
        $storeId = $request->get('_store_id');
        $period  = $request->period ?? 'today';
        $range   = match ($period) {
            'week'   => [now()->startOfWeek(),  now()->endOfWeek()],
            'month'  => [now()->startOfMonth(), now()->endOfMonth()],
            'year'   => [now()->startOfYear(),  now()->endOfYear()],
            'all'    => [now()->subYears(10)->startOfDay(), now()->endOfDay()],
            'custom' => [Carbon::parse($request->from)->startOfDay(), Carbon::parse($request->to)->endOfDay()],
            default  => [now()->startOfDay(), now()->endOfDay()],
        };
        [$from, $to] = $range;
        $raw = $this->reportService->dashboardAnalytics($storeId, $from, $to);
        return response()->json([
            'totalSales'   => $raw['totalSales']   ?? 0,
            'totalOrders'  => $raw['totalOrders']  ?? 0,
            'avgOrder'     => $raw['avgOrder']      ?? 0,
            'activeOrders' => $raw['activeOrders']  ?? [],
            'topProducts'  => $raw['topProducts']   ?? [],
            'recentOrders' => $raw['recentOrders']  ?? [],
            'trend'        => collect($raw['trend'] ?? [])->map(fn($r) => ['date' => $r->date, 'total' => $r->total])->values(),
        ]);
    }

    // ── Sales ─────────────────────────────────────────────────────────────
    public function sales(Request $request): View
    {
        [$from, $to] = $this->parseDates($request);
        $raw = $this->reportService->salesReport($request->get('_store_id'), $from, $to);
        return view('manager.reports.sales', [
            'from'             => $from,
            'to'               => $to,
            'totalSales'       => $raw['totalSales']       ?? 0,
            'totalOrders'      => $raw['orderCount']       ?? 0,
            'avgTransaction'   => $raw['avgSale']          ?? 0,
            'byPaymentMethod'  => collect($raw['byMethod'] ?? [])->values(),
            'byOrderType'      => $raw['byType']           ?? collect(),
            'byStatus'         => $raw['byStatus']         ?? collect(),
            'byPaymentStatus'  => $raw['byPaymentStatus']  ?? collect(),
            'dailyTrend'       => $raw['daily']            ?? collect(),
        ]);
    }

    // ── Products ──────────────────────────────────────────────────────────
    public function products(Request $request): View
    {
        [$from, $to] = $this->parseDates($request);
        $data = $this->reportService->productReport($request->get('_store_id'), $from, $to);
        return view('manager.reports.products', array_merge($data, compact('from', 'to')));
    }

    // ── Revenue ───────────────────────────────────────────────────────────
    public function revenue(Request $request): View
    {
        [$from, $to] = $this->parseDates($request);
        $period = $request->period ?? 'daily';
        $data   = $this->reportService->revenueAnalytics($request->get('_store_id'), $period, $from, $to);
        return view('manager.reports.revenue', compact('data', 'from', 'to', 'period'));
    }

    // ── Cashiers ──────────────────────────────────────────────────────────
    public function cashiers(Request $request): View
    {
        [$from, $to] = $this->parseDates($request);
        $data = $this->reportService->cashierReport($request->get('_store_id'), $from, $to);
        return view('manager.reports.cashiers', compact('data', 'from', 'to'));
    }

    // ── Payments ──────────────────────────────────────────────────────────
    public function payments(Request $request): View
    {
        [$from, $to] = $this->parseDates($request);
        $storeId = $request->get('_store_id');
    
        $filterConfirmedPending = function ($q) {
            $q->where('status', '!=', 'pending')
            ->orWhereHas('order', function ($oq) {
                $oq->whereDoesntHave('payments', fn($pq) => $pq->whereIn('status', ['paid', 'refunded']));
            });
        };
    
        // ── Daftar transaksi ──────────────────────────────────────────────
        $payments = \App\Models\Payment::whereHas('order', fn($q) => $q->where('store_id', $storeId))
            ->with(['order', 'cashier'])
            ->when($request->method, fn($q, $m) => $q->where('payment_method', $m))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->where($filterConfirmedPending)
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->latest()
            ->paginate(50)
            ->withQueryString();
    
        // ── Summary hanya paid ────────────────────────────────────────────
        $summary = \App\Models\Payment::whereHas('order', fn($q) => $q->where('store_id', $storeId))
            ->where('status', 'paid')
            ->when($request->method, fn($q, $m) => $q->where('payment_method', $m))
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('SUM(amount) as total, COUNT(*) as count')
            ->first();
    
        // ── Summary per status ────────────────────────────────────────────
        $summaryByStatus = \App\Models\Payment::whereHas('order', fn($q) => $q->where('store_id', $storeId))
            ->when($request->method, fn($q, $m) => $q->where('payment_method', $m))
            ->where($filterConfirmedPending)
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('status, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->keyBy('status');
    
        // ── Breakdown per metode (hanya paid) — BARU ─────────────────────
        // Digunakan untuk card ringkasan per metode di view laporan.
        $byMethodSummary = \App\Models\Payment::whereHas('order', fn($q) => $q->where('store_id', $storeId))
            ->where('status', 'paid')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('payment_method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->orderByRaw("CASE payment_method WHEN 'cash' THEN 1 WHEN 'card' THEN 2 WHEN 'qris' THEN 3 WHEN 'ewallet' THEN 4 WHEN 'bank_transfer' THEN 5 ELSE 6 END")
            ->get();
    
        // ── Pesanan belum bayar ───────────────────────────────────────────
        $unpaidOrders = \App\Models\Order::forStore($storeId)
            ->whereIn('status', ['pending', 'cooking', 'ready'])
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->with(['cashier', 'table'])
            ->get();
    
        if ($request->status && $request->status !== 'pending') {
            $unpaidOrders = collect();
        }
    
        return view('manager.reports.payments', compact(
            'payments', 'from', 'to', 'summary', 'summaryByStatus',
            'unpaidOrders', 'byMethodSummary'  // ← byMethodSummary ditambahkan
        ));
    }

    // ── Download ──────────────────────────────────────────────────────────
    public function download(Request $request, string $type): mixed
    {
        $storeId  = $request->get('_store_id');
        $from     = $request->from   ?? now()->startOfMonth()->format('Y-m-d');
        $to       = $request->to     ?? now()->format('Y-m-d');
        $format   = $request->format ?? 'xlsx';
        $period   = $request->period ?? 'daily';
        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate   = Carbon::parse($to)->endOfDay();

        if ($format === 'pdf') {
            $viewData = $this->buildPdfData($type, $storeId, $fromDate, $toDate, $from, $to, $period, $request);
            return Pdf::loadView('manager.reports.pdf', $viewData)
                ->setPaper('a4', 'portrait')
                ->download("laporan-{$type}-{$from}-{$to}.pdf");
        }

        $export     = $this->buildExport($type, $storeId, $fromDate, $toDate, $from, $to, $period, $request);
        $writerType = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        return Excel::download($export, "laporan-{$type}-{$from}-{$to}.{$format}", $writerType);
    }

    // ── Send Email ────────────────────────────────────────────────────────
    public function sendReportEmail(Request $request, string $type): JsonResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);

        $storeId  = $request->get('_store_id');
        $from     = $request->from   ?? now()->startOfMonth()->format('Y-m-d');
        $to       = $request->to     ?? now()->format('Y-m-d');
        $period   = $request->period ?? 'daily';
        $format   = $request->format ?? 'xlsx';
        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate   = Carbon::parse($to)->endOfDay();
        $ext      = match($format) { 'csv' => 'csv', 'pdf' => 'pdf', default => 'xlsx' };
        $fileName = "laporan-{$type}-{$from}-{$to}.{$ext}";
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;

        try {
            if ($format === 'pdf') {
                $viewData = $this->buildPdfData($type, $storeId, $fromDate, $toDate, $from, $to, $period, $request);
                file_put_contents($filePath, Pdf::loadView('manager.reports.pdf', $viewData)->setPaper('a4')->output());
            } else {
                $export     = $this->buildExport($type, $storeId, $fromDate, $toDate, $from, $to, $period, $request);
                $writerType = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
                file_put_contents($filePath, Excel::raw($export, $writerType));
            }
            Mail::to($request->email)->send(new ReportMail($type, $from, $to, $filePath, $fileName));
            @unlink($filePath);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            @unlink($filePath);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Build Export ──────────────────────────────────────────────────────
    private function buildExport(string $type, int $storeId, $fromDate, $toDate, string $from, string $to, string $period, ?Request $request = null): mixed
    {
        return match ($type) {
            'sales' => (function () use ($storeId, $fromDate, $toDate, $from, $to) {
                $raw = $this->reportService->salesReport($storeId, $fromDate, $toDate);
                return new SalesReportExport(
                    dailyTrend:      $raw['daily']           ?? collect(),
                    byPaymentMethod: collect($raw['byMethod'] ?? [])->values(),
                    byOrderType:     $raw['byType']          ?? collect(),
                    byStatus:        $raw['byStatus']        ?? collect(),
                    totalSales:      (float) ($raw['totalSales'] ?? 0),
                    totalOrders:     (int)   ($raw['orderCount'] ?? 0),
                    avgTransaction:  (float) ($raw['avgSale']    ?? 0),
                    from: $from, to: $to,
                    byPaymentStatus: $raw['byPaymentStatus'] ?? collect(),
                );
            })(),

            'products' => (function () use ($storeId, $fromDate, $toDate, $from, $to) {
                $raw = $this->reportService->productReport($storeId, $fromDate, $toDate);
                return new ProductReportExport(
                    topByQty:         $raw['top_by_qty']        ?? collect(),
                    topByRevenue:     $raw['top_by_revenue']    ?? collect(),
                    cancelledSummary: $raw['cancelled_summary'] ?? null,
                    from: $from, to: $to,
                    cancelledItems:   $raw['cancelled_items']   ?? collect(),
                );
            })(),

            'revenue' => (function () use ($storeId, $period, $fromDate, $toDate, $from, $to) {
                $raw = $this->reportService->revenueAnalytics($storeId, $period, $fromDate, $toDate);
                return new RevenueReportExport(
                    data:             $raw['data']               ?? collect(),
                    period:           $period,
                    from:             $from,
                    to:               $to,
                    nonRevenueOrders: $raw['non_revenue_orders'] ?? collect(),
                    refundSummary:    $raw['refund_summary']     ?? null,
                );
            })(),

            'cashiers' => (function () use ($storeId, $fromDate, $toDate, $from, $to) {
                $raw = $this->reportService->cashierReport($storeId, $fromDate, $toDate);
                return new CashierReportExport(
                    cashiers:            $raw['cashiers']              ?? collect(),
                    cancelledPerCashier: $raw['cancelled_per_cashier'] ?? collect(),
                    from: $from, to: $to,
                );
            })(),

            'payments' => new \App\Exports\PaymentReportExport(
                payments: \App\Models\Payment::whereHas('order', fn($q) => $q->where('store_id', $storeId))
                    ->with(['order', 'cashier'])
                    ->when($request?->method, fn($q, $m) => $q->where('payment_method', $m))
                    ->when($request?->status, fn($q, $s) => $q->where('status', $s))
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->latest()->get(),
                unpaidOrders: \App\Models\Order::forStore($storeId)
                    ->whereIn('status', ['pending', 'cooking', 'ready'])
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->with('cashier')->get(),
                from: $from, to: $to,
            ),

            default => abort(404),
        };
    }

    // ── Build PDF Data ────────────────────────────────────────────────────
    private function buildPdfData(string $type, int $storeId, $fromDate, $toDate, string $from, string $to, string $period, ?Request $request = null): array
    {
        $base = compact('type', 'from', 'to', 'period');
        return match ($type) {
            'sales' => array_merge($base, (function () use ($storeId, $fromDate, $toDate) {
                $raw = $this->reportService->salesReport($storeId, $fromDate, $toDate);
                return [
                    'totalSales'      => $raw['totalSales']      ?? 0,
                    'totalOrders'     => $raw['orderCount']      ?? 0,
                    'avgTransaction'  => $raw['avgSale']         ?? 0,
                    'byPaymentMethod' => collect($raw['byMethod'] ?? [])->values(),
                    'byOrderType'     => $raw['byType']          ?? collect(),
                    'byStatus'        => $raw['byStatus']        ?? collect(),
                    'byPaymentStatus' => $raw['byPaymentStatus'] ?? collect(),
                    'dailyTrend'      => $raw['daily']           ?? collect(),
                ];
            })()),

            'products' => array_merge($base, (function () use ($storeId, $fromDate, $toDate) {
                $raw = $this->reportService->productReport($storeId, $fromDate, $toDate);
                return [
                    'topByQty'         => $raw['top_by_qty']        ?? collect(),
                    'topByRevenue'     => $raw['top_by_revenue']    ?? collect(),
                    'cancelledSummary' => $raw['cancelled_summary'] ?? null,
                    'cancelledItems'   => $raw['cancelled_items']   ?? collect(),
                ];
            })()),

            'revenue' => array_merge($base, (function () use ($storeId, $fromDate, $toDate, $period) {
                $raw = $this->reportService->revenueAnalytics($storeId, $period, $fromDate, $toDate);
                return [
                    'data'          => $raw,
                    'refundSummary' => $raw['refund_summary'] ?? null,
                ];
            })()),

            'cashiers' => array_merge($base, (function () use ($storeId, $fromDate, $toDate) {
                $raw = $this->reportService->cashierReport($storeId, $fromDate, $toDate);
                return ['data' => $raw, 'cancelledPerCashier' => $raw['cancelled_per_cashier'] ?? collect()];
            })()),

            'payments' => array_merge($base, [
                'payments' => \App\Models\Payment::whereHas('order', fn($q) => $q->where('store_id', $storeId))
                    ->with(['order', 'cashier'])
                    ->when($request?->method, fn($q, $m) => $q->where('payment_method', $m))
                    ->when($request?->status, fn($q, $s) => $q->where('status', $s))
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->latest()->get(),
                'unpaidOrders' => \App\Models\Order::forStore($storeId)
                    ->whereIn('status', ['pending', 'cooking', 'ready'])
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->with('cashier')->get(),
            ]),

            default => abort(404),
        };
    }

    private function parseDates(Request $request): array
    {
        $from = $request->from ? Carbon::parse($request->from)->startOfDay() : now()->copy()->startOfMonth();
        $to   = $request->to   ? Carbon::parse($request->to)->endOfDay()     : now()->copy()->endOfDay();
        return [$from, $to];
    }
}