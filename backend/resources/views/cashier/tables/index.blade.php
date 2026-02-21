@extends('layouts.app')
@section('title', 'Status Meja')
@section('page-title', 'Status Meja')

@section('content')
<div class="space-y-5">

    {{-- Legend --}}
    <div class="flex flex-wrap gap-3 items-center">
        <span class="flex items-center gap-1.5 text-sm text-gray-600">
            <span class="w-3 h-3 rounded-full bg-green-400"></span> Tersedia
        </span>
        <span class="flex items-center gap-1.5 text-sm text-gray-600">
            <span class="w-3 h-3 rounded-full bg-red-400"></span> Terisi
        </span>
        <span class="flex items-center gap-1.5 text-sm text-gray-600">
            <span class="w-3 h-3 rounded-full bg-yellow-400"></span> Reservasi
        </span>
        <span class="flex items-center gap-1.5 text-sm text-gray-600">
            <span class="w-3 h-3 rounded-full bg-gray-300"></span> Ditutup
        </span>
        <div class="ml-auto">
            <a href="{{ route('cashier.reservations.create') }}" class="btn-secondary text-sm">+ Reservasi</a>
        </div>
    </div>

    {{-- Meja per Seksi --}}
    @php $sections = $tables->groupBy('section'); @endphp
    @forelse($sections as $section => $sectionTables)
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">
                {{ $section ?: 'Area Utama' }}
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                @foreach($sectionTables as $table)
                    @php
                        $activeOrder = $table->activeOrder;
                        $reservation = $table->activeReservation;
                        $status = $table->status;
                        $colorMap = [
                            'available' => 'border-green-300 bg-green-50 hover:bg-green-100',
                            'occupied'  => 'border-red-300 bg-red-50',
                            'reserved'  => 'border-yellow-300 bg-yellow-50',
                            'closed'    => 'border-gray-200 bg-gray-50 opacity-60',
                        ];
                        $dotMap = [
                            'available' => 'bg-green-400',
                            'occupied'  => 'bg-red-400',
                            'reserved'  => 'bg-yellow-400',
                            'closed'    => 'bg-gray-300',
                        ];
                    @endphp
                    <div class="border-2 rounded-xl p-3 transition-all {{ $colorMap[$status] ?? 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <p class="font-bold text-gray-900 text-sm">Meja {{ $table->number }}</p>
                                <p class="text-xs text-gray-500">{{ $table->capacity }} kursi</p>
                            </div>
                            <span class="w-2.5 h-2.5 rounded-full mt-0.5 {{ $dotMap[$status] ?? 'bg-gray-300' }}"></span>
                        </div>

                        @if($activeOrder)
                            <div class="mt-2 pt-2 border-t border-current border-opacity-20">
                                <p class="text-xs font-medium text-red-700 truncate">{{ $activeOrder->order_number }}</p>
                                <p class="text-xs text-red-500">Rp {{ number_format($activeOrder->total_amount,0,',','.') }}</p>
                                <a href="{{ route('cashier.orders.show', $activeOrder) }}"
                                   class="mt-1.5 block text-center text-xs bg-white border border-red-200 text-red-700 rounded-lg py-1 hover:bg-red-50 transition">
                                    Lihat Pesanan
                                </a>
                            </div>
                        @elseif($reservation)
                            <div class="mt-2 pt-2 border-t border-current border-opacity-20">
                                <p class="text-xs font-medium text-yellow-700 truncate">{{ $reservation->customer_name }}</p>
                                <p class="text-xs text-yellow-600">{{ \Carbon\Carbon::parse($reservation->reserved_at)->format('H:i') }}</p>
                                <form method="POST" action="{{ route('cashier.reservations.cancel', $reservation) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="mt-1.5 w-full text-xs bg-white border border-yellow-200 text-yellow-700 rounded-lg py-1 hover:bg-yellow-50 transition">
                                        Batalkan
                                    </button>
                                </form>
                            </div>
                        @elseif($status === 'available')
                            <a href="{{ route('cashier.orders.create') }}?table={{ $table->id }}"
                               class="mt-2 block text-center text-xs bg-white border border-green-200 text-green-700 rounded-lg py-1 hover:bg-green-50 transition">
                                Buat Pesanan
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card text-center py-10 text-gray-400">
            <p class="text-4xl mb-2">🪑</p>
            <p>Belum ada meja terdaftar</p>
        </div>
    @endforelse

</div>
@endsection