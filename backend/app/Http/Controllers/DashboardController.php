<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $storeId = $request->get('_store_id');
        [$start, $end] = $this->dateRange('today');

        return view('manager.dashboard', array_merge(
            $this->buildStats($storeId, $start, $end),
            [
                'trendByStatus'    => $this->trendByStatus($storeId, $start, $end),
                'paymentBreakdown' => $this->paymentBreakdown($storeId, $start, $end),
            ]
        ));
    }

    public function filter(Request $request): JsonResponse
    {
        $storeId = $request->get('_store_id');
        $period  = $request->input('period', 'today');

        if ($period === 'custom') {
            $start = Carbon::parse($request->input('from'))->startOfDay();
            $end   = Carbon::parse($request->input('to'))->endOfDay();
        } else {
            [$start, $end] = $this->dateRange($period);
        }

        $stats = $this->buildStats($storeId, $start, $end);

        return response()->json([
            'totalSales'       => $stats['totalSalesToday'],
            'totalOrders'      => $stats['totalOrdersToday'],
            'avgOrder'         => $stats['avgOrderValue'],
            'activeOrders'     => $stats['activeOrders'],
            'trendByStatus'    => $this->trendByStatus($storeId, $start, $end),
            'paymentBreakdown' => $this->paymentBreakdown($storeId, $start, $end),
            'topProducts'      => $stats['topProducts'],
            'recentOrders'     => $stats['recentOrders'],
        ]);
    }

    // ── Private helpers ──────────────────────────────────────────────────

    private function dateRange(string $period): array
    {
        return match ($period) {
            'today' => [now()->startOfDay(),   now()->endOfDay()],
            'week'  => [now()->startOfWeek(),  now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year'  => [now()->startOfYear(),  now()->endOfYear()],
            'all'   => [Carbon::createFromTimestamp(0), now()],
            default => [now()->startOfDay(),   now()->endOfDay()],
        };
    }

    private function buildStats(int $storeId, $start, $end): array
    {
        $orders = Order::forStore($storeId)
            ->whereBetween('created_at', [$start, $end]);

        $totalSales  = (clone $orders)->where('status', 'completed')->sum('total_amount');
        $totalOrders = (clone $orders)->count();
        $avgOrder    = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        $activeOrders = Order::forStore($storeId)
            ->active()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $topProducts = \DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.store_id', $storeId)
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as total_qty')
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $recentOrders = Order::forStore($storeId)
            ->with('payments')
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->limit(10)
            ->get();

        $lowStockProducts = \App\Models\Product::where('store_id', $storeId)
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->get();

        return compact(
            'totalSales', 'totalOrders', 'avgOrder',
            'activeOrders', 'topProducts', 'recentOrders', 'lowStockProducts',
        ) + [
            'totalSalesToday' => $totalSales,
            'totalOrdersToday' => $totalOrders,
            'avgOrderValue'    => $avgOrder,
        ];
    }

    /**
     * Tren harian pesanan — hanya status completed & cancelled.
     * Format output: [['date' => '2024-04-01', 'completed' => 5, 'cancelled' => 1], ...]
     */
    private function trendByStatus(int $storeId, $start, $end): array
    {
        $rows = Order::forStore($storeId)
            ->selectRaw('DATE(created_at) as date, status, COUNT(*) as count')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['completed', 'cancelled'])
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get();

        return $rows
            ->groupBy('date')
            ->map(fn($items, $date) => array_merge(
                ['date' => $date],
                $items->pluck('count', 'status')->toArray()
            ))
            ->values()
            ->toArray();
    }

    /**
     * Breakdown metode pembayaran berdasarkan total nilai transaksi lunas.
     * Format output: [['payment_method' => 'cash', 'total' => 500000], ...]
     */
    private function paymentBreakdown(int $storeId, $start, $end): array
    {
        return Payment::whereHas('order', fn($q) => $q->where('store_id', $storeId))
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }
}