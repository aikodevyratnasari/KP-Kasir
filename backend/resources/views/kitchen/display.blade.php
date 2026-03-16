@extends('layouts.app')
@section('title', 'Tampilan Dapur')
@section('page-title', 'Tampilan Dapur')

@section('content')
@php
    $queued  = $orders->where('status', 'pending');
    $cooking = $orders->where('status', 'cooking');
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                ⏳ Antri: {{ $queued->count() }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                🔥 Dimasak: {{ $cooking->count() }}
            </span>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-500">
            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
            Live • Refresh otomatis tiap 10 detik
        </div>
    </div>

    {{-- ── ANTRI (pending + sudah lunas) ── --}}
    @if($queued->count() > 0)
        <div>
            <h2 class="text-sm font-bold text-yellow-700 uppercase tracking-wider mb-3">⏳ Antri — Belum Dimasak</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($queued as $order)
                    @php
                        $ko       = $order->kitchenOrder;
                        $waitMins = $ko?->queued_at ? (int) now()->diffInMinutes($ko->queued_at) : 0;
                        $urgency  = $waitMins >= 20 ? 'red' : ($waitMins >= 10 ? 'yellow' : 'green');
                    @endphp
                    <div class="bg-white border-2 border-yellow-300 rounded-xl p-4 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <p class="font-bold text-gray-900">#{{ $order->order_number }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $order->order_type === 'dine_in' ? 'Dine-In' : 'Takeaway' }}
                                    {{ $order->table ? '· Meja ' . $order->table->number : '' }}
                                </p>
                                @if($order->notes)
                                    <p class="text-xs text-orange-600 mt-0.5 italic">📝 {{ $order->notes }}</p>
                                @endif
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold flex-shrink-0
                                {{ $urgency === 'red' ? 'bg-red-100 text-red-700' : ($urgency === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                {{ $waitMins }} mnt
                            </span>
                        </div>

                        <ul class="space-y-1 mb-4 text-sm">
                            @foreach($order->items as $item)
                                <li class="flex justify-between">
                                    <span class="text-gray-700">{{ $item->product_name }}</span>
                                    <span class="font-bold ml-2">×{{ $item->quantity }}</span>
                                </li>
                                @if($item->special_notes)
                                    <li class="text-xs text-orange-600 pl-2 italic">→ {{ $item->special_notes }}</li>
                                @endif
                            @endforeach
                        </ul>

                        <form method="POST" action="{{ route('kitchen.orders.start', $ko) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full py-2.5 text-sm font-bold rounded-xl bg-yellow-400 hover:bg-yellow-500 text-yellow-900 transition">
                                ▶ Mulai Masak
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── DIMASAK ── --}}
    @if($cooking->count() > 0)
        <div>
            <h2 class="text-sm font-bold text-orange-700 uppercase tracking-wider mb-3">🔥 Sedang Dimasak</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($cooking as $order)
                    @php
                        $ko       = $order->kitchenOrder;
                        $waitMins = $ko?->cooking_started_at ? (int) now()->diffInMinutes($ko->cooking_started_at) : 0;
                        $urgency  = $waitMins >= 20 ? 'red' : ($waitMins >= 10 ? 'yellow' : 'green');
                    @endphp
                    <div class="bg-white border-2 border-orange-300 rounded-xl p-4 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <p class="font-bold text-gray-900">#{{ $order->order_number }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $order->order_type === 'dine_in' ? 'Dine-In' : 'Takeaway' }}
                                    {{ $order->table ? '· Meja ' . $order->table->number : '' }}
                                </p>
                                @if($order->notes)
                                    <p class="text-xs text-orange-600 mt-0.5 italic">📝 {{ $order->notes }}</p>
                                @endif
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold flex-shrink-0
                                {{ $urgency === 'red' ? 'bg-red-100 text-red-700' : ($urgency === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                {{ $waitMins }} mnt
                            </span>
                        </div>

                        <ul class="space-y-1 mb-4 text-sm">
                            @foreach($order->items as $item)
                                <li class="flex justify-between">
                                    <span class="text-gray-700">{{ $item->product_name }}</span>
                                    <span class="font-bold ml-2">×{{ $item->quantity }}</span>
                                </li>
                                @if($item->special_notes)
                                    <li class="text-xs text-orange-600 pl-2 italic">→ {{ $item->special_notes }}</li>
                                @endif
                            @endforeach
                        </ul>

                        <form method="POST" action="{{ route('kitchen.orders.ready', $ko) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full py-2.5 text-sm font-bold rounded-xl bg-green-500 hover:bg-green-600 text-white transition">
                                ✓ Selesai Masak — Siap Saji
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Kosong --}}
    @if($orders->isEmpty())
        <div class="flex flex-col items-center justify-center py-24 text-gray-400">
            <span class="text-6xl mb-4">🎉</span>
            <p class="text-lg font-semibold text-gray-600">Semua pesanan selesai!</p>
            <p class="text-sm mt-1">Tidak ada pesanan yang perlu diproses saat ini</p>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh setiap 10 detik
    setTimeout(() => window.location.reload(), 10000);
</script>
@endpush