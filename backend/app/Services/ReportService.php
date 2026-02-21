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
    // ── Dashboard ──────────────────────────────────────────────────────────
    public function dashboardToday(int $storeId): array
    {
        $orders   = Order::forStore($storeId)->today();
        $payments = Payment::whereHas('order', fn($q) => $q->forStore($storeId))->today()->paid();

        $totalSales    = (float) $payments->sum('amount');
        $orderCount    = $orders->count();
        $avgOrderValue = $orderCount > 0 ? round($totalSales / $orderCount, 2) : 0;

        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.store_id', $storeId)
            ->whereDate('orders.created_at', today())
            ->whereNotIn('orders.status', ['cancelled'])
            ->select('product_name', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.subtotal) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(3)
            ->get();

        $salesTrend = $this->salesTrend($storeId, 7);

        $recentOrders = Order::forStore($storeId)
            ->with('cashier', 'table')
            ->latest()
            ->limit(10)
            ->get();

        $lowStock = Product::forStore($storeId)->lowStock()->with('category')->get();

        $activeOrders = Order::forStore($storeId)->active()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return compact('totalSales', 'orderCount', 'avgOrderValue', 'topProducts', 'salesTrend', 'recentOrders', 'lowStock', 'activeOrders');
    }

    // ── Sales Report ──────────────────────────────────────────────────────
    public function salesReport(int $storeId, Carbon $from, Carbon $to): array
    {
        $payments = Payment::whereHas('order', fn($q) => $q->forStore($storeId))
            ->paid()
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);

        $totalSales   = (float) $payments->sum('amount');
        $orderCount   = Order::forStore($storeId)
            ->whereBetween('created_at', [$from, $to])
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $byMethod = $payments->clone()
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        $byType = Order::forStore($storeId)
            ->whereBetween('created_at', [$from, $to])
            ->whereNotIn('status', ['cancelled'])
            ->select('order_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('order_type')
            ->get();

        $daily = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.store_id', $storeId)
            ->where('payments.status', 'paid')
            ->whereBetween('payments.created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('DATE(payments.created_at) as date, SUM(payments.amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return compact('totalSales', 'orderCount', 'byMethod', 'byType', 'daily');
    }

    // ── Product Performance ───────────────────────────────────────────────
    public function productReport(int $storeId, Carbon $from, Carbon $to): array
    {
        $items = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.store_id', $storeId)
            ->whereNotIn('orders.status', ['cancelled'])
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('order_items.product_name, categories.name as category, SUM(order_items.quantity) as total_qty, SUM(order_items.subtotal) as total_revenue')
            ->groupBy('order_items.product_name', 'categories.name')
            ->orderByDesc('total_qty')
            ->get();

        $byCategory = $items->groupBy('category')->map(fn($g) => [
            'qty'     => $g->sum('total_qty'),
            'revenue' => $g->sum('total_revenue'),
        ]);

        return [
            'top_by_qty'     => $items->take(10),
            'top_by_revenue' => $items->sortByDesc('total_revenue')->take(10)->values(),
            'least_sold'     => $items->sortBy('total_qty')->take(10)->values(),
            'by_category'    => $byCategory,
        ];
    }

    // ── Cashier Performance ───────────────────────────────────────────────
    public function cashierReport(int $storeId, Carbon $from, Carbon $to): array
    {
        return DB::table('orders')
            ->join('users', 'orders.cashier_id', '=', 'users.id')
            ->where('orders.store_id', $storeId)
            ->whereNotIn('orders.status', ['cancelled'])
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('users.name as cashier, COUNT(orders.id) as order_count,
                SUM(orders.total_amount) as total_sales,
                AVG(orders.total_amount) as avg_order_value,
                AVG(TIMESTAMPDIFF(MINUTE, orders.created_at, orders.completed_at)) as avg_processing_min')
            ->groupBy('users.name')
            ->orderByDesc('total_sales')
            ->get()
            ->toArray();
    }

    // ── Revenue Analytics ─────────────────────────────────────────────────
    public function revenueAnalytics(int $storeId, string $period, Carbon $from, Carbon $to): array
    {
        $format = match ($period) {
            'daily'   => '%Y-%m-%d',
            'weekly'  => '%Y-%u',
            'monthly' => '%Y-%m',
            'yearly'  => '%Y',
            default   => '%Y-%m-%d',
        };

        $data = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.store_id', $storeId)
            ->where('payments.status', 'paid')
            ->whereBetween('payments.created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw("DATE_FORMAT(payments.created_at, '{$format}') as period, SUM(payments.amount) as revenue")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('revenue', 'period');

        return $data->toArray();
    }

    // ── Helpers ───────────────────────────────────────────────────────────
    private function salesTrend(int $storeId, int $days): array
    {
        $rows = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.store_id', $storeId)
            ->where('payments.status', 'paid')
            ->where('payments.created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(payments.created_at) as date, SUM(payments.amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        // Fill missing days with 0
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date          = now()->subDays($i)->format('Y-m-d');
            $result[$date] = (float) ($rows[$date] ?? 0);
        }

        return $result;
    }
}