@extends('layouts.app')
@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Informasi Profil --}}
    <div class="card">
        <h2 class="text-base font-semibold text-gray-800 pb-3 mb-4 border-b border-gray-200">Informasi Profil</h2>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PATCH')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus
                           class="form-input @error('name', 'profileInformation') border-red-400 @enderror">
                    @error('name', 'profileInformation')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <div class="flex items-center gap-2">
                        <input type="email" value="{{ $user->email }}" disabled
                               class="form-input flex-1 bg-gray-50 text-gray-400 cursor-not-allowed">
                        @if($user->hasVerifiedEmail())
    <span class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-white whitespace-nowrap"
      style="background:#16a34a; border-radius:999px;">
    Verified
</span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-yellow-100 text-yellow-700 whitespace-nowrap">
                                ⚠ Belum diverifikasi
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Email tidak dapat diubah. Hubungi Admin jika perlu perubahan.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           placeholder="08xxxxxxxxxx" class="form-input">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <input type="text" value="{{ $user->role->name }}" disabled
                               class="form-input bg-gray-50 text-gray-400 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Store</label>
                        <input type="text" value="{{ $user->store->name ?? 'N/A' }}" disabled
                               class="form-input bg-gray-50 text-gray-400 cursor-not-allowed">
                    </div>
                </div>
            </div>

            <div class="mt-4 text-right">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    {{-- Ganti Password --}}
    <div class="card">
        <h2 class="text-base font-semibold text-gray-800">Ganti Password</h2>
        <p class="text-sm text-gray-500 mb-5 pb-3 mb-4 border-b border-gray-200">Gunakan password yang kuat dan unik untuk keamanan akun Anda</p>

        @if(session('status') === 'password-updated')
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                Password berhasil diperbarui.
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
                    <input type="password" name="current_password" autocomplete="current-password"
                           class="form-input @error('current_password', 'updatePassword') border-red-400 @enderror">
                    @error('current_password', 'updatePassword')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input type="password" name="password" autocomplete="new-password"
                           class="form-input @error('password', 'updatePassword') border-red-400 @enderror">
                    @error('password', 'updatePassword')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">Min. 8 karakter, mengandung huruf besar, kecil, dan angka.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password"
                           class="form-input">
                </div>
            </div>

            <div class="mt-4 text-right">
                <button type="submit" class="btn-primary">Ganti Password</button>
            </div>
        </form>
    </div>

</div>
@endsection