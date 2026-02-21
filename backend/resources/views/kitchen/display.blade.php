@extends('layouts.app')
@section('title', 'Tampilan Dapur')

@section('content')
<div class="space-y-4" x-data="kitchenDisplay()" x-init="startPolling()">

    <div class="flex items-center justify-between">
        <h1 class="page-title">🍳 Tampilan Dapur</h1>
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
            <span class="text-xs text-gray-500">Live</span>
        </div>
    </div>

    {{-- Status tabs --}}
    <div class="flex gap-2">
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
            Antri: {{ $queued->count() }}
        </span>
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
            Dimasak: {{ $cooking->count() }}
        </span>
    </div>

    {{-- Antri --}}
    @if($queued->count() > 0)
    <div>
        <h2 class="text-sm font-semibold text-yellow-700 uppercase tracking-wide mb-3">⏳ Menunggu Dimasak</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($queued as $ko)
                <div class="card border-l-4 border-yellow-400 kitchen-yellow" id="ko-{{ $ko->id }}">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="font-bold text-gray-900">#{{ $ko->order->order_number }}</p>
                            <p class="text-xs text-gray-500 capitalize">{{ str_replace('_',' ',$ko->order->order_type) }}
                                {{ $ko->order->table ? '· Meja '.$ko->order->table->number : '' }}</p>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                            {{ $ko->waitingColor() === 'red' ? 'bg-red-100 text-red-700' :
                               ($ko->waitingColor() === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                            {{ $ko->waitingMinutes() }} mnt
                        </span>
                    </div>
                    <ul class="space-y-1 text-sm mb-4">
                        @foreach($ko->order->items as $item)
                            <li class="flex justify-between">
                                <span>{{ $item->product_name }}</span>
                                <span class="font-semibold">x{{ $item->quantity }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <form method="POST" action="{{ route('kitchen.orders.start', $ko) }}">
                        @csrf
                        <button type="submit" class="btn-primary w-full justify-center">▶ Mulai Masak</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Sedang Dimasak --}}
    @if($cooking->count() > 0)
    <div>
        <h2 class="text-sm font-semibold text-orange-700 uppercase tracking-wide mb-3">🔥 Sedang Dimasak</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($cooking as $ko)
                <div class="card border-l-4 border-orange-400 kitchen-yellow">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="font-bold text-gray-900">#{{ $ko->order->order_number }}</p>
                            <p class="text-xs text-gray-500 capitalize">{{ str_replace('_',' ',$ko->order->order_type) }}
                                {{ $ko->order->table ? '· Meja '.$ko->order->table->number : '' }}</p>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                            {{ $ko->waitingColor() === 'red' ? 'bg-red-100 text-red-700' :
                               ($ko->waitingColor() === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                            {{ $ko->waitingMinutes() }} mnt
                        </span>
                    </div>
                    <ul class="space-y-1 text-sm mb-4">
                        @foreach($ko->order->items as $item)
                            <li class="flex justify-between">
                                <span>{{ $item->product_name }}</span>
                                <span class="font-semibold">x{{ $item->quantity }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <form method="POST" action="{{ route('kitchen.orders.ready', $ko) }}">
                        @csrf
                        <button type="submit" class="btn-success w-full justify-center">✓ Selesai / Siap Saji</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($queued->count() === 0 && $cooking->count() === 0)
        <div class="text-center py-20 text-gray-400">
            <p class="text-5xl mb-4">🎉</p>
            <p class="text-lg font-medium">Semua pesanan sudah selesai!</p>
            <p class="text-sm mt-1">Tidak ada pesanan yang perlu diproses</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
function kitchenDisplay() {
    return {
        startPolling() {
            // Auto refresh setiap 10 detik
            setInterval(() => { window.location.reload(); }, 10000);
        }
    }
}
</script>
@endpush
@endsection