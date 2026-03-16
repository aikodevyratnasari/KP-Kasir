@extends('layouts.app')
@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk')

@section('content')
<div class="max-w-2xl space-y-5 mx-auto">

    <div class="card">
        <form method="POST" action="{{ route('manager.products.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 gap-4">

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-input @error('name') border-red-400 @enderror">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                        <select name="category_id" class="form-input @error('category_id') border-red-400 @enderror">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" value="{{ old('price') }}" min="0" class="form-input @error('price') border-red-400 @enderror">
                        @error('price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="description" rows="3" class="form-input">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Foto Produk</label>
                        {{-- Sembunyikan input file asli, tampilkan button custom --}}
                        <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png" class="hidden">
                       <button type="button"
                            onclick="document.getElementById('imageInput').click()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 border border-gray-300 rounded-lg transition-colors"
                            onmouseover="this.style.backgroundColor='#e5e7eb'"
                            onmouseout="this.style.backgroundColor='#f3f4f6'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 12V4m0 0L8 8m4-4l4 4"/>
                            </svg>
                            Upload
                        </button>
                        <span id="fileName" class="ml-2 text-sm text-gray-500">Tidak ada file yang dipilih</span>
                        <p class="mt-1 text-xs text-gray-400">Max 1MB, JPG/PNG</p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="is_available" value="0">
                            <input type="checkbox" name="is_available" id="is_available" value="1"
                                   {{ old('is_available', 1) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="is_available" class="text-sm text-gray-700">Tersedia untuk dipesan</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="track_stock" value="0">
                            <input type="checkbox" name="track_stock" id="track_stock" value="1"
                                   {{ old('track_stock') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                   x-on:change="$refs.stockFields.style.display = $event.target.checked ? '' : 'none'">
                            <label for="track_stock" class="text-sm text-gray-700">Lacak stok</label>
                        </div>
                    </div>

                    <div class="col-span-2 grid grid-cols-2 gap-4" x-ref="stockFields"
                         style="{{ old('track_stock') ? '' : 'display:none' }}">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stok Awal</label>
                            <input type="number" name="stock" value="{{ old('stock', 0) }}" min="0" class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alert Stok Rendah</label>
                            <input type="number" name="low_stock_alert" value="{{ old('low_stock_alert', 5) }}" min="0" class="form-input">
                        </div>
                    </div>
                </div>

            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="submit" class="btn-primary">Simpan Produk</button>
                <a href="{{ route('manager.products.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>

    <a href="{{ route('manager.products.index') }}" class="btn-primary">Kembali</a>

</div>

@push('scripts')
<script>
    document.getElementById('imageInput').addEventListener('change', function() {
        const fileName = this.files[0] ? this.files[0].name : 'Tidak ada file yang dipilih';
        document.getElementById('fileName').textContent = fileName;
    });
</script>
@endpush

@endsection