{{-- resources/views/manager/dashboard.blade.php --}}
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
.filter-btn:hover { border-color: #2D54BF; color: #2D54BF; }
.filter-btn.active { background: #2D54BF; color: white; border-color: #2D54BF; }
.stat-value { transition: all 0.3s ease; }
</style>

<div class="space-y-6">

    {{-- Header + Filter --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                @if(auth()->user()->store)
                    &bull; {{ auth()->user()->store->name }}
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button onclick="loadDashboard('today')"   id="btn-today"   class="filter-btn">Hari Ini</button>
            <button onclick="loadDashboard('week')"    id="btn-week"    class="filter-btn">Minggu Ini</button>
            <button onclick="loadDashboard('month')"   id="btn-month"   class="filter-btn">Bulan Ini</button>
            <button onclick="loadDashboard('year')"    id="btn-year"    class="filter-btn">Tahun Ini</button>
            <button onclick="loadDashboard('all')"     id="btn-all"     class="filter-btn active">Total</button>
            <button onclick="toggleCustomRange()"      id="btn-custom"  class="filter-btn flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Custom
            </button>

            {{-- Custom date range (hidden by default) --}}
            <div id="custom-range" class="hidden flex items-center gap-2">
                <input type="date" id="custom-from"
                       value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                       class="form-input text-sm py-1.5 w-36">
                <span class="text-gray-400 text-sm">–</span>
                <input type="date" id="custom-to"
                       value="{{ now()->format('Y-m-d') }}"
                       class="form-input text-sm py-1.5 w-36">
                <button onclick="loadCustomRange()"
                        class="ml-auto inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                        style="background-color: #2D54BF; border: 1px solid #2D54BF;"
                        onmouseover="this.style.backgroundColor='#1e3d8f'"
                        onmouseout="this.style.backgroundColor='#2D54BF'">Terapkan</button>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
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
        {{-- Donut: Metode Pembayaran --}}
        <div class="card">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-250">
                <h2 class="font-semibold text-gray-800">Metode Pembayaran</h2>
            </div>
            <div class="relative h-56">
                <canvas id="paymentChart"></canvas>
            </div>
            <div class="flex flex-wrap gap-3 mt-3" id="payment-legend"></div>
        </div>

        {{-- Chart: Lunas vs Dibatalkan --}}
        <div class="card lg:col-span-2">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-250">
                <h2 class="font-semibold text-gray-800" id="chart-title">Pesanan Total</h2>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1.5 text-xs text-gray-500">
                        <span class="w-2.5 h-2.5 rounded-full inline-block" style="background:#10b981"></span>Lunas
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-gray-500">
                        <span class="w-2.5 h-2.5 rounded-full inline-block" style="background:#ef4444"></span>Dibatalkan
                    </div>
                    <div id="chart-loading" class="hidden">
                        <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="relative h-64">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Orders --}}
        <div class="card lg:col-span-2">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-250">
                <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                    Pesanan Terbaru
                </h2>
                 <a href="{{ route('cashier.orders.index') }}"
                        class="ml-auto inline-flex items-center px-2 py-1 text-xs font-medium text-white rounded-lg transition-colors"
                        style="background-color: #2D54BF; border: 1px solid #2D54BF;"
                        onmouseover="this.style.backgroundColor='#1e3d8f'"
                        onmouseout="this.style.backgroundColor='#2D54BF'">
                        Lihat Semua
                 </a>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left">
                        <th class="py-2 text-gray-500 font-medium w-28">No. Pesanan</th>
                        <th class="py-2 text-gray-500 font-medium w-28">Tipe</th>
                        <th class="py-2 text-gray-500 font-medium w-28">Status</th>
                        <th class="py-2 text-left text-gray-500 font-medium w-28">Total</th>
                    </tr>
                </thead>
                <tbody id="recent-orders-body">
                    @forelse($recentOrders ?? [] as $order)
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="py-2 w-48">
                                <a href="{{ route('cashier.orders.show', $order) }}" class="font-medium text-indigo-600 hover:underline">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td class="py-2 text-gray-600 capitalize w-28">{{ str_replace('_', '-', $order->order_type) }}</td>
                            <td class="py-2 w-28"><span class="badge badge-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                            <td class="py-2 text-left font-semibold w-28">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-gray-400">Belum ada pesanan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Low Stock --}}
        <div class="card">
            <div class="flex items-center justify-between mb-2 pb-1 border-b border-gray-250">
                <h2 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    Stok Menipis
                </h2>
            </div>
            @forelse($lowStockProducts ?? [] as $p)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <span class="text-sm text-gray-700 truncate flex-1">{{ $p->name }}</span>
                    <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-semibold {{ $p->stock == 0 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $p->stock }} sisa
                    </span>
                </div>
            @empty
                <div class="text-sm text-gray-400 text-center py-6 flex flex-col items-center gap-2">
                    Semua stok aman
                </div>
            @endforelse
        </div>
    </div>


</div>

<script>
let statusChart  = null;
let paymentChart = null;

const PAYMENT_COLORS = {
    cash:          '#10b981',
    card:          '#6366f1',
    qris:          '#8b5cf6',
    ewallet:       '#f59e0b',
    bank_transfer: '#ec4899',
};
const PAYMENT_LABELS = {
    cash: 'Tunai', card: 'Kartu', qris: 'QRIS',
    ewallet: 'E-Wallet', bank_transfer: 'Transfer Bank',
};

document.addEventListener('DOMContentLoaded', function () {
    const trendByStatus  = @json($trendByStatus ?? []);
    const paymentBreakdown = @json($paymentBreakdown ?? []);

    statusChart  = buildStatusChart(trendByStatus);
    paymentChart = buildPaymentChart(paymentBreakdown);
});

function buildStatusChart(data) {
    const ctx = document.getElementById('statusChart').getContext('2d');
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: formatLabels(data),
            datasets: [
                {
                    label: 'Lunas',
                    data: data.map(d => d.completed ?? 0),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.1)',
                    borderWidth: 2.5, fill: true, tension: 0.4,
                    pointRadius: 4, pointBackgroundColor: '#10b981',
                },
                {
                    label: 'Dibatalkan',
                    data: data.map(d => d.cancelled ?? 0),
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,0.08)',
                    borderWidth: 2.5, fill: true, tension: 0.4,
                    pointRadius: 4, pointBackgroundColor: '#ef4444',
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    grid: { color: '#f3f4f6' }
                },
                x: { grid: { display: false } }
            }
        }
    });
}

function buildPaymentChart(breakdown) {
    const ctx    = document.getElementById('paymentChart').getContext('2d');
    const labels = breakdown.map(p => PAYMENT_LABELS[p.payment_method] ?? p.payment_method);
    const colors = breakdown.map(p => PAYMENT_COLORS[p.payment_method] ?? '#94a3b8');
    const values = breakdown.map(p => Number(p.total));

    renderPaymentLegend(breakdown, labels, colors);

    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, hoverOffset: 6 }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct   = ((ctx.parsed / total) * 100).toFixed(1);
                            return ` Rp ${Number(ctx.parsed).toLocaleString('id-ID')} (${pct}%)`;
                        }
                    }
                }
            }
        }
    });
}

function renderPaymentLegend(breakdown, labels, colors) {
    const el = document.getElementById('payment-legend');
    el.innerHTML = breakdown.map((_, i) =>
        `<div class="flex items-center gap-1.5 text-xs text-gray-500">
            <span class="w-2.5 h-2.5 rounded-full inline-block" style="background:${colors[i]}"></span>
            ${labels[i]}
        </div>`
    ).join('');
}

function toggleCustomRange() {
    const el  = document.getElementById('custom-range');
    const btn = document.getElementById('btn-custom');
    const hidden = el.classList.contains('hidden');
    el.classList.toggle('hidden', !hidden);
    if (hidden) {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    } else {
        btn.classList.remove('active');
    }
}

function loadCustomRange() {
    const from = document.getElementById('custom-from').value;
    const to   = document.getElementById('custom-to').value;
    if (!from || !to) return;

    document.getElementById('chart-title').textContent = `Pesanan ${from} – ${to}`;
    setLoading(true);

    fetch(`{{ route('manager.dashboard.filter') }}?period=custom&from=${from}&to=${to}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(res => { if (!res.ok) throw new Error('Network error'); return res.json(); })
    .then(data => {
        updateStats(data);
        updateStatusChart(data.trendByStatus, 'custom');
        updatePaymentChart(data.paymentBreakdown);
        updateRecentOrders(data.recentOrders);
        setLoading(false);
    })
    .catch(err => { console.error(err); setLoading(false); });
}

function loadDashboard(period) {
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('btn-' + period).classList.add('active');
    document.getElementById('custom-range').classList.add('hidden');
    setLoading(true);

    fetch(`{{ route('manager.dashboard.filter') }}?period=${period}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(res => { if (!res.ok) throw new Error('Network error'); return res.json(); })
    .then(data => {
        updateStats(data);
        updateStatusChart(data.trendByStatus, period);
        updatePaymentChart(data.paymentBreakdown);
        updateRecentOrders(data.recentOrders);
        setLoading(false);
    })
    .catch(err => { console.error(err); setLoading(false); });
}

function updateStats(data) {
    document.getElementById('stat-sales').textContent  = 'Rp ' + formatRp(data.totalSales);
    document.getElementById('stat-orders').textContent = data.totalOrders;
    document.getElementById('stat-avg').textContent    = 'Rp ' + formatRp(data.avgOrder);
    const el = document.getElementById('stat-active-orders');
    if (data.activeOrders && Object.keys(data.activeOrders).length > 0) {
        el.innerHTML = Object.entries(data.activeOrders).map(([s, c]) =>
            `<div class="text-center"><p class="text-xl font-bold text-gray-900">${c}</p><p class="text-xs text-gray-400 capitalize">${s}</p></div>`
        ).join('');
    } else {
        el.innerHTML = '<p class="text-2xl font-bold text-green-500">0</p>';
    }
}

function updateStatusChart(trendData, period) {
    const titles = {
        today: 'Pesanan Hari Ini',
        week:  'Pesanan Minggu Ini',
        month: 'Pesanan Bulan Ini',
        year:  'Pesanan Tahun Ini',
        all:   'Pesanan Total',
    };
    if (period !== 'custom') {
        document.getElementById('chart-title').textContent = titles[period] ?? 'Pesanan ';
    }
    statusChart.data.labels          = formatLabels(trendData);
    statusChart.data.datasets[0].data = (trendData || []).map(d => d.completed ?? 0);
    statusChart.data.datasets[1].data = (trendData || []).map(d => d.cancelled ?? 0);
    statusChart.update('active');
}

function updatePaymentChart(breakdown) {
    if (!breakdown || !paymentChart) return;
    const labels = breakdown.map(p => PAYMENT_LABELS[p.payment_method] ?? p.payment_method);
    const colors = breakdown.map(p => PAYMENT_COLORS[p.payment_method] ?? '#94a3b8');
    paymentChart.data.labels                       = labels;
    paymentChart.data.datasets[0].data             = breakdown.map(p => Number(p.total));
    paymentChart.data.datasets[0].backgroundColor  = colors;
    paymentChart.update('active');
    renderPaymentLegend(breakdown, labels, colors);
}

function updateRecentOrders(orders) {
    const el = document.getElementById('recent-orders-body');
    if (!orders || orders.length === 0) {
        el.innerHTML = '<tr><td colspan="4" class="py-6 text-center text-gray-400">Belum ada pesanan</td></tr>';
        return;
    }
    el.innerHTML = orders.map(o => `
        <tr class="border-b border-gray-50 hover:bg-gray-50">
            <td class="py-2 w-48"><a href="/cashier/orders/${o.id}" class="font-medium text-indigo-600 hover:underline">${o.order_number}</a></td>
            <td class="py-2 text-gray-600 capitalize w-28">${o.order_type.replace('_','-')}</td>
            <td class="py-2 w-28"><span class="badge badge-${o.status}">${capitalize(o.status)}</span></td>
            <td class="py-2 text-left font-semibold w-28">Rp ${formatRp(o.total_amount)}</td>
        </tr>
    `).join('');
}

function formatLabels(trendData) {
    if (!trendData || trendData.length === 0) return [];
    return trendData.map(item => {
        if (!item.date) return '';
        const d = new Date(item.date);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit' });
    });
}

function setLoading(state) {
    document.getElementById('chart-loading').classList.toggle('hidden', !state);
    document.querySelectorAll('.stat-value').forEach(el => { el.style.opacity = state ? '0.4' : '1'; });
}
function formatRp(n) { return Number(n).toLocaleString('id-ID'); }
function capitalize(str) { return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''; }
</script>

@endsection