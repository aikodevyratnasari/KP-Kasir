@extends('layouts.app')
@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')

@section('content')
<div class="max-w-2xl space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('manager.products.index') }}" class="text-gray-400 hover:text-gray-600">← Kembali</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('manager.products.update', $product) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 gap-4">

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-input @error('name') border-red-400 @enderror">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                        <select name="category_id" class="form-input">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" value="{{ old('price', $product->price) }}" min="0" class="form-input">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="description" rows="3" class="form-input">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Foto Produk</label>
                        @if($product->image)
                            <div class="mb-2">
                                <img src="{{ Storage::url($product->image) }}" class="w-20 h-20 rounded-lg object-cover">
                            </div>
                        @endif
                        <input type="file" name="image" accept="image/jpeg,image/png"
                               class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700">
                        <p class="mt-1 text-xs text-gray-400">Kosongkan jika tidak ingin mengubah foto</p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="is_available" value="0">
                            <input type="checkbox" name="is_available" id="is_available" value="1"
                                   {{ old('is_available', $product->is_available) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600">
                            <label for="is_available" class="text-sm text-gray-700">Tersedia untuk dipesan</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="track_stock" value="0">
                            <input type="checkbox" name="track_stock" id="track_stock" value="1"
                                   {{ old('track_stock', $product->track_stock) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600">
                            <label for="track_stock" class="text-sm text-gray-700">Lacak stok</label>
                        </div>
                    </div>

                    @if($product->track_stock)
                    <div class="col-span-2 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stok Saat Ini</label>
                            <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" min="0" class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alert Stok Rendah</label>
                            <input type="number" name="low_stock_alert" value="{{ old('low_stock_alert', $product->low_stock_alert) }}" min="0" class="form-input">
                        </div>
                    </div>
                    @endif
                </div>

            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <a href="{{ route('manager.products.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>

    {{-- Adjust Stock (terpisah) --}}
    @if($product->track_stock)
    <div class="card border-l-4 border-blue-400">
        <h3 class="font-semibold text-gray-800 mb-3">🔢 Setel Stok Langsung</h3>
        <form method="POST" action="{{ route('manager.products.adjust-stock', $product) }}" class="flex gap-3 items-end">
            @csrf @method('PATCH')
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Stok Baru</label>
                <input type="number" name="quantity" value="{{ $product->stock }}" min="0" class="form-input w-32">
            </div>
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan</label>
                <input type="text" name="notes" placeholder="Alasan penyesuaian..." class="form-input">
            </div>
            <button type="submit" class="btn-primary whitespace-nowrap">Perbarui Stok</button>
        </form>
    </div>
    @endif
</div>
@endsection