@extends('layouts.app')
@section('title', 'Riwayat Pembayaran')
@section('page-title', 'Riwayat Pembayaran')

@section('content')
<div class="space-y-6">
    <!-- <h1 class="page-title">Riwayat Pembayaran</h1> -->

    <div class="card overflow-x-auto">
        <form method="GET" class="flex flex-wrap gap-3 items-end min-w-max">
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Metode</label>
                <select name="method" class="form-input w-44">
                    <option value="">Semua Metode</option>
                    <option value="cash" {{ request('method')==='cash'?'selected':'' }}>Tunai</option>
                    <option value="card" {{ request('method')==='card'?'selected':'' }}>Kartu</option>
                    <option value="ewallet" {{ request('method')==='ewallet'?'selected':'' }}>E-Wallet</option>
                </select></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input w-auto"></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input w-auto"></div>
           <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                style="background-color: #2D54BF; border: 1px solid #2D54BF;"
                onmouseover="this.style.backgroundColor='#1e3d8f'"
                onmouseout="this.style.backgroundColor='#2D54BF'">
                Filter
            </button>
        </form>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap pr-8">No. Pesanan</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Kasir</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Metode</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 uppercase">Waktu</th>
                    <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($payments ?? [] as $p)
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-4 font-medium text-indigo-600 whitespace-nowrap pr-1">
                        <a href="{{ route('cashier.orders.show', $p->order) }}" class="hover:underline">{{ $p->order?->order_number ?? '-' }}</a>
                    </td>
                    <td class="py-3 px-4 text-gray-600 whitespace-nowrap">{{ $p->cashier?->name ?? '-' }}</td>
                    <td class="py-3 px-4 capitalize text-gray-700">{{ $p->payment_method }}</td>
                    <td class="py-3 px-4 text-left font-semibold whitespace-nowrap pr-1">Rp {{ number_format($p->amount,0,',','.') }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $p->status==='paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ ucfirst($p->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-gray-500 text-xs whitespace-nowrap text-center">{{ $p->created_at->format('d M Y H:i') }}</td>
                    <td class="py-3 px-4 text-center">
                        <a href="{{ route('cashier.receipts.show', $p) }}"
                        class="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-semibold rounded-full transition-all"
                        style="background-color: #b06bff; color: white; border: 1px solid #b06bff;"
                        onmouseover="this.style.backgroundColor='#8b3de3';"
                        onmouseout="this.style.backgroundColor='#b06bff';">
                            Struk
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-8 text-center text-gray-400">Tidak ada data pembayaran</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection