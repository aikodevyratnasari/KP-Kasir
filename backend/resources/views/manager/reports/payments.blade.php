@extends('layouts.app')
@section('title', 'Laporan Pembayaran')
@section('page-title', 'Laporan Pembayaran')

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
                <label class="block text-xs font-medium text-gray-600 mb-1">Metode</label>
                <select name="method" class="form-input w-36">
                    <option value="">Semua</option>
                    <option value="cash"    {{ request('method') === 'cash'    ? 'selected' : '' }}>Tunai</option>
                    <option value="card"    {{ request('method') === 'card'    ? 'selected' : '' }}>Kartu</option>
                    <option value="ewallet" {{ request('method') === 'ewallet' ? 'selected' : '' }}>E-Wallet</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="form-input w-36">
                    <option value="">Semua</option>
                    <option value="paid"     {{ request('status') === 'paid'     ? 'selected' : '' }}>Lunas</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refund</option>
                    <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('manager.reports.payments') }}" class="btn-secondary">Reset</a>
            <div class="ml-auto">
                <x-manager.reports.actions
                    type="payments"
                    :from="$from->format('Y-m-d')"
                    :to="$to->format('Y-m-d')"
                    :extra-params="'&method='.request('method', '').'&status='.request('status', '')"
                />
            </div>
        </form>
    </div>

    {{-- Summary Cards per Status --}}
    @php
        $summaryByStatus = $summaryByStatus ?? collect();
        $paidStat        = $summaryByStatus->get('paid');
        $refundedStat    = $summaryByStatus->get('refunded');
        $pendingPayStat  = $summaryByStatus->get('pending');
        $unpaidOrders    = $unpaidOrders ?? collect();
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Lunas --}}
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Lunas</p>
            </div>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($summary->total ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $summary->count ?? 0 }} transaksi</p>
        </div>

        {{-- Refund --}}
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#fff7ed;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Refund</p>
            </div>
            <p class="text-2xl font-bold text-orange-600">Rp {{ number_format($refundedStat->total ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $refundedStat->count ?? 0 }} transaksi</p>
        </div>

        {{-- Belum Bayar (orders tanpa payment) --}}
        <div class="card" style="{{ $unpaidOrders->count() > 0 ? 'border-left:3px solid #f97316;' : '' }}">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#fff7ed;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Belum Bayar</p>
            </div>
            <p class="text-2xl font-bold {{ $unpaidOrders->count() > 0 ? 'text-orange-500' : 'text-gray-300' }}">
                {{ $unpaidOrders->count() }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Rp {{ number_format($unpaidOrders->sum('total_amount'), 0, ',', '.') }}</p>
        </div>

        {{-- Pending Payment Record --}}
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:32px;height:32px;border-radius:8px;background:#fefce8;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ca8a04" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Pending</p>
            </div>
            <p class="text-2xl font-bold text-yellow-600">{{ $pendingPayStat->count ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">Rp {{ number_format($pendingPayStat->total ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Banner pesanan belum bayar --}}
    @if($unpaidOrders->count() > 0 && (! request('status') || request('status') === 'pending'))
    <div class="card" style="border-left:3px solid #f97316;">
        <div class="flex items-start gap-3">
            <div style="color:#f97316;flex-shrink:0;margin-top:2px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-800 text-sm mb-1">
                    {{ $unpaidOrders->count() }} Pesanan Aktif Belum Selesai
                </h3>
                <p class="text-xs text-gray-500 mb-3">
                    Pesanan berikut masih dalam status aktif (Pending / Dimasak / Siap) dan belum ada pembayaran yang diproses.
                    Total nilai: <strong>Rp {{ number_format($unpaidOrders->sum('total_amount'), 0, ',', '.') }}</strong>
                </p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" style="min-width:550px;">
                        <thead>
                            <tr style="border-bottom:1px solid #fed7aa;">
                                <th class="py-1.5 text-left text-xs font-semibold text-gray-500">No. Pesanan</th>
                                <th class="py-1.5 text-left text-xs font-semibold text-gray-500">Pelanggan</th>
                                <th class="py-1.5 text-left text-xs font-semibold text-gray-500">Kasir</th>
                                <th class="py-1.5 text-right text-xs font-semibold text-gray-500">Total</th>
                                <th class="py-1.5 text-center text-xs font-semibold text-gray-500">Status</th>
                                <th class="py-1.5 text-center text-xs font-semibold text-gray-500">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($unpaidOrders as $o)
                            @php
                                $stClass = match($o->status) {
                                    'cooking' => 'bg-yellow-100 text-yellow-700',
                                    'ready'   => 'bg-blue-100 text-blue-700',
                                    default   => 'bg-orange-100 text-orange-700',
                                };
                                $stLabel = match($o->status) {
                                    'cooking' => 'Dimasak',
                                    'ready'   => 'Siap',
                                    default   => 'Pending',
                                };
                            @endphp
                            <tr style="border-bottom:1px solid #ffedd5;">
                                <td class="py-1.5">
                                    <a href="{{ route('cashier.orders.show', $o) }}" class="font-medium text-indigo-600 hover:underline">
                                        {{ $o->order_number }}
                                    </a>
                                </td>
                                <td class="py-1.5 text-gray-700">{{ $o->customer_name ?? '—' }}</td>
                                <td class="py-1.5 text-gray-600">{{ $o->cashier?->name ?? '-' }}</td>
                                <td class="py-1.5 text-right font-semibold">Rp {{ number_format($o->total_amount, 0, ',', '.') }}</td>
                                <td class="py-1.5 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $stClass }}">{{ $stLabel }}</span>
                                </td>
                                <td class="py-1.5 text-center text-xs text-gray-500">{{ $o->created_at->format('d M, H:i') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Tabel Transaksi Pembayaran --}}
    <div class="card p-0 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-gray-800 text-sm">Daftar Transaksi Pembayaran</h2>
                <p class="text-xs text-gray-400 mt-0.5">Hanya menampilkan transaksi yang sudah diproses (Lunas / Refund / Pending Payment)</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width: 720px;">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">No. Pesanan</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Pelanggan</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Kasir</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Metode</th>
                        <th class="py-3 px-4 text-right text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Jumlah</th>
                        <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Status</th>
                        <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($payments as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 font-medium text-indigo-600 whitespace-nowrap">
                            <a href="{{ route('cashier.orders.show', $p->order) }}" class="hover:underline">
                                {{ $p->order?->order_number ?? '-' }}
                            </a>
                        </td>
                        <td class="py-3 px-4 text-gray-700 whitespace-nowrap">{{ $p->order?->customer_name ?? '—' }}</td>
                        <td class="py-3 px-4 text-gray-600 whitespace-nowrap">{{ $p->cashier?->name ?? '-' }}</td>
                        <td class="py-3 px-4 text-gray-700 whitespace-nowrap">{{ $p->methodLabel() }}</td>
                        <td class="py-3 px-4 text-right font-semibold whitespace-nowrap">
                            Rp {{ number_format($p->amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @php
                                $statusClass = match($p->status) {
                                    'paid'     => 'bg-green-100 text-green-700',
                                    'refunded' => 'bg-orange-100 text-orange-700',
                                    default    => 'bg-yellow-100 text-yellow-700',
                                };
                                $statusLabel = match($p->status) {
                                    'paid'     => 'Lunas',
                                    'refunded' => 'Refund',
                                    'pending'  => 'Pending',
                                    default    => ucfirst($p->status),
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="py-3 px-4 text-gray-500 text-xs whitespace-nowrap text-center">
                            {{ $p->created_at->format('d M Y, H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" class="mx-auto mb-2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            Tidak ada data pembayaran untuk periode ini
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection