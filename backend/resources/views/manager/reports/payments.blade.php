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
                <select name="method" class="form-input w-40">
                    <option value="">Semua</option>
                    @foreach(\App\Models\Payment::methodOptions() as $val => $label)
                    <option value="{{ $val }}" {{ request('method') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
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
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                style="background-color: #2D54BF; border: 1px solid #2D54BF;"
                onmouseover="this.style.backgroundColor='#1e3d8f'"
                onmouseout="this.style.backgroundColor='#2D54BF'">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="6" x2="20" y2="6"/>
                    <circle cx="16" cy="6" r="2"/>
                    <line x1="4" y1="12" x2="20" y2="12"/>
                    <circle cx="8" cy="12" r="2"/>
                    <line x1="4" y1="18" x2="20" y2="18"/>
                    <circle cx="14" cy="18" r="2"/>
                </svg>
                Filter
            </button>
            <a href="{{ route('manager.reports.payments') }}" class="btn-secondary text-sm inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74"/>
                    <path d="M3 3v6h6"/>
                </svg>
                Reset
            </a>
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
                <div style="width:36px;height:36px;border-radius:50%;background:#f0fdf4;display:flex;align-items:center;justify-content:center;">
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
                <div style="width:36px;height:36px;border-radius:50%;background:#fff7ed;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Refund</p>
            </div>
            <p class="text-2xl font-bold text-orange-600">Rp {{ number_format($refundedStat->total ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $refundedStat->count ?? 0 }} transaksi</p>
        </div>

        {{-- Belum Bayar --}}
        <div class="card" style="{{ $unpaidOrders->count() > 0 ? 'border-left:3px solid #f97316;' : '' }}">
            <div class="flex items-center gap-2 mb-2">
                <div style="width:36px;height:36px;border-radius:50%;background:#fff7ed;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Belum Bayar</p>
            </div>
            <p class="text-2xl font-bold {{ $unpaidOrders->count() > 0 ? 'text-orange-500' : 'text-gray-300' }}">
                {{ $unpaidOrders->count() }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Rp {{ number_format($unpaidOrders->sum('total_amount'), 0, ',', '.') }}</p>
        </div>

        {{-- Pending --}}
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

    {{-- Breakdown per metode --}}
    @if(isset($byMethodSummary) && $byMethodSummary->count() > 0)
    <div class="card">
        <div class="flex items-center gap-2 mb-4">
            <h2 class="font-semibold text-gray-800 text-sm">Ringkasan per Metode Pembayaran</h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @php
            $methodConfig = [
                'cash'          => ['label' => 'Tunai',         'bg' => '#f0fdf4', 'border' => '#86efac', 'text' => '#166534'],
                'card'          => ['label' => 'Kartu',         'bg' => '#eff6ff', 'border' => '#93c5fd', 'text' => '#1d4ed8'],
                'qris'          => ['label' => 'QRIS',          'bg' => '#eef2ff', 'border' => '#a5b4fc', 'text' => '#4338ca'],
                'ewallet'       => ['label' => 'E-Wallet',      'bg' => '#fdf4ff', 'border' => '#e879f9', 'text' => '#86198f'],
                'bank_transfer' => ['label' => 'Transfer Bank', 'bg' => '#fff7ed', 'border' => '#fdba74', 'text' => '#9a3412'],
            ];
            @endphp
            @foreach($byMethodSummary as $m)
            @php $cfg = $methodConfig[$m->payment_method] ?? ['label' => ucfirst($m->payment_method), 'bg' => '#f9fafb', 'border' => '#e5e7eb', 'text' => '#374151']; @endphp
            <div style="background:{{ $cfg['bg'] }};border:1px solid {{ $cfg['border'] }};color:{{ $cfg['text'] }};border-radius:10px;padding:14px;">
                <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px;">{{ $cfg['label'] }}</p>
                <p style="font-size:20px;font-weight:700;line-height:1;">Rp {{ number_format($m->total, 0, ',', '.') }}</p>
                <p style="font-size:11px;margin-top:4px;opacity:0.7;">{{ $m->count }} transaksi</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

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
                    Pesanan berikut masih dalam status aktif dan belum ada pembayaran yang diproses.
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
                                $stClass = match($o->status) { 'cooking' => 'bg-yellow-100 text-yellow-700', 'ready' => 'bg-blue-100 text-blue-700', default => 'bg-orange-100 text-orange-700' };
                                $stLabel = match($o->status) { 'cooking' => 'Dimasak', 'ready' => 'Siap', default => 'Pending' };
                            @endphp
                            <tr style="border-bottom:1px solid #ffedd5;">
                                <td class="py-1.5"><a href="{{ route('cashier.orders.show', $o) }}" class="font-medium text-indigo-600 hover:underline">{{ $o->order_number }}</a></td>
                                <td class="py-1.5 text-gray-700">{{ $o->customer_name ?? '—' }}</td>
                                <td class="py-1.5 text-gray-600">{{ $o->cashier?->name ?? '-' }}</td>
                                <td class="py-1.5 text-right font-semibold">Rp {{ number_format($o->total_amount, 0, ',', '.') }}</td>
                                <td class="py-1.5 text-center"><span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $stClass }}">{{ $stLabel }}</span></td>
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

    {{-- Tabel Transaksi --}}
    <div class="card p-0 overflow-hidden">
        <div class="px-4 py-3 flex items-center justify-between border-b border-gray-200">
            <div>
                <h2 class="font-semibold text-gray-800 text-sm">Daftar Transaksi Pembayaran</h2>
                <p class="text-xs text-gray-400 mt-0.5">Transaksi yang sudah diproses (Lunas / Refund / Pending Gateway)</p>
            </div>
        </div>
        <div class="overflow-x-auto border-b border-gray-200">
            <table class="w-full text-sm" style="min-width: 780px;">
                <thead class="border-gray-200">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">No. Pesanan</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Pelanggan</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Kasir</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Metode</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase whitespace-nowrap">Detail</th>
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
                        <td class="py-3 px-4 whitespace-nowrap">
                            @php
                            $methodColors = [
                                'cash'          => 'bg-green-50 text-green-700',
                                'card'          => 'bg-blue-50 text-blue-700',
                                'qris'          => 'bg-indigo-50 text-indigo-700',
                                'ewallet'       => 'bg-purple-50 text-purple-700',
                                'bank_transfer' => 'bg-orange-50 text-orange-700',
                            ];
                            $mc = $methodColors[$p->payment_method] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $mc }}">
                                {{ \App\Models\Payment::methodOptions()[$p->payment_method] ?? ucfirst($p->payment_method) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-gray-500 text-xs whitespace-nowrap">
                            @if($p->payment_method === 'card' && $p->card_last_four)
                                {{ $p->card_type }} (...{{ $p->card_last_four }})
                            @elseif($p->payment_method === 'ewallet' && $p->ewallet_type)
                                {{ $p->ewalletLabel() }}
                            @elseif($p->payment_method === 'bank_transfer')
                                @php $bankMap = ['bca'=>'BCA','bni'=>'BNI','bri'=>'BRI','mandiri'=>'Mandiri','permata'=>'Permata']; @endphp
                                {{ $bankMap[strtolower($p->bank ?? '')] ?? strtoupper($p->bank ?? '') }}
                                @if($p->va_number)
                                    · VA: {{ $p->va_number }}
                                @endif
                            @elseif($p->payment_method === 'qris' && $p->gateway_trx_id)
                                ID: {{ Str::limit($p->gateway_trx_id, 12) }}
                            @else
                                —
                            @endif
                        </td>
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
                        <td colspan="8" class="py-10 text-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" class="mx-auto mb-2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            Tidak ada data pembayaran untuk periode ini
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
            <div class="pagination-custom">
            {{ $payments->links() }}
            </div>
        </div>
    </div>
    <a href="{{ route('manager.reports.index') }}"
        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
        style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
        onmouseover="this.style.backgroundColor='#f3f4f6';"
        onmouseout="this.style.backgroundColor='white';">
        Kembali
    </a>
</div>
@endsection