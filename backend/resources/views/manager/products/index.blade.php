@extends('layouts.app')
@section('title', 'Manajemen Menu')
@section('page-title', 'Menu & Produk')

@section('content')
<div class="space-y-5">

{{-- Filter --}}
<div class="card overflow-x-auto">
    <form method="GET" class="flex gap-3 items-end min-w-max">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama produk..." class="form-input w-44">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
            <select name="category" class="form-input w-44">
                <option value="">Semua</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Urutkan</label>
            <select name="sort" class="form-input w-44">
                <option value="">Terbaru</option>
                <option value="name" {{ request('sort')==='name'?'selected':'' }}>Nama A-Z</option>
                <option value="price" {{ request('sort')==='price'?'selected':'' }}>Harga</option>
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
        <a href="{{ route('manager.products.index') }}" class="btn-secondary text-sm inline-flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74"/>
                <path d="M3 3v6h6"/>
            </svg>
            Reset
        </a>

        <div class="ml-auto flex gap-2 items-center">

            {{-- Tombol Tambah Paket Bundling --}}
            <a href="{{ route('manager.bundles.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap flex-shrink-0"
               style="background-color: #16a34a; border: 1px solid #16a34a; color: white;"
               onmouseover="this.style.backgroundColor='#15803d'; this.style.borderColor='#15803d';"
               onmouseout="this.style.backgroundColor='#16a34a'; this.style.borderColor='#16a34a';">
                {{-- Icon: gift/box package --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 12v10H4V12"/>
                    <path d="M22 7H2v5h20V7z"/>
                    <path d="M12 22V7"/>
                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                </svg>
                Tambah Bundling
            </a>

            {{-- Tambah Produk --}}
            <a href="{{ route('manager.products.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0"
               style="background-color: #2D54BF; border: 1px solid #2D54BF;"
               onmouseover="this.style.backgroundColor='#1e3d8f'"
               onmouseout="this.style.backgroundColor='#2D54BF'">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                Tambah Produk
            </a>

            {{-- Sampah --}}
            <a href="{{ route('manager.products.trashed') }}"
               class="inline-flex items-center p-2 text-gray-500 hover:text-red-600 transition-colors"
               title="Produk Terhapus">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6"/>
                    <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
                </svg>
            </a>
        </div>
    </form>
</div>

{{-- ── CARD PAKET BUNDLING (hanya tampil jika filter "Semua" / tidak ada filter kategori & search) ── --}}
@php
    $showBundles = isset($bundles) && $bundles->count() > 0
        && !request('category')
        && !request('search')
        && !request('sort');
@endphp

@if($showBundles)
<div class="card p-0 overflow-hidden">
    {{-- Header card bundle --}}
    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-gray-700 uppercase tracking-wide">Paket Bundling</span>
        </div>
        <div class="flex-1 h-px bg-gray-200"></div>
        <span class="text-xs text-gray-400">{{ $bundles->count() }} paket</span>
    </div>

    {{-- Tabel bundle dengan header kolom sejajar produk --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[700px]">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Produk</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Kategori</th>
                    <th class="py-3 px-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wide">Harga</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide">Tersedia</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @foreach($bundles as $bundle)
            @php
                $isActive    = $bundle->isCurrentlyActive();
                $normalPrice = $bundle->normalPrice();
                $savings     = $bundle->savings();
            @endphp
            <tr class="transition-colors"
                onmouseover="this.style.backgroundColor='#f5f3ff'"
                onmouseout="this.style.backgroundColor=''">

                {{-- Kolom Produk --}}
                <td class="py-3 px-4">
                    <div class="flex items-center gap-3">
                        @if($bundle->image)
                            <img src="{{ Storage::url($bundle->image) }}" alt="{{ $bundle->name }}"
                                 class="w-10 h-10 rounded-lg object-cover flex-shrink-0"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                                 style="display:none; background-color:#ede9fe;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" style="color:#7c3aed;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 12v10H4V12"/>
                                    <path d="M22 7H2v5h20V7z"/>
                                    <path d="M12 22V7"/>
                                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
                                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                                </svg>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                                 style="background-color:#ede9fe;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" style="color:#7c3aed;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 12v10H4V12"/>
                                    <path d="M22 7H2v5h20V7z"/>
                                    <path d="M12 22V7"/>
                                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
                                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                                </svg>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 truncate">{{ $bundle->name }}</p>
                            <p class="text-xs text-gray-500 truncate max-w-xs">
                                {{ $bundle->items->map(fn($i) => $i->product->name . ' ×' . $i->quantity)->join(' + ') }}
                            </p>
                        </div>
                    </div>
                </td>

                {{-- Kolom Kategori — badge PAKET sejajar dengan badge kategori produk --}}
                <td class="py-3 px-4">
                    <span style="background:#ede9fe; color:#6d28d9; border:1px solid #c4b5fd; font-size:11px; font-weight:700; padding:2px 10px; border-radius:6px; white-space:nowrap; display:inline-block;">
                        Bundle
                    </span>
                </td>

                {{-- Kolom Harga — sejajar dengan harga produk (text-right) --}}
                <td class="py-3 px-4 text-right whitespace-nowrap">
                    <p class="font-semibold text-gray-900">Rp {{ number_format($bundle->bundle_price, 0, ',', '.') }}</p>
                    @if($savings > 0)
                        <p class="text-xs text-gray-400 line-through">Rp {{ number_format($normalPrice, 0, ',', '.') }}</p>
                    @endif
                </td>

                {{-- Kolom Tersedia — status aktif/nonaktif/kadaluarsa --}}
                <td class="py-3 px-4 text-center">
                    @if($isActive)
                        <span class="text-xs font-semibold text-green-600">Aktif</span>
                    @elseif(!$bundle->is_active)
                        <span class="text-xs font-semibold text-gray-400">Nonaktif</span>
                    @else
                        <span class="text-xs font-semibold text-amber-500">Kadaluarsa</span>
                    @endif
                </td>

                {{-- Kolom Aksi --}}
                <td class="py-3 px-4 text-center">
                    <div class="flex justify-center items-center gap-2">
                        <a href="{{ route('manager.bundles.edit', $bundle) }}"
                           class="inline-flex items-center justify-center transition-all p-1"
                           style="color:#EF8F00;"
                           onmouseover="this.style.color='#cc7a00';"
                           onmouseout="this.style.color='#EF8F00';"
                           title="Edit Paket">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </a>
                        <form method="POST" action="{{ route('manager.bundles.destroy', $bundle) }}"
                              onsubmit="return confirm('Hapus paket {{ addslashes($bundle->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center justify-center transition-all p-1"
                                    style="color:#dc2626; background:none; border:none;"
                                    onmouseover="this.style.color='#b91c1c';"
                                    onmouseout="this.style.color='#dc2626';"
                                    title="Hapus Paket">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                    <path d="M10 11v6M14 11v6"/>
                                    <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Tabel Produk --}}
<div class="card p-0 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[700px]">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Produk</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Kategori</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide">Harga</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide">Stok</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide">Tersedia</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($products as $product)
                <tr class="transition-colors cursor-pointer"
                    onmouseover="this.style.backgroundColor='#f0f4ff'"
                    onmouseout="this.style.backgroundColor=''">
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                                     class="w-10 h-10 rounded-lg object-cover flex-shrink-0"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-10 h-10 rounded-lg bg-gray-100 items-center justify-center flex-shrink-0" style="display:none;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 truncate">{{ $product->name }}</p>
                                @if($product->description)
                                    <p class="text-xs text-gray-600 truncate max-w-xs">{{ Str::limit($product->description, 50) }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        @php
                            $categoryColors = [
                                'Dessert'           => ['bg' => '#fff0f6', 'text' => '#c2185b', 'border' => '#f48fb1'],
                                'Makanan Ringan'    => ['bg' => '#fff8ec', 'text' => '#EF8F00', 'border' => '#ffcc80'],
                                'Makanan Utama'     => ['bg' => '#f0fdf4', 'text' => '#2e7d32', 'border' => '#a5d6a7'],
                                'Minuman'           => ['bg' => '#f3e5f5', 'text' => '#7b1fa2', 'border' => '#ce93d8'],
                                'Minuman Panas'     => ['bg' => '#fce4ec', 'text' => '#c62828', 'border' => '#ef9a9a'],
                                'Paket Hemat'       => ['bg' => '#e8f5e9', 'text' => '#1b5e20', 'border' => '#80cbc4'],
                                'Makanan Penutup'   => ['bg' => '#fff3e0', 'text' => '#ef6c00', 'border' => '#ffb74d'],
                            ];
                            $catName = $product->category?->name ?? '—';
                            $color   = $categoryColors[$catName] ?? ['bg' => '#f5f5f5', 'text' => '#616161', 'border' => '#e0e0e0'];
                        @endphp
                        <span class="px-2 py-0.5 rounded-md text-xs font-medium whitespace-nowrap"
                              style="background-color:{{ $color['bg'] }}; color:{{ $color['text'] }}; border:1px solid {{ $color['border'] }};">
                            {{ $catName }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right font-semibold text-gray-900 whitespace-nowrap">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </td>
                    <td class="py-3 px-4 text-center">
                        @if($product->track_stock)
                            <span class="font-semibold {{ $product->stock <= ($product->low_stock_alert ?? 5) ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $product->stock }}
                            </span>
                            @if($product->stock <= ($product->low_stock_alert ?? 5))
                                <span class="flex items-center justify-center gap-0.5 text-xs text-red-500 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                                    </svg>
                                    Menipis
                                </span>
                            @endif
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center">
                        @if($product->is_available)
                            <span class="text-xs font-semibold text-green-600">Ya</span>
                        @else
                            <span class="text-xs font-semibold text-gray-400">Tidak</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center">
                        <div class="flex justify-center items-center gap-2">
                            <a href="{{ route('manager.products.edit', $product) }}"
                               class="inline-flex items-center justify-center transition-all p-1"
                               style="color:#EF8F00;"
                               onmouseover="this.style.color='#cc7a00';"
                               onmouseout="this.style.color='#EF8F00';"
                               title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('manager.products.destroy', $product) }}"
                                  onsubmit="return confirm('Hapus produk {{ addslashes($product->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center justify-center transition-all p-1"
                                        style="color:#dc2626; background:none; border:none;"
                                        onmouseover="this.style.color='#b91c1c';"
                                        onmouseout="this.style.color='#dc2626';"
                                        title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                        <path d="M10 11v6M14 11v6"/>
                                        <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-12 text-center">
                        <div class="flex justify-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
                            </svg>
                        </div>
                        <p class="text-gray-500 font-medium">Belum ada produk</p>
                        <p class="text-gray-400 text-xs mt-1">Mulai dengan menambahkan produk pertama</p>
                        <a href="{{ route('manager.products.create') }}" class="btn-primary mt-3 inline-flex">+ Tambah Produk</a>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
        <div class="pagination-custom">
            {{ $products->links() }}
        </div>
    </div>
</div>

</div>
@endsection