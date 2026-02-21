@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <h1 class="text-2xl font-bold text-gray-900">Profil Saya</h1>

    {{-- Info Profil --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Informasi Profil</h2>
        <p class="text-sm text-gray-500 mb-4">Update nama dan nomor telepon Anda. Email tidak dapat diubah.</p>

        @if(session('status') === 'profile-updated')
            <div class="mb-4 text-sm text-green-600 bg-green-50 border border-green-200 rounded-lg px-4 py-2">
                ✅ Profil berhasil diperbarui.
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PATCH')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none
                              @error('name', 'profileInformation') border-red-400 @enderror">
                @error('name', 'profileInformation') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" value="{{ $user->email }}" disabled
                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                <p class="mt-1 text-xs text-gray-400">Email tidak dapat diubah. Hubungi Admin jika perlu perubahan.</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div class="mb-4 grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <input type="text" value="{{ $user->role->name }}" disabled
                           class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Store</label>
                    <input type="text" value="{{ $user->store->name ?? 'N/A' }}" disabled
                           class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                </div>
            </div>

            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                Simpan Perubahan
            </button>
        </form>
    </div>

    {{-- Ganti Password --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Ganti Password</h2>

        @if(session('status') === 'password-updated')
            <div class="mb-4 text-sm text-green-600 bg-green-50 border border-green-200 rounded-lg px-4 py-2">
                ✅ Password berhasil diperbarui.
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
                <input type="password" name="current_password" autocomplete="current-password"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none
                              @error('current_password', 'updatePassword') border-red-400 @enderror">
                @error('current_password', 'updatePassword') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                <input type="password" name="password" autocomplete="new-password"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none
                              @error('password', 'updatePassword') border-red-400 @enderror">
                @error('password', 'updatePassword') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-400">Min. 8 karakter, mengandung huruf besar, kecil, dan angka.</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" autocomplete="new-password"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <button type="submit"
                    class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                Ganti Password
            </button>
        </form>
    </div>

</div>
@endsection
