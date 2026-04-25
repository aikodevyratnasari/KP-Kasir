@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="card">
        {{-- Form utama: hanya PUT update, tidak ada form lain di dalamnya --}}
        <form method="POST" action="{{ route('admin.users.update', $user) }}" id="form-edit-user">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="form-input @error('name') form-input-error @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="form-input @error('email') form-input-error @enderror">
                    @if($user->hasVerifiedEmail())
                        <p class="mt-1 text-xs text-amber-500">
                            Mengubah email akan mereset status verifikasi — email verifikasi baru akan dikirim otomatis.
                        </p>
                    @endif
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                        <select name="role_id" class="form-input @error('role_id') form-input-error @enderror">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Store <span class="text-red-500">*</span></label>
                        <select name="store_id" class="form-input @error('store_id') form-input-error @enderror">
                            <option value="">Pilih Store</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ old('store_id', $user->store_id) == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('store_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input">
                </div>

                @if($user->id !== auth()->id())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="form-input">
                        <option value="active"   {{ old('status', $user->status) === 'active'   ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                @else
                    <input type="hidden" name="status" value="{{ $user->status }}">
                @endif
            </div>

            {{-- Tombol aksi — TIDAK ada <form> lain di sini --}}
            <div class="flex items-center justify-between mt-6 gap-3">
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.users.index') }}"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                       style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
                       onmouseover="this.style.backgroundColor='#f3f4f6';"
                       onmouseout="this.style.backgroundColor='white';">
                        Kembali
                    </a>

                    <a href="{{ route('admin.users.reset-password', $user) }}"
                       onclick="return confirm('Reset password {{ addslashes($user->name) }}?\nAnda akan diarahkan ke halaman reset password.')"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                       style="border: 1.5px solid #d97706; background-color: white; color: #d97706;"
                       onmouseover="this.style.backgroundColor='#fffbeb';"
                       onmouseout="this.style.backgroundColor='white';">
                        Ubah Password
                    </a>

                    @if($user->id !== auth()->id())
                    {{-- Tombol hapus: trigger form delete yang ada DI LUAR form utama --}}
                    <button type="button"
                            onclick="confirmDelete()"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                            style="border: 1.5px solid #dc2626; background-color: white; color: #dc2626;"
                            onmouseover="this.style.backgroundColor='#fef2f2';"
                            onmouseout="this.style.backgroundColor='white';">
                        Hapus User
                    </button>
                    @endif
                </div>

                <button type="submit"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                        style="background-color: #2D54BF;"
                        onmouseover="this.style.backgroundColor='#1e3d8f'"
                        onmouseout="this.style.backgroundColor='#2D54BF'"
                        onmousedown="this.style.transform='scale(0.98)';"
                        onmouseup="this.style.transform='scale(1)';">
                    Simpan Perubahan
                </button>
            </div>
        </form>
        {{-- ↑ Form utama ditutup di sini — tidak ada form lain di dalamnya --}}
    </div>
</div>

{{-- Form hapus: SEPENUHNYA di luar form utama, tidak nested --}}
@if($user->id !== auth()->id())
<form id="form-delete-user"
      method="POST"
      action="{{ route('admin.users.destroy', $user) }}"
      style="display: none;">
    @csrf @method('DELETE')
</form>
@endif

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Hapus user {{ addslashes($user->name) }} ({{ addslashes($user->email) }})?\n\nTindakan ini tidak dapat dibatalkan.')) {
        document.getElementById('form-delete-user').submit();
    }
}
</script>
@endpush
@endsection