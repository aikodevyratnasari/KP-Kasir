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
                <select name="status" class="form-input w-32">
                    <option value="">Semua</option>
                    <option value="paid"     {{ request('status') === 'paid'     ? 'selected' : '' }}>Lunas</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refund</option>
                    <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('manager.reports.payments') }}" class="btn-secondary">Reset</a>

            {{-- Download & Kirim --}}
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

    {{-- Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="card">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Penerimaan</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($summary->total ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="card">
            <p class="text-xs text-gray-500 uppercase font-medium">Jumlah Transaksi Lunas</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $summary->count ?? 0 }}</p>
        </div>
    </div>

    {{-- Tabel — overflow-x-auto untuk responsif --}}
    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width: 700px;">
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
                        <td class="py-3 px-4 text-gray-700 whitespace-nowrap">
                            {{ $p->order?->customer_name ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-gray-600 whitespace-nowrap">
                            {{ $p->cashier?->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-gray-700 whitespace-nowrap">
                            {{ $p->methodLabel() }}
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
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-gray-500 text-xs whitespace-nowrap text-center">
                            {{ $p->created_at->format('d M Y, H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-400">Tidak ada data pembayaran untuk periode ini</td>
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