@extends('layouts.app')
@section('title', 'Edit Toko')
@section('page-title', 'Edit Toko')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Info ringkas toko --}}
    <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-50 border border-gray-200 text-sm text-gray-600">
        <img src="{{ $store->logo_url }}"
             class="w-8 h-8 rounded object-cover border border-gray-200 flex-shrink-0"
             alt="Logo {{ $store->name }}">
        <div>
            <span class="font-medium text-gray-800">{{ $store->name }}</span>
            <span class="mx-2 text-gray-300">|</span>
            <span>{{ $store->users_count }} user</span>
            <span class="mx-1 text-gray-300">·</span>
            <span class="{{ $store->active_users_count > 0 ? 'text-green-600' : 'text-gray-400' }}">
                {{ $store->active_users_count }} aktif
            </span>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('admin.stores.update', $store) }}"
              id="form-edit-store" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 gap-4">

                {{-- Logo --}}
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Logo Toko</label>
    <div class="flex items-center gap-4">
        <img id="logo-preview" src="{{ $store->logo_url }}"
             class="w-16 h-16 rounded-lg object-cover border border-gray-200 flex-shrink-0"
             alt="Logo saat ini">
        <div class="flex-1">
            <input type="file" name="logo"
                   accept="image/png,image/jpg,image/jpeg,image/webp"
                   id="logoInput"
                   class="hidden"
                   onchange="previewLogo(event)">
            <button type="button"
                onclick="document.getElementById('logoInput').click()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
                style="border: 1px solid #2D54BF; background-color: #2D54BF; color: white;"
                onmouseover="this.style.backgroundColor='#1e3d8f'"
                onmouseout="this.style.backgroundColor='#2D54BF'">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 12V4m0 0L8 8m4-4l4 4"/>
                </svg>
                Upload
            </button>
            <span id="logoFileName" class="ml-2 text-xs text-gray-500">Tidak ada file yang dipilih</span>
            <p class="mt-1 text-xs text-gray-400">PNG/JPG/WEBP, maks. 1 MB. Kosongkan jika tidak ingin mengganti logo.</p>
            @error('logo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Toko <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $store->name) }}"
                           class="form-input @error('name') form-input-error @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Alamat --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea name="address" rows="2"
                              class="form-input @error('address') form-input-error @enderror"
                    >{{ old('address', $store->address) }}</textarea>
                    @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Telepon & Email --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone', $store->phone) }}"
                               class="form-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $store->email) }}"
                               class="form-input @error('email') form-input-error @enderror">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- NPWP & Tax Rate --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Pajak (NPWP)</label>
                        <input type="text" name="tax_number"
                               value="{{ old('tax_number', $store->tax_number) }}"
                               class="form-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tarif Pajak (%) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="tax_rate" step="0.01" min="0" max="100"
                               value="{{ old('tax_rate', $store->tax_rate) }}"
                               class="form-input @error('tax_rate') form-input-error @enderror">
                        @error('tax_rate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Receipt Footer --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Footer Struk</label>
                    <textarea name="receipt_footer" rows="2"
                              class="form-input"
                    >{{ old('receipt_footer', $store->receipt_footer) }}</textarea>
                </div>

                {{-- Opsi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Opsi</label>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">

                        {{-- Kitchen --}}
                        <div class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg">
                            <input type="hidden" name="has_kitchen" value="0">
                            <input type="checkbox" name="has_kitchen" value="1" id="has_kitchen"
                                   {{ old('has_kitchen', $store->has_kitchen) ? 'checked' : '' }}
                                   class="mt-0.5 w-4 h-4 text-indigo-600 rounded">
                            <div>
                                <label for="has_kitchen" class="text-sm font-medium text-gray-700 cursor-pointer">
                                    Kitchen Display
                                </label>
                                <p class="text-xs text-gray-400">Aktifkan layar dapur untuk toko ini.</p>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg
                            @if($store->active_users_count > 0 && $store->is_active) opacity-60 @endif">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" id="is_active"
                                   {{ old('is_active', $store->is_active) ? 'checked' : '' }}
                                   class="mt-0.5 w-4 h-4 text-indigo-600 rounded"
                                   @if($store->active_users_count > 0 && $store->is_active)
                                       title="Tidak bisa dinonaktifkan, masih ada {{ $store->active_users_count }} user aktif"
                                   @endif>
                            <div>
                                <label for="is_active" class="text-sm font-medium text-gray-700 cursor-pointer">
                                    Toko Aktif
                                </label>
                                @if($store->active_users_count > 0 && $store->is_active)
                                    <p class="text-xs text-red-500">
                                        Ada {{ $store->active_users_count }} user aktif. Nonaktifkan user dulu sebelum menonaktifkan toko.
                                    </p>
                                @else
                                    <p class="text-xs text-gray-400">Toko nonaktif tidak muncul di pilihan user baru.</p>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </form>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.stores.index') }}"
           class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
           style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
           onmouseover="this.style.backgroundColor='#f3f4f6';"
           onmouseout="this.style.backgroundColor='white';">
            Kembali
        </a>
        <button type="submit" form="form-edit-store"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                style="background-color: #2D54BF;"
                onmouseover="this.style.backgroundColor='#1e3d8f';"
                onmouseout="this.style.backgroundColor='#2D54BF';"
                onmousedown="this.style.transform='scale(0.98)';"
                onmouseup="this.style.transform='scale(1)';">
            Simpan Perubahan
        </button>
    </div>

</div>

@push('scripts')
<script>
function previewLogo(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => document.getElementById('logo-preview').src = e.target.result;
    reader.readAsDataURL(file);
    document.getElementById('logoFileName').textContent = file.name;
}

document.querySelectorAll('.form-input').forEach(input => {
    ['input', 'change'].forEach(evt => {
        input.addEventListener(evt, function () {
            this.classList.remove('form-input-error');
            const err = this.closest('div')?.querySelector('p.text-red-600');
            if (err) err.remove();
        });
    });
});
</script>
@endpush
@endsection