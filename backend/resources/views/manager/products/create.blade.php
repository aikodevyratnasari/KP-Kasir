@extends('layouts.app')
@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk')

@section('content')
<div class="max-w-2xl space-y-5">
    <a href="{{ route('manager.products.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Kembali</a>

    <form method="POST" action="{{ route('manager.products.store') }}"
          enctype="multipart/form-data"
          x-data="{
              trackStock: {{ old('track_stock') ? 'true' : 'false' }},
              imagePreview: null,
              handleImage(e) {
                  const file = e.target.files[0];
                  if (!file) return;
                  const reader = new FileReader();
                  reader.onload = ev => this.imagePreview = ev.target.result;
                  reader.readAsDataURL(file);
              }
          }">
        @csrf

        {{-- ── INFO DASAR ── --}}
        <div class="card space-y-4">
            <h3 class="font-semibold text-gray-800">Informasi Produk</h3>

            <div class="grid grid-cols-2 gap-4">
                {{-- Nama --}}
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="form-input @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="category_id" class="form-input @error('category_id') border-red-400 @enderror">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Harga --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" value="{{ old('price') }}" min="0" step="500"
                           class="form-input @error('price') border-red-400 @enderror">
                    @error('price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Deskripsi --}}
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="3"
                              class="form-input">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── FOTO PRODUK ── --}}
        <div class="card space-y-3">
            <h3 class="font-semibold text-gray-800">Foto Produk</h3>

            <div class="flex items-start gap-4">
                {{-- Preview --}}
                <div class="flex-shrink-0">
                    <div class="w-28 h-28 rounded-xl border-2 border-dashed border-gray-300 overflow-hidden bg-gray-50 flex items-center justify-center">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!imagePreview">
                            <span class="text-3xl text-gray-300">🖼️</span>
                        </template>
                    </div>
                </div>

                <div class="flex-1">
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                           @change="handleImage($event)"
                           class="block w-full text-sm text-gray-500
                                  file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                                  file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700
                                  hover:file:bg-indigo-100 cursor-pointer">
                    <p class="mt-1.5 text-xs text-gray-400">JPG, PNG, WebP — maks. 2MB</p>
                    @error('image')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ── STOK & KETERSEDIAAN ── --}}
        <div class="card space-y-4">
            <h3 class="font-semibold text-gray-800">Stok & Ketersediaan</h3>

            <div class="flex flex-wrap gap-5">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_available" value="0">
                    <input type="checkbox" name="is_available" value="1"
                           {{ old('is_available', 1) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">Tersedia untuk dipesan</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="track_stock" value="0">
                    <input type="checkbox" name="track_stock" value="1"
                           {{ old('track_stock') ? 'checked' : '' }}
                           @change="trackStock = $event.target.checked"
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">Lacak stok</span>
                </label>
            </div>

            {{-- Field stok — muncul saat lacak stok aktif --}}
            <div x-show="trackStock" x-transition class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok Awal</label>
                    <input type="number" name="stock" value="{{ old('stock', 0) }}" min="0"
                           class="form-input @error('stock') border-red-400 @enderror">
                    @error('stock')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alert Stok Rendah</label>
                    <input type="number" name="low_stock_alert" value="{{ old('low_stock_alert', 5) }}" min="0"
                           class="form-input">
                    <p class="mt-1 text-xs text-gray-400">Notifikasi saat stok ≤ nilai ini</p>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Simpan Produk</button>
            <a href="{{ route('manager.products.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection