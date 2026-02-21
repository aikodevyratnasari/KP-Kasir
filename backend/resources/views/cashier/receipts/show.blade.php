@extends('layouts.app')
@section('title', 'Struk Pembayaran')

@section('content')
<div class="max-w-sm mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <a href="{{ route('cashier.orders.show', $payment->order) }}" class="text-gray-400 hover:text-gray-600">←</a>
        <h1 class="text-lg font-bold text-gray-900">Struk Pembayaran</h1>
        <a href="{{ route('cashier.receipts.print', $payment) }}" target="_blank" class="btn-secondary text-xs">🖨️ Cetak</a>
    </div>

    <div class="card font-mono text-sm" id="receipt">
        {{-- Header --}}
        <div class="text-center border-b border-dashed border-gray-300 pb-3 mb-3">
            <p class="text-base font-bold">{{ $payment->order->store->name ?? config('app.name') }}</p>
            <p class="text-xs text-gray-500">{{ $payment->order->store->address ?? '' }}</p>
            <p class="text-xs text-gray-500">{{ $payment->order->store->phone ?? '' }}</p>
        </div>

        {{-- Info --}}
        <div class="space-y-1 text-xs border-b border-dashed border-gray-300 pb-3 mb-3">
            <div class="flex justify-between"><span>No. Pesanan</span><span>{{ $payment->order->order_number }}</span></div>
            <div class="flex justify-between"><span>Kasir</span><span>{{ $payment->cashier?->name ?? '-' }}</span></div>
            <div class="flex justify-between"><span>Waktu</span><span>{{ $payment->created_at->format('d/m/Y H:i') }}</span></div>
            <div class="flex justify-between"><span>Tipe</span><span class="capitalize">{{ str_replace('_',' ',$payment->order->order_type) }}</span></div>
            @if($payment->order->table)
                <div class="flex justify-between"><span>Meja</span><span>{{ $payment->order->table->number }}</span></div>
            @endif
        </div>

        {{-- Items --}}
        <div class="border-b border-dashed border-gray-300 pb-3 mb-3">
            @foreach($payment->order->items as $item)
                <div class="flex justify-between">
                    <span>{{ $item->product_name }}</span>
                </div>
                <div class="flex justify-between text-gray-500 pl-2">
                    <span>{{ $item->quantity }} x Rp {{ number_format($item->unit_price,0,',','.') }}</span>
                    <span>Rp {{ number_format($item->subtotal,0,',','.') }}</span>
                </div>
            @endforeach
        </div>

        {{-- Total --}}
        <div class="space-y-1 text-xs border-b border-dashed border-gray-300 pb-3 mb-3">
            <div class="flex justify-between font-bold text-sm">
                <span>TOTAL</span>
                <span>Rp {{ number_format($payment->order->total_amount,0,',','.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="capitalize">{{ $payment->payment_method }}</span>
                <span>Rp {{ number_format($payment->amount,0,',','.') }}</span>
            </div>
            @if($payment->amount_received && $payment->payment_method === 'cash')
                <div class="flex justify-between">
                    <span>Uang Diterima</span>
                    <span>Rp {{ number_format($payment->amount_received,0,',','.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Kembalian</span>
                    <span>Rp {{ number_format($payment->change_amount ?? ($payment->amount_received - $payment->amount),0,',','.') }}</span>
                </div>
            @endif
        </div>

        <div class="text-center text-xs text-gray-500">
            <p>Terima kasih atas kunjungan Anda!</p>
            <p class="mt-1">Simpan struk ini sebagai bukti pembayaran</p>
        </div>
    </div>
</div>
@endsection