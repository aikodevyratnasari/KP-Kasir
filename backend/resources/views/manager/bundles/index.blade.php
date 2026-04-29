@extends('layouts.app')
@section('title', 'Paket Bundling')
@section('page-title', 'Paket Bundling')

@section('content')
<div class="space-y-5">

    {{-- Header + Tombol --}}
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div>
            <p class="text-sm text-gray-500 mt-0.5">Buat paket produk dengan harga lebih hemat untuk pelanggan</p>
            @if(isset($bundleCount))
                <p class="text-xs text-gray-400 mt-0.5">{{ $bundles->total() }} paket tersimpan</p>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.products.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg transition-colors"
               style="border:1.5px solid #dcdcdc; background:white; color:#374151;"
               onmouseover="this.style.backgroundColor='#f3f4f6';"
               onmouseout="this.style.backgroundColor='white';">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Produk
            </a>
            <a href="{{ route('manager.bundles.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
               style="background-color:#2D54BF; border:1px solid #2D54BF;"
               onmouseover="this.style.backgroundColor='#1e3d8f';"
               onmouseout="this.style.backgroundColor='#2D54BF';">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Paket
            </a>
        </div>
    </div>

    @if($bundles->count() === 0)
    {{-- Empty state --}}
    <div class="card py-16 flex flex-col items-center justify-center text-center">
        <div style="width:72px; height:72px; border-radius:50%; background:#f0f9ff; border:2px solid #bae6fd; display:flex; align-items:center; justify-content:center; margin-bottom:16px;">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:32px; height:32px; color:#0ea5e9;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                <line x1="12" y1="22.08" x2="12" y2="12"/>
            </svg>
        </div>
        <h3 class="font-semibold text-gray-800 mb-1">Belum ada paket bundling</h3>
        <p class="text-sm text-gray-400 mb-4 max-w-xs">Buat paket yang menggabungkan beberapa produk dengan harga spesial untuk meningkatkan penjualan.</p>
        <a href="{{ route('manager.bundles.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg"
           style="background-color:#2D54BF;">
            + Buat Paket Pertama
        </a>
    </div>
    @else

    {{-- Grid Kartu Bundle --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($bundles as $bundle)
        @php
            $normalPrice = $bundle->normalPrice();
            $savings     = $bundle->savings();
            $savingsPct  = $normalPrice > 0 ? round($savings / $normalPrice * 100) : 0;
            $isActive    = $bundle->isCurrentlyActive();
        @endphp
        <div class="card p-0 overflow-hidden flex flex-col" style="border:1.5px solid {{ $isActive ? '#86efac' : '#e5e7eb' }};">
            {{-- Gambar / Header --}}
            <div style="height:120px; background:{{ $isActive ? '#f0fdf4' : '#f9fafb' }}; overflow:hidden; position:relative; display:flex; align-items:center; justify-content:center;">
                @if($bundle->image)
                    <img src="{{ Storage::url($bundle->image) }}" alt="{{ $bundle->name }}"
                         style="width:100%; height:100%; object-fit:cover;">
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:36px; height:36px; color:{{ $isActive ? '#86efac' : '#d1d5db' }};" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                @endif

                {{-- Badge status --}}
                <div style="position:absolute; top:8px; left:8px;">
                    @if($isActive)
                        <span style="background:#16a34a; color:white; font-size:9px; font-weight:700; padding:2px 8px; border-radius:8px;">AKTIF</span>
                    @elseif(!$bundle->is_active)
                        <span style="background:#6b7280; color:white; font-size:9px; font-weight:700; padding:2px 8px; border-radius:8px;">NONAKTIF</span>
                    @else
                        <span style="background:#f59e0b; color:white; font-size:9px; font-weight:700; padding:2px 8px; border-radius:8px;">KADALUARSA</span>
                    @endif
                </div>

                {{-- Badge hemat --}}
                @if($savingsPct > 0)
                <div style="position:absolute; top:8px; right:8px; background:#ef4444; color:white; font-size:9px; font-weight:700; padding:2px 8px; border-radius:8px;">
                    HEMAT {{ $savingsPct }}%
                </div>
                @endif
            </div>

            {{-- Konten --}}
            <div class="p-4 flex-1 flex flex-col">
                <h3 class="font-bold text-gray-900 text-sm mb-1">{{ $bundle->name }}</h3>
                @if($bundle->description)
                    <p class="text-xs text-gray-400 mb-3 line-clamp-2">{{ $bundle->description }}</p>
                @endif

                {{-- Isi paket --}}
                <div class="space-y-1 mb-3 flex-1">
                    @foreach($bundle->items as $item)
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <span style="width:5px; height:5px; border-radius:50%; background:#6366f1; flex-shrink:0;"></span>
                        <span>{{ $item->product->name }}
                            @if($item->variant)<span class="text-indigo-500">({{ $item->variant->name }})</span>@endif
                            <span class="text-gray-400">× {{ $item->quantity }}</span>
                        </span>
                    </div>
                    @endforeach
                </div>

                {{-- Harga --}}
                <div class="pt-3 border-t border-gray-100">
                    <div class="flex items-end justify-between">
                        <div>
                            <span class="text-lg font-bold text-green-600">
                                Rp {{ number_format($bundle->bundle_price, 0, ',', '.') }}
                            </span>
                            @if($savings > 0)
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-gray-400 line-through">Rp {{ number_format($normalPrice, 0, ',', '.') }}</span>
                                <span class="text-xs font-semibold text-green-600">Hemat Rp {{ number_format($savings, 0, ',', '.') }}</span>
                            </div>
                            @endif
                        </div>
                        {{-- Aksi --}}
                        <div class="flex items-center gap-2">
                            <a href="{{ route('manager.bundles.edit', $bundle) }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition-all"
                               style="background:#fef3c7; color:#d97706; border:1px solid #fde68a;"
                               onmouseover="this.style.background='#fde68a';"
                               onmouseout="this.style.background='#fef3c7';"
                               title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('manager.bundles.destroy', $bundle) }}"
                                  onsubmit="return confirm('Hapus paket {{ addslashes($bundle->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition-all"
                                    style="background:#fee2e2; color:#dc2626; border:1px solid #fca5a5;"
                                    onmouseover="this.style.background='#fca5a5';"
                                    onmouseout="this.style.background='#fee2e2';"
                                    title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Periode aktif --}}
                    @if($bundle->starts_at || $bundle->ends_at)
                    <p class="text-xs text-gray-400 mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width:11px;height:11px;display:inline;margin-right:2px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ $bundle->starts_at?->format('d M Y') ?? '—' }} s/d {{ $bundle->ends_at?->format('d M Y') ?? 'selamanya' }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($bundles->hasPages())
    <div class="px-4 py-3 bg-white rounded-xl border border-gray-100">
        <div class="pagination-custom">{{ $bundles->links() }}</div>
    </div>
    @endif

    @endif
</div>
@endsection