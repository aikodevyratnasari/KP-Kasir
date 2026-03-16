@extends('layouts.app')
@section('title', 'Kategori')
@section('page-title', 'Manajemen Kategori')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Form Tambah Kategori --}}
    <div class="card h-fit">
        <h2 class="font-semibold text-gray-800 mb-4">+ Tambah Kategori</h2>
        <form method="POST" action="{{ route('manager.categories.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="mis. Makanan Utama" class="form-input @error('name') border-red-400 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" class="form-input" placeholder="Opsional...">{{ old('description') }}</textarea>
                </div>
                <div>
    <label class="block text-xs font-medium text-gray-600 mb-1">Foto (opsional)</label>
    <input type="file" name="image" id="imageInputKategori" accept="image/jpeg,image/png" class="hidden">
    <button type="button"
        onclick="document.getElementById('imageInputKategori').click()"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 border border-gray-300 rounded-lg transition-colors"
        onmouseover="this.style.backgroundColor='#e5e7eb'"
        onmouseout="this.style.backgroundColor='#f3f4f6'">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 12V4m0 0L8 8m4-4l4 4"/>
        </svg>
        Upload
    </button>
    <span id="fileNameKategori" class="ml-2 text-xs text-gray-500">Tidak ada file yang dipilih</span>
</div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Urutan Tampil</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="form-input w-24">
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', 1) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600">
                    <label for="is_active" class="text-sm text-gray-700">Aktif</label>
                </div>
            </div>
            <button type="submit" class="btn-primary w-full justify-center mt-4">Simpan Kategori</button>
        </form>
    </div>

    {{-- Daftar Kategori --}}
    <div class="lg:col-span-2 space-y-3">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">{{ $categories->total() }} kategori terdaftar</p>
        </div>

        @forelse($categories as $category)
            <div class="card" x-data="{ editing: false }">
                {{-- View mode --}}
                <div x-show="!editing" class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @if($category->image)
                            <img src="{{ Storage::url($category->image) }}" class="w-10 h-10 rounded-lg object-cover">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-xl">📁</div>
                        @endif
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-gray-900">{{ $category->name }}</p>
                                <span class="px-1.5 py-0.5 rounded text-xs {{ $category->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400">{{ $category->products_count }} produk</p>
                        </div>
                    </div>
                    <div class="flex gap-2 items-center">
    <button @click="editing = true"
        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
        style="color: #EF8F00; background-color: #fff8ec; border: 1px solid #EF8F00;"
        onmouseover="this.style.backgroundColor='#ffefd0'"
        onmouseout="this.style.backgroundColor='#fff8ec'"
        onmousedown="this.style.transform='scale(0.95)'"
        onmouseup="this.style.transform='scale(1)'">
        Edit
    </button>
    @if($category->products_count === 0)
        <form method="POST" action="{{ route('manager.categories.destroy', $category) }}"
              x-on:submit.prevent="if(confirm('Hapus kategori {{ $category->name }}?')) $el.submit()">
            @csrf @method('DELETE')
            <button type="submit"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors"
                onmousedown="this.style.transform='scale(0.95)'"
                onmouseup="this.style.transform='scale(1)'">
                Hapus
            </button>
        </form>
    @else
        <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-50 border border-gray-200 rounded-lg cursor-not-allowed" title="Ada produk di kategori ini">Hapus</span>
    @endif
</div>
                </div>

                {{-- Edit mode --}}
                <div x-show="editing" x-transition>
                    <form method="POST" action="{{ route('manager.categories.update', $category) }}" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nama <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ $category->name }}" class="form-input">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                                <textarea name="description" rows="2" class="form-input">{{ $category->description }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Foto baru</label>
                                <input type="file" name="image" accept="image/jpeg,image/png"
                                       class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-700">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Urutan</label>
                                <input type="number" name="sort_order" value="{{ $category->sort_order }}" min="0" class="form-input">
                            </div>
                            <div class="col-span-2 flex items-center gap-2">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600">
                                <label class="text-sm text-gray-700">Aktif</label>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="btn-primary text-xs">Simpan</button>
                            <button type="button" @click="editing = false" class="btn-secondary text-xs">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="card text-center py-10 text-gray-400">
                <p class="text-3xl mb-2">📁</p>
                <p>Belum ada kategori. Buat kategori pertama di sebelah kiri.</p>
            </div>
        @endforelse

        <div>{{ $categories->links() }}</div>
    </div>
</div>

@endsection