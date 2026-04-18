@extends('layouts.app')
@section('title', 'Tambah User')
@section('page-title', 'Tambah User Baru')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <div class="card">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input @error('name') form-input-error @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input @error('email') form-input-error @enderror">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" class="form-input @error('password') form-input-error @enderror">
                    <p class="mt-1 text-xs text-gray-400">Min. 8 karakter, huruf besar, kecil, dan angka</p>
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                        <select name="role_id" class="form-input @error('role_id') form-input-error @enderror">
                            <option value="">Pilih Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id')==$role->id?'selected':'' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Store <span class="text-red-500">*</span></label>
                        <select name="store_id" class="form-input @error('store_id') form-input-error @enderror">
                            <option value="">Pilih Store</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ old('store_id')==$store->id?'selected':'' }}>{{ $store->name }}</option>
                            @endforeach
                        </select>
                        @error('store_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-input">
                </div>
            </div>
        </form>
    </div>
    <div class="flex justify-between mt-6">
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
           style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
           onmouseover="this.style.backgroundColor='#f3f4f6';"
           onmouseout="this.style.backgroundColor='white';">
            Kembali
        </a>
        <button type="submit"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                style="background-color: #2D54BF;"
                onmouseover="this.style.backgroundColor='#1e3d8f';"
                onmouseout="this.style.backgroundColor='#2D54BF';"
                onmousedown="this.style.transform='scale(0.98)';"
                onmouseup="this.style.transform='scale(1)';">
            Simpan User
        </button>
    </div>
</div>
@push('scripts')
<script>
document.querySelectorAll('.form-input').forEach(input => {
    input.addEventListener('input', function() {
        this.classList.remove('form-input-error');
        const errorMsg = this.closest('div').querySelector('p.text-red-600');
        if (errorMsg) errorMsg.remove();
    });
    input.addEventListener('change', function() {
        this.classList.remove('form-input-error');
        const errorMsg = this.closest('div').querySelector('p.text-red-600');
        if (errorMsg) errorMsg.remove();
    });
});
</script>
@endpush
@endsection