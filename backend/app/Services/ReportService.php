<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    // ══════════════════════════════════════════════════════════════════════
    // DASHBOARD
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Dashboard hari ini — dipanggil saat pertama kali load halaman.
     * Delegasikan ke dashboardAnalytics() + tambah data tambahan.
     */
    public function dashboardToday(int $storeId): array
    {
        $from = now()->startOfDay();
        $to   = now()->endOfDay();

        $analytics = $this->dashboardAnalytics($storeId, $from, $to);

        $salesTrend   = $this->salesTrend($storeId, 7);
        $recentOrders = Order::forStore($storeId)
            ->with('cashier', 'table')
            ->latest()
            ->limit(10)
            ->get();
        $lowStock     = Product::forStore($storeId)
            ->lowStock()
            ->with('category')
            ->get();
        $activeOrders = Order::forStore($storeId)
            ->active()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return array_merge($analytics, compact('salesTrend', 'recentOrders', 'lowStock', 'activeOrders'));
    }

    /**
     * Dashboard analytics — dipanggil Controller untuk filter AJAX maupun load awal.
     * Menerima $from & $to agar bisa filter per hari / minggu / bulan.
     */
    public function dashboardAnalytics(int $storeId, Carbon $from, Carbon $to): array
    {
        $fromStr = $from->startOfDay()->toDateTimeString();
        $toStr   = $to->endOfDay()->toDateTimeString();

        // ── 1. Summary (total sales & order count) ──────────────────────
        $summary = DB::table('orders')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$fromStr, $toStr])
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw('COUNT(*) as order_count, COALESCE(SUM(total_amount), 0) as total_sales')
            ->first();

        $totalSales  = (float) ($summary->total_sales  ?? 0);
        $totalOrders = (int)   ($summary->order_count  ?? 0);
        $avgOrder    = $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0;

        // ── 2. Top products ──────────────────────────────────────────────
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.store_id', $storeId)
            ->whereBetween('orders.created_at', [$fromStr, $toStr])
            ->whereNotIn('orders.status', ['cancelled'])
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // ── 3. Recent orders ─────────────────────────────────────────────
        $recentOrders = Order::forStore($storeId)
            ->with('cashier', 'table')
            ->whereBetween('created_at', [$fromStr, $toStr])
            ->latest()
            ->limit(10)
            ->get(['id', 'order_number', 'order_type', 'status', 'total_amount', 'created_at', 'cashier_id', 'table_id']);

        // ── 4. Trend harian (untuk line chart) ──────────────────────────
        $trend = DB::table('orders')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$fromStr, $toStr])
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw("DATE(created_at) as date, SUM(total_amount) as total")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ── 5. Penjualan per Jam ─────────────────────────────────────────
        $hourSales = DB::table('orders')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$fromStr, $toStr])
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw("EXTRACT(HOUR FROM created_at)::int AS hour, SUM(total_amount) AS total")
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('total', 'hour');

        // Fill jam 0–23 yang kosong dengan 0
        $hourSalesFull = collect(range(0, 23))->mapWithKeys(
            fn($h) => [$h => (float) ($hourSales[$h] ?? 0)]
        );

        // ── 6. Penjualan per Hari dalam Seminggu ─────────────────────────
        $dayOfWeekSales = DB::table('orders')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$fromStr, $toStr])
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw("TO_CHAR(created_at, 'Dy') AS day, EXTRACT(DOW FROM created_at)::int AS dow, SUM(total_amount) AS total")
            ->groupBy('day', 'dow')
            ->orderBy('dow')
            ->pluck('total', 'day');

        // ── 7. Statistik per Kategori ────────────────────────────────────
        $categoryStats = DB::table('order_items')
            ->join('orders',     'order_items.order_id',   '=', 'orders.id')
            ->join('products',   'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id',   '=', 'categories.id')
            ->where('orders.store_id', $storeId)
            ->whereBetween('orders.created_at', [$fromStr, $toStr])
            ->whereNotIn('orders.status', ['cancelled'])
            ->selectRaw('categories.name AS category, SUM(order_items.quantity) AS total_qty, SUM(order_items.subtotal) AS total_revenue')
            ->groupBy('categories.name')
            ->orderByDesc('total_revenue')
            ->get();

        // ── 8. Metode Pembayaran ─────────────────────────────────────────
        $paymentMethods = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.store_id', $storeId)
            ->where('payments.status', 'paid')
            ->whereBetween('payments.created_at', [$fromStr, $toStr])
            ->selectRaw('payment_method, COUNT(*) AS count, SUM(amount) AS total')
            ->groupBy('payment_method')
            ->get();

        // ── 9. Active orders (tidak terpengaruh filter tanggal) ──────────
        $activeOrders = Order::forStore($storeId)
            ->active()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return compact(
            'summary',
            'totalSales',
            'totalOrders',
            'avgOrder',
            'topProducts',
            'recentOrders',
            'trend',
            'hourSales',
            'hourSalesFull',
            'dayOfWeekSales',
            'categoryStats',
            'paymentMethods',
            'activeOrders'
        );
    }

    // ══════════════════════════════════════════════════════════════════════
    // SALES REPORT
    // ══════════════════════════════════════════════════════════════════════

    public function salesReport(int $storeId, Carbon $from, Carbon $to): array
    {
        $fromDt = $from->startOfDay()->toDateTimeString();
        $toDt   = $to->endOfDay()->toDateTimeString();

        // Base query — rebuild daripada clone agar aman
        $basePayments = fn() => Payment::whereHas('order', fn($q) => $q->forStore($storeId))
            ->paid()
            ->whereBetween('created_at', [$fromDt, $toDt]);

        $totalSales = (float) $basePayments()->sum('amount');
        $orderCount = Order::forStore($storeId)
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $avgSale = $orderCount > 0 ? round($totalSales / $orderCount, 2) : 0;

        // Breakdown per metode bayar
        $byMethod = $basePayments()
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        // Breakdown per tipe order
        $byType = Order::forStore($storeId)
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->whereNotIn('status', ['cancelled'])
            ->select('order_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('order_type')
            ->get();

        // Breakdown per status order
        $byStatus = Order::forStore($storeId)
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('status')
            ->get();

        // Trend harian
        $daily = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.store_id', $storeId)
            ->where('payments.status', 'paid')
            ->whereBetween('payments.created_at', [$fromDt, $toDt])
            ->selectRaw('DATE(payments.created_at) as date, SUM(payments.amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return compact('totalSales', 'orderCount', 'avgSale', 'byMethod', 'byType', 'byStatus', 'daily');
    }

    // ══════════════════════════════════════════════════════════════════════
    // PRODUCT REPORT
    // ══════════════════════════════════════════════════════════════════════

    public function productReport(int $storeId, Carbon $from, Carbon $to): array
    {
        $fromDt = $from->startOfDay()->toDateTimeString();
        $toDt   = $to->endOfDay()->toDateTimeString();

        $items = DB::table('order_items')
            ->join('orders',     'order_items.order_id',   '=', 'orders.id')
            ->join('products',   'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id',   '=', 'categories.id')
            ->where('orders.store_id', $storeId)
            ->whereNotIn('orders.status', ['cancelled'])
            ->whereBetween('orders.created_at', [$fromDt, $toDt])
            ->selectRaw('
                order_items.product_name,
                categories.name        AS category,
                SUM(order_items.quantity)  AS total_qty,
                SUM(order_items.subtotal)  AS total_revenue
            ')
            ->groupBy('order_items.product_name', 'categories.name')
            ->orderByDesc('total_qty')
            ->get();

        $byCategory = $items
            ->groupBy('category')
            ->map(fn($g) => [
                'qty'     => $g->sum('total_qty'),
                'revenue' => $g->sum('total_revenue'),
            ]);

        return [
            'top_by_qty'     => $items->take(10)->values(),
            'top_by_revenue' => $items->sortByDesc('total_revenue')->take(10)->values(),
            'least_sold'     => $items->sortBy('total_qty')->take(10)->values(),
            'by_category'    => $byCategory,
            'total_items'    => $items->count(),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // CASHIER REPORT
    // ══════════════════════════════════════════════════════════════════════

    public function cashierReport(int $storeId, Carbon $from, Carbon $to): array
    {
        $fromDt = $from->startOfDay()->toDateTimeString();
        $toDt   = $to->endOfDay()->toDateTimeString();

        $rows = DB::table('orders')
            ->join('users', 'orders.cashier_id', '=', 'users.id')
            ->where('orders.store_id', $storeId)
            ->whereNotIn('orders.status', ['cancelled'])
            ->whereBetween('orders.created_at', [$fromDt, $toDt])
            ->selectRaw("
                users.id                              AS cashier_id,
                users.name                            AS cashier,
                COUNT(orders.id)                      AS order_count,
                COALESCE(SUM(orders.total_amount), 0) AS total_sales,
                COALESCE(AVG(orders.total_amount), 0) AS avg_order_value,
                AVG(
                    CASE
                        WHEN orders.completed_at IS NOT NULL
                        THEN EXTRACT(EPOCH FROM (orders.completed_at - orders.created_at)) / 60
                        ELSE NULL
                    END
                )                                     AS avg_processing_min
            ")
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_sales')
            ->get();

        return [
            'cashiers'    => $rows,
            'total_sales' => $rows->sum('total_sales'),
            'best'        => $rows->first(),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // REVENUE ANALYTICS
    // ══════════════════════════════════════════════════════════════════════

    public function revenueAnalytics(int $storeId, string $period, Carbon $from, Carbon $to): array
    {
        $fromDt = $from->startOfDay()->toDateTimeString();
        $toDt   = $to->endOfDay()->toDateTimeString();

        $format = match ($period) {
            'weekly'  => 'YYYY"-W"IW',   // → "2025-W03"  lebih readable
            'monthly' => 'YYYY-MM',       // → "2025-03"
            'yearly'  => 'YYYY',          // → "2025"
            default   => 'YYYY-MM-DD',    // daily → "2025-03-15"
        };

        $rows = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.store_id', $storeId)
            ->where('payments.status', 'paid')
            ->whereBetween('payments.created_at', [$fromDt, $toDt])
            ->selectRaw("TO_CHAR(payments.created_at, '{$format}') AS period, SUM(payments.amount) AS revenue, COUNT(*) AS count")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'data'        => $rows,
            'labels'      => $rows->pluck('period'),
            'values'      => $rows->pluck('revenue'),
            'total'       => (float) $rows->sum('revenue'),
            'period_type' => $period,
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Tren penjualan N hari terakhir — untuk chart di dashboard awal.
     * Setiap hari yang kosong diisi 0 agar chart tidak bolong.
     */
    private function salesTrend(int $storeId, int $days = 7): array
    {
        $rows = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.store_id', $storeId)
            ->where('payments.status', 'paid')
            ->where('payments.created_at', '>=', now()->subDays($days)->startOfDay())
            ->selectRaw('DATE(payments.created_at) as date, SUM(payments.amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date          = now()->subDays($i)->format('Y-m-d');
            $result[$date] = (float) ($rows[$date] ?? 0);
        }

        return $result;
    }
}