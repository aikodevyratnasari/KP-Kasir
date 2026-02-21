@extends('layouts.app')
@section('title', 'Detail Pesanan')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('cashier.orders.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
            <h1 class="page-title">Pesanan #{{ $order->order_number }}</h1>
        </div>
        <span class="badge badge-{{ $order->status }} text-sm px-3 py-1">{{ ucfirst($order->status) }}</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Info Pesanan --}}
        <div class="card">
            <h2 class="font-semibold text-gray-800 mb-4">Informasi Pesanan</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">No. Pesanan</dt><dd class="font-medium">{{ $order->order_number }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Tipe</dt><dd class="capitalize">{{ str_replace('_',' ',$order->order_type) }}</dd></div>
                @if($order->table)
                    <div class="flex justify-between"><dt class="text-gray-500">Meja</dt><dd>{{ $order->table->number }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-gray-500">Kasir</dt><dd>{{ $order->cashier?->name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Waktu</dt><dd>{{ $order->created_at->format('d M Y, H:i') }}</dd></div>
                @if($order->notes)
                    <div class="flex justify-between"><dt class="text-gray-500">Catatan</dt><dd>{{ $order->notes }}</dd></div>
                @endif
            </dl>
        </div>

        {{-- Pembayaran --}}
        <div class="card">
            <h2 class="font-semibold text-gray-800 mb-4">Pembayaran</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Total Tagihan</dt><dd class="font-semibold">Rp {{ number_format($order->total_amount,0,',','.') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Sudah Dibayar</dt><dd class="text-green-600 font-semibold">Rp {{ number_format($order->totalPaid(),0,',','.') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Sisa</dt><dd class="{{ $order->remainingBalance() > 0 ? 'text-red-600' : 'text-green-600' }} font-semibold">Rp {{ number_format($order->remainingBalance(),0,',','.') }}</dd></div>
            </dl>

            @if($order->payments->count() > 0)
                <div class="mt-3 pt-3 border-t border-gray-100 space-y-1">
                    @foreach($order->payments as $p)
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>{{ ucfirst($p->payment_method) }} — {{ $p->created_at->format('H:i') }}</span>
                            <span>Rp {{ number_format($p->amount,0,',','.') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($order->remainingBalance() > 0 && !$order->isCancelled())
                <a href="{{ route('cashier.payments.create', $order) }}" class="btn-success w-full justify-center mt-4">
                    💳 Proses Pembayaran
                </a>
            @elseif($order->isFullyPaid())
                <a href="{{ route('cashier.receipts.show', $order->payments->last()) }}" class="btn-secondary w-full justify-center mt-4">
                    🧾 Cetak Struk
                </a>
            @endif
        </div>
    </div>

    {{-- Items --}}
    <div class="card">
        <h2 class="font-semibold text-gray-800 mb-4">Item Pesanan</h2>
        <table class="w-full text-sm">
            <thead><tr class="border-b border-gray-100">
                <th class="py-2 text-left text-gray-500">Produk</th>
                <th class="py-2 text-center text-gray-500">Qty</th>
                <th class="py-2 text-right text-gray-500">Harga</th>
                <th class="py-2 text-right text-gray-500">Subtotal</th>
            </tr></thead>
            <tbody>
            @foreach($order->items as $item)
                <tr class="border-b border-gray-50">
                    <td class="py-2">
                        <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                        @if($item->special_notes)<p class="text-xs text-gray-400">{{ $item->special_notes }}</p>@endif
                    </td>
                    <td class="py-2 text-center">{{ $item->quantity }}</td>
                    <td class="py-2 text-right">Rp {{ number_format($item->unit_price,0,',','.') }}</td>
                    <td class="py-2 text-right font-semibold">Rp {{ number_format($item->subtotal,0,',','.') }}</td>
                </tr>
            @endforeach
            <tr class="border-t-2 border-gray-200">
                <td colspan="3" class="py-2 text-right font-semibold text-gray-700">Total</td>
                <td class="py-2 text-right font-bold text-lg">Rp {{ number_format($order->total_amount,0,',','.') }}</td>
            </tr>
            </tbody>
        </table>
    </div>

    {{-- Actions --}}
    @if($order->isPending())
        <div class="flex gap-3">
            <a href="{{ route('cashier.orders.edit', $order) }}" class="btn-secondary">Edit Pesanan</a>
            <form method="POST" action="{{ route('cashier.orders.cancel', $order) }}" x-data
                  @submit.prevent="if(confirm('Batalkan pesanan ini?')) $el.submit()">
                @csrf
                <input type="hidden" name="cancel_reason" value="Dibatalkan oleh kasir">
                <button type="submit" class="btn-danger">Batalkan</button>
            </form>
        </div>
    @endif
</div>
@endsection