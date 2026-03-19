@extends('layouts.app')
@section('title', 'Reset Password User')
@section('page-title', 'Reset Password User')

@section('content')
<div class="max-w-lg space-y-5">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Kembali ke Users</a>

    <div class="card">
        <h3 class="font-semibold text-gray-800 mb-1">Reset Password: {{ $user->name }}</h3>
        <p class="text-sm text-gray-500 mb-5">{{ $user->email }}</p>

        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
            @csrf
            @method('PATCH')

            <div class="mb-4" x-data="{ show: false }">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Password Baru <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" name="password" required
                       placeholder="Min. 8 karakter"
                       class="form-input pr-10 @error('password') border-red-400 @enderror">
                <button type="button" @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            <p class="mt-1 text-xs text-gray-400">Min. 8 karakter, huruf besar, kecil, dan angka</p>
            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Konfirmasi Password <span class="text-red-500">*</span>
            </label>
            <input type="password" name="password_confirmation" required
                   placeholder="Ulangi password baru"
                   class="form-input">
        </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Simpan Password Baru</button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection