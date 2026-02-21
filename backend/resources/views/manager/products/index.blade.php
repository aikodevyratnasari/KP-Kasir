@extends('layouts.app')
@section('title', 'Manajemen Menu')
@section('page-title', 'Menu & Produk')

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $products->total() }} produk terdaftar</p>
        <div class="flex gap-2">
            <a href="{{ route('manager.products.trashed') }}" class="btn-secondary text-sm">🗑️ Sampah</a>
            <a href="{{ route('manager.products.create') }}" class="btn-primary text-sm">+ Tambah Produk</a>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama produk..." class="form-input w-44">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
                <select name="category" class="form-input w-auto">
                    <option value="">Semua</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Urutkan</label>
                <select name="sort" class="form-input w-auto">
                    <option value="">Terbaru</option>
                    <option value="name" {{ request('sort')==='name'?'selected':'' }}>Nama A-Z</option>
                    <option value="price" {{ request('sort')==='price'?'selected':'' }}>Harga</option>
                </select>
            </div>
            <button type="submit" class="btn-primary text-sm">Filter</button>
            <a href="{{ route('manager.products.index') }}" class="btn-secondary text-sm">Reset</a>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Produk</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategori</th>
                    <th class="py-3 px-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Harga</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Stok</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Tersedia</th>
                    <th class="py-3 px-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($products as $product)
                <tr class="hover:bg-gray-50 transition-colors">
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
                                    <p class="text-xs text-gray-400 truncate max-w-xs">{{ Str::limit($product->description, 50) }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-md text-xs">
                            {{ $product->category?->name ?? '—' }}
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
                    <td class="py-3 px-4 text-right">
                        <div class="flex justify-end items-center gap-3">
                            <a href="{{ route('manager.products.edit', $product) }}"
                               class="text-xs text-indigo-600 hover:text-indigo-800 font-medium hover:underline">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('manager.products.destroy', $product) }}"
                                  onsubmit="return confirm('Hapus produk {{ addslashes($product->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium hover:underline">
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
            {{ $products->links() }}
        </div>
    </div>

</div>
@endsection