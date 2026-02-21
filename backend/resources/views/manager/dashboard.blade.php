@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">{{ now()->format('l, d F Y') }} &bull; {{ auth()->user()->store->name ?? '' }}</p>
        </div>
        <a href="{{ route('cashier.orders.create') }}" class="btn-primary">+ Pesanan Baru</a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Penjualan Hari Ini</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalSalesToday ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="card">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Pesanan</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $totalOrdersToday ?? 0 }}</p>
        </div>
        <div class="card">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Rata-rata Transaksi</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($avgOrderValue ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="card">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pesanan Aktif</p>
            <div class="flex gap-3 mt-1 flex-wrap">
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
            <h2 class="font-semibold text-gray-800 mb-4">🏆 Top Produk Hari Ini</h2>
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

        {{-- 7-day Trend --}}
        <div class="card lg:col-span-2">
            <h2 class="font-semibold text-gray-800 mb-4">📈 Tren 7 Hari Terakhir</h2>
            @if(!empty($weeklyTrend) && count($weeklyTrend) > 0)
                @php $maxVal = collect($weeklyTrend)->max('total') ?: 1; @endphp
                <div class="flex items-end justify-between gap-1 h-28">
                    @foreach($weeklyTrend as $day)
                        @php $pct = max(4, ($day->total / $maxVal) * 100); $isToday = \Carbon\Carbon::parse($day->date)->isToday(); @endphp
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <span class="text-xs text-gray-400">{{ number_format($day->total/1000,0) }}k</span>
                            <div class="w-full rounded-t {{ $isToday ? 'bg-indigo-500' : 'bg-indigo-200' }}" style="height:{{ $pct }}%"></div>
                            <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($day->date)->format('d/m') }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400 text-center py-10">Belum ada data penjualan</p>
            @endif
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
                <thead><tr class="border-b border-gray-100 text-left">
                    <th class="py-2 text-gray-500 font-medium">No. Pesanan</th>
                    <th class="py-2 text-gray-500 font-medium">Tipe</th>
                    <th class="py-2 text-gray-500 font-medium">Status</th>
                    <th class="py-2 text-right text-gray-500 font-medium">Total</th>
                </tr></thead>
                <tbody>
                @forelse($recentOrders ?? [] as $order)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2"><a href="{{ route('cashier.orders.show', $order) }}" class="font-medium text-indigo-600 hover:underline">{{ $order->order_number }}</a></td>
                        <td class="py-2 text-gray-600 capitalize">{{ str_replace('_','-',$order->order_type) }}</td>
                        <td class="py-2"><span class="badge badge-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                        <td class="py-2 text-right font-semibold">Rp {{ number_format($order->total_amount,0,',','.') }}</td>
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
@endsection