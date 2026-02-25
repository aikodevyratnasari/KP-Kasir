{{-- resources/views/cashier/dashboard.blade.php --}}
@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.filter-btn {
    padding: 6px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    background: white;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.15s ease;
}
.filter-btn:hover {
    border-color: #6366f1;
    color: #6366f1;
}
.filter-btn.active {
    background: #6366f1;
    color: white;
    border-color: #6366f1;
}

/* Loading state */
.card-loading {
    opacity: 0.5;
    pointer-events: none;
    transition: opacity 0.2s;
}
.stat-value {
    transition: all 0.3s ease;
}
</style>

<div class="space-y-6">

    {{-- Header + Filter --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ now()->format('l, d F Y') }}
                @if(auth()->user()->store)
                    &bull; {{ auth()->user()->store->name }}
                @endif
            </p>
        </div>

        {{-- TOMBOL FILTER --}}
        <div class="flex gap-2">
            <button onclick="loadDashboard('today')" id="btn-today" class="filter-btn active">
                Hari Ini
            </button>
            <button onclick="loadDashboard('week')" id="btn-week" class="filter-btn">
                Minggu Ini
            </button>
            <button onclick="loadDashboard('month')" id="btn-month" class="filter-btn">
                Bulan Ini
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4" id="stats-section">
        <div class="card">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Penjualan</p>
            <p class="text-2xl font-bold text-gray-900 mt-1 stat-value" id="stat-sales">
                Rp {{ number_format($totalSalesToday ?? 0, 0, ',', '.') }}
            </p>
        </div>
        <div class="card">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Pesanan</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1 stat-value" id="stat-orders">
                {{ $totalOrdersToday ?? 0 }}
            </p>
        </div>
        <div class="card">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Rata-rata Transaksi</p>
            <p class="text-2xl font-bold text-gray-900 mt-1 stat-value" id="stat-avg">
                Rp {{ number_format($avgOrderValue ?? 0, 0, ',', '.') }}
            </p>
        </div>
        <div class="card">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pesanan Aktif</p>
            <div class="flex gap-3 mt-1 flex-wrap" id="stat-active-orders">
                @forelse($activeOrders ?? [] as $status => $count)
                    <div class="text-center">
                        <p class="text-xl font-bold text-gray-900">{{ $count }}</p>
                        <p class="text-xs text-gray-400 capitalize">{{ $status }}</p>
                    </div>
                @empty
                    <p class="text-2xl font-bold text-green-500">0</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Top Products --}}
        <div class="card">
            <h2 class="font-semibold text-gray-800 mb-4">🏆 Top Produk</h2>
            <div id="top-products-list">
                @forelse($topProducts ?? [] as $i => $p)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                            <span class="text-sm text-gray-700">{{ $p->product_name }}</span>
                        </div>
                        <span class="text-sm font-semibold">{{ $p->total_qty }} pcs</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-6">Belum ada data</p>
                @endforelse
            </div>
        </div>

        {{-- 7-day / Period Trend --}}
        <div class="card lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-800" id="chart-title">📈 Tren Penjualan 7 Hari Terakhir</h2>
                {{-- Loading spinner --}}
                <div id="chart-loading" class="hidden">
                    <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </div>
            </div>
            <div class="relative h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Recent Orders --}}
        <div class="card lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-800">🧾 Pesanan Terbaru</h2>
                <a href="{{ route('cashier.orders.index') }}" class="text-xs text-indigo-600 hover:underline">Lihat semua →</a>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left">
                        <th class="py-2 text-gray-500 font-medium">No. Pesanan</th>
                        <th class="py-2 text-gray-500 font-medium">Tipe</th>
                        <th class="py-2 text-gray-500 font-medium">Status</th>
                        <th class="py-2 text-right text-gray-500 font-medium">Total</th>
                    </tr>
                </thead>
                <tbody id="recent-orders-body">
                    @forelse($recentOrders ?? [] as $order)
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="py-2">
                                <a href="{{ route('cashier.orders.show', $order) }}" class="font-medium text-indigo-600 hover:underline">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td class="py-2 text-gray-600 capitalize">{{ str_replace('_', '-', $order->order_type) }}</td>
                            <td class="py-2"><span class="badge badge-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                            <td class="py-2 text-right font-semibold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-gray-400">Belum ada pesanan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Low Stock --}}
        <div class="card">
            <h2 class="font-semibold text-gray-800 mb-4">⚠️ Stok Menipis</h2>
            @forelse($lowStockProducts ?? [] as $p)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <span class="text-sm text-gray-700 truncate flex-1">{{ $p->name }}</span>
                    <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-semibold {{ $p->stock == 0 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $p->stock }} sisa
                    </span>
                </div>
            @empty
                <p class="text-sm text-green-600 text-center py-6">✅ Semua stok aman</p>
            @endforelse
        </div>

    </div>
</div>

<script>
let salesChart = null;

// ==========================================
// INISIALISASI CHART PERTAMA KALI
// ==========================================
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const trendData = @json($weeklyTrend ?? []);

    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: formatLabels(trendData),
            datasets: [{
                label: 'Total Penjualan (Rp)',
                data: trendData.map(i => i.total),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#6366f1'
            }]
        },
        options: chartOptions()
    });
});

// ==========================================
// FUNGSI LOAD DATA FILTER VIA AJAX
// ==========================================
function loadDashboard(period) {
    // 1. Update tombol aktif
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('btn-' + period).classList.add('active');

    // 2. Tampilkan loading
    setLoading(true);

    // 3. Fetch ke endpoint controller
    fetch(`{{ route('manager.dashboard.filter') }}?period=${period}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Network error');
        return res.json();
    })
    .then(data => {
        updateStats(data);
        updateChart(data.trend, period);
        updateTopProducts(data.topProducts);
        updateRecentOrders(data.recentOrders);
        setLoading(false);
    })
    .catch(err => {
        console.error('Error:', err);
        setLoading(false);
        alert('Gagal memuat data. Silakan coba lagi.');
    });
}

// ==========================================
// UPDATE STAT CARDS
// ==========================================
function updateStats(data) {
    document.getElementById('stat-sales').textContent =
        'Rp ' + Number(data.totalSales).toLocaleString('id-ID');

    document.getElementById('stat-orders').textContent = data.totalOrders;

    document.getElementById('stat-avg').textContent =
        'Rp ' + Number(data.avgOrder).toLocaleString('id-ID');

    // Active orders
    const el = document.getElementById('stat-active-orders');
    if (data.activeOrders && Object.keys(data.activeOrders).length > 0) {
        el.innerHTML = Object.entries(data.activeOrders).map(([status, count]) => `
            <div class="text-center">
                <p class="text-xl font-bold text-gray-900">${count}</p>
                <p class="text-xs text-gray-400 capitalize">${status}</p>
            </div>
        `).join('');
    } else {
        el.innerHTML = '<p class="text-2xl font-bold text-green-500">0</p>';
    }
}

// ==========================================
// UPDATE CHART
// ==========================================
function updateChart(trendData, period) {
    const titles = {
        today: '📈 Tren Penjualan Hari Ini',
        week:  '📈 Tren Penjualan Minggu Ini',
        month: '📈 Tren Penjualan Bulan Ini',
    };
    document.getElementById('chart-title').textContent = titles[period] ?? '📈 Tren Penjualan';

    salesChart.data.labels = formatLabels(trendData);
    salesChart.data.datasets[0].data = trendData.map(i => i.total);
    salesChart.update('active');
}

// ==========================================
// UPDATE TOP PRODUCTS
// ==========================================
function updateTopProducts(products) {
    const el = document.getElementById('top-products-list');
    if (!products || products.length === 0) {
        el.innerHTML = '<p class="text-sm text-gray-400 text-center py-6">Belum ada data</p>';
        return;
    }
    el.innerHTML = products.map((p, i) => `
        <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center">${i + 1}</span>
                <span class="text-sm text-gray-700">${p.product_name}</span>
            </div>
            <span class="text-sm font-semibold">${p.total_qty} pcs</span>
        </div>
    `).join('');
}

// ==========================================
// UPDATE RECENT ORDERS
// ==========================================
function updateRecentOrders(orders) {
    const el = document.getElementById('recent-orders-body');
    if (!orders || orders.length === 0) {
        el.innerHTML = '<tr><td colspan="4" class="py-6 text-center text-gray-400">Belum ada pesanan</td></tr>';
        return;
    }
    el.innerHTML = orders.map(order => `
        <tr class="border-b border-gray-50 hover:bg-gray-50">
            <td class="py-2">
                <a href="/cashier/orders/${order.id}" class="font-medium text-indigo-600 hover:underline">
                    ${order.order_number}
                </a>
            </td>
            <td class="py-2 text-gray-600 capitalize">${order.order_type.replace('_', '-')}</td>
            <td class="py-2"><span class="badge badge-${order.status}">${capitalize(order.status)}</span></td>
            <td class="py-2 text-right font-semibold">Rp ${Number(order.total_amount).toLocaleString('id-ID')}</td>
        </tr>
    `).join('');
}

// ==========================================
// HELPERS
// ==========================================
function formatLabels(trendData) {
    return trendData.map(item => {
        const d = new Date(item.date);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit' });
    });
}

function chartOptions() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: v => 'Rp ' + (v / 1000) + 'k' },
                grid: { drawBorder: false, color: '#f3f4f6' }
            },
            x: { grid: { display: false } }
        }
    };
}

function setLoading(state) {
    document.getElementById('chart-loading').classList.toggle('hidden', !state);
    document.querySelectorAll('.stat-value').forEach(el => {
        el.style.opacity = state ? '0.4' : '1';
    });
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}
</script>

@endsection