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
                {{-- hourglass --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 22h14"/><path d="M5 2h14"/><path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"/><path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"/>
                </svg>
                Antri: {{ $queued->count() }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                {{-- flame --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                </svg>
                Dimasak: {{ $cooking->count() }}
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
            <h2 class="text-sm font-bold text-yellow-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 22h14"/><path d="M5 2h14"/><path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"/><path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"/>
                </svg>
                Antri — Belum Dimasak
            </h2>
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
                                    <p class="text-xs text-orange-600 mt-0.5 italic flex items-center gap-1">
                                        {{-- note/pencil --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                        {{ $order->notes }}
                                    </p>
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
                                    <li class="text-xs text-orange-600 pl-2 italic flex items-center gap-1">
                                        {{-- arrow right --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                                        </svg>
                                        {{ $item->special_notes }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>

                        <form method="POST" action="{{ route('kitchen.orders.start', $ko) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full py-2.5 text-sm font-bold rounded-xl bg-yellow-400 hover:bg-yellow-500 text-yellow-900 transition flex items-center justify-center gap-1.5">
                                {{-- play --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                </svg>
                                Mulai Masak
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
            <h2 class="text-sm font-bold text-orange-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                </svg>
                Sedang Dimasak
            </h2>
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
                                    <p class="text-xs text-orange-600 mt-0.5 italic flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                        {{ $order->notes }}
                                    </p>
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
                                    <li class="text-xs text-orange-600 pl-2 italic flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                                        </svg>
                                        {{ $item->special_notes }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>

                        <form method="POST" action="{{ route('kitchen.orders.ready', $ko) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full py-2.5 text-sm font-bold rounded-xl bg-green-500 hover:bg-green-600 text-white transition flex items-center justify-center gap-1.5">
                                {{-- check --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Selesai Masak — Siap Saji
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
            {{-- party popper / celebration --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mb-4 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m5.8 11.3-1.9 7.4a.5.5 0 0 0 .6.6l7.4-1.9"/><path d="M11 13 9 2l4 4 4-4-1.3 5.7"/><path d="m13 13 7 2-4 4 4 4-7.4-1.9"/><path d="m8 8-3-3"/>
            </svg>
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