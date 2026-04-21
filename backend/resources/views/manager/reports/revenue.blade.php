@extends('layouts.app')
@section('title', 'Analitik Revenue')
@section('page-title', 'Analitik Revenue')

@section('content')
<div class="space-y-6">

    {{-- Filter --}}
    <div class="card">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-input w-auto">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-input w-auto">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Periode</label>
                <select name="period" class="form-input w-auto">
                    @foreach(['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan', 'yearly' => 'Tahunan'] as $val => $label)
                        <option value="{{ $val }}" {{ $period === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary">Tampilkan</button>
            <a href="{{ route('manager.reports.revenue') }}" class="btn-secondary">Reset</a>
            <div class="ml-auto">
                <x-manager.reports.actions type="revenue" :from="$from->format('Y-m-d')" :to="$to->format('Y-m-d')" :period="$period" />
            </div>
        </form>
    </div>

    @php
        $rows             = $data['data']               ?? collect();
        $totalRev         = $data['total']              ?? 0;
        $periodType       = $data['period_type']        ?? $period;
        $nonRevenueOrders = $data['non_revenue_orders'] ?? collect();
        $cancelledData    = $nonRevenueOrders->get('cancelled');
        $periodLabels     = ['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan', 'yearly' => 'Tahunan'];
    @endphp

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Total Revenue</p>
            </div>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalRev, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">Dari pembayaran lunas</p>
        </div>
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#eef2ff;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Jumlah Periode</p>
            </div>
            <p class="text-2xl font-bold text-indigo-600">{{ $rows->count() }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $periodLabels[$periodType] ?? $periodType }}</p>
        </div>
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Rata-rata / Periode</p>
            </div>
            <p class="text-2xl font-bold text-gray-900">
                Rp {{ $rows->count() > 0 ? number_format($totalRev / $rows->count(), 0, ',', '.') : '0' }}
            </p>
        </div>
    </div>

    {{-- Pesanan Non-Revenue --}}
    @if($nonRevenueOrders->isNotEmpty())
    <div class="card">
        <div class="flex items-center gap-2 mb-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9a3412" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <h2 class="font-semibold text-gray-800 text-sm">Pesanan Tidak Menghasilkan Revenue</h2>
        </div>
        <p class="text-xs text-gray-400 mb-4">Pesanan yang belum selesai</p>
        @php
            $nonRevConfig = [
                'cancelled' => ['label' => 'Dibatalkan', 'bg' => '#fef2f2', 'border' => '#fca5a5', 'text' => '#991b1b'],
                'pending'   => ['label' => 'Pending',    'bg' => '#fff7ed', 'border' => '#fdba74', 'text' => '#9a3412'],
                'cooking'   => ['label' => 'Dimasak',    'bg' => '#fffbeb', 'border' => '#fcd34d', 'text' => '#92400e'],
                'ready'     => ['label' => 'Siap Saji',  'bg' => '#eff6ff', 'border' => '#93c5fd', 'text' => '#1d4ed8'],
            ];
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach($nonRevConfig as $statusKey => $cfg)
                @php $nr = $nonRevenueOrders->get($statusKey); @endphp
                @if($nr)
                <div style="background:{{ $cfg['bg'] }};border:1px solid {{ $cfg['border'] }};color:{{ $cfg['text'] }};border-radius:10px;padding:12px;">
                    <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:4px;">{{ $cfg['label'] }}</p>
                    <p style="font-size:24px;font-weight:700;line-height:1;">{{ $nr->count }}</p>
                    <p style="font-size:11px;margin-top:4px;opacity:0.7;">Rp {{ number_format($nr->total_amount, 0, ',', '.') }}</p>
                </div>
                @endif
            @endforeach
        </div>
        @if($cancelledData)
        <div class="mt-3 flex items-center gap-2 px-3 py-2 rounded-lg text-xs" style="background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span><strong>{{ $cancelledData->count }} pesanan dibatalkan</strong> senilai Rp {{ number_format($cancelledData->total_amount, 0, ',', '.') }} tidak termasuk dalam total revenue.</span>
        </div>
        @endif
    </div>
    @endif

    {{-- Tabel Data Revenue --}}
    <div class="card">
        <div class="flex items-center gap-2 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <h2 class="font-semibold text-gray-800 text-sm">
                Data Revenue
                <span class="text-xs font-normal text-gray-400 ml-1">({{ $periodLabels[$periodType] ?? $periodType }})</span>
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="py-2.5 px-3 text-left text-xs font-semibold text-gray-500 uppercase">Periode</th>
                        <th class="py-2.5 px-3 text-right text-xs font-semibold text-gray-500 uppercase">Total Revenue</th>
                        <th class="py-2.5 px-3 text-right text-xs font-semibold text-gray-500 uppercase">Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2.5 px-3 text-gray-700">{{ $row->period ?? '-' }}</td>
                        <td class="py-2.5 px-3 text-right font-semibold">Rp {{ number_format($row->revenue ?? 0, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right text-gray-600">{{ $row->count ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-10 text-center text-gray-400 text-sm">Tidak ada data untuk periode ini</td></tr>
                @endforelse
                </tbody>
                @if($rows->count() > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50">
                        <td class="py-2.5 px-3 font-semibold text-gray-700 text-xs uppercase">Total</td>
                        <td class="py-2.5 px-3 text-right font-bold text-gray-900">Rp {{ number_format($totalRev, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right font-semibold text-gray-700">{{ $rows->sum('count') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection