@extends('layouts.app')
@section('title', 'Manajemen Menu')
@section('page-title', 'Menu & Produk')

@section('content')
<div class="space-y-5">

    

{{-- Filter --}}
<div class="card">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
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
        <button type="submit" class="btn-primary text-sm">Filter</button>
        <a href="{{ route('manager.products.index') }}" class="btn-secondary text-sm">Reset</a>

        {{-- Pindah ke sini --}}
        <div class="ml-auto flex gap-2 items-center">
    <a href="{{ route('manager.products.create') }}" class="btn-primary text-sm">+ Tambah Produk</a>
    <a href="{{ route('manager.products.trashed') }}" class="inline-flex items-center p-2 text-gray-500 hover:text-red-600 transition-colors" title="Sampah">
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

    {{-- Tabel --}}
    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-indigo-600 uppercase tracking-wide">Produk</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-indigo-600 uppercase tracking-wide">Kategori</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-indigo-600 uppercase tracking-wide">Harga</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-indigo-600 uppercase tracking-wide">Stok</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-indigo-600 uppercase tracking-wide">Tersedia</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-indigo-600 uppercase tracking-wide">Aksi</th>
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
                                     class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-xl flex-shrink-0">🍽️</div>
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
        'Dessert'        => ['bg' => '#fff0f6', 'text' => '#c2185b', 'border' => '#f48fb1'],
        'Makanan Ringan' => ['bg' => '#fff8ec', 'text' => '#EF8F00', 'border' => '#ffcc80'],
        'Makanan Utama'  => ['bg' => '#f0fdf4', 'text' => '#2e7d32', 'border' => '#a5d6a7'],
        'Minuman'        => ['bg' => '#f3e5f5', 'text' => '#7b1fa2', 'border' => '#ce93d8'],
        'Minuman Panas'  => ['bg' => '#fce4ec', 'text' => '#c62828', 'border' => '#ef9a9a'],
        'Paket Hemat'    => ['bg' => '#e8f5e9', 'text' => '#1b5e20', 'border' => '#80cbc4'],
    ];
    $catName = $product->category?->name ?? '—';
    $color = $categoryColors[$catName] ?? ['bg' => '#f5f5f5', 'text' => '#616161', 'border' => '#e0e0e0'];
@endphp
<span class="px-2 py-0.5 rounded-md text-xs font-medium"
      style="background-color: {{ $color['bg'] }}; color: {{ $color['text'] }}; border: 1px solid {{ $color['border'] }};">
    {{ $catName }}
</span>
                    </td>
                    <td class="py-3 px-4 text-right font-semibold text-gray-900">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </td>
                    <td class="py-3 px-4 text-center">
                        @if($product->track_stock)
                            <span class="font-semibold {{ $product->stock <= ($product->low_stock_alert ?? 5) ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $product->stock }}
                            </span>
                            @if($product->stock <= ($product->low_stock_alert ?? 5))
                                <span class="block text-xs text-red-500">⚠ Menipis</span>
                            @endif
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $product->is_available ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $product->is_available ? '✓ Ya' : '✗ Tidak' }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-center">
    <div class="flex justify-center items-center gap-2">
        <a href="{{ route('manager.products.edit', $product) }}"
   class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
   style="color: #EF8F00; background-color: #fff8ec; border: 1px solid #EF8F00;"
   onmouseover="this.style.backgroundColor='#ffefd0'"
   onmouseout="this.style.backgroundColor='#fff8ec'"
   onmousedown="this.style.transform='scale(0.95)'"
   onmouseup="this.style.transform='scale(1)'">
    Edit
</a>
        <form method="POST" action="{{ route('manager.products.destroy', $product) }}"
              onsubmit="return confirm('Hapus produk {{ addslashes($product->name) }}?')">
            @csrf @method('DELETE')
            <button type="submit" 
   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors"
   onmousedown="this.style.transform='scale(0.95)'"
   onmouseup="this.style.transform='scale(1)'">
    Hapus
</button>
        </form>
    </div>
</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-12 text-center">
                        <p class="text-4xl mb-2">🍽️</p>
                        <p class="text-gray-500 font-medium">Belum ada produk</p>
                        <p class="text-gray-400 text-xs mt-1">Mulai dengan menambahkan produk pertama</p>
                        <a href="{{ route('manager.products.create') }}" class="btn-primary mt-3 inline-flex">+ Tambah Produk</a>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
    <div class="pagination-custom">
        {{ $products->links() }}
    </div>
</div>
    </div>

</div>
@endsection