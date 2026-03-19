@extends('layouts.app')
@section('title', 'Ubah Password User')
@section('page-title', 'Ubah Password User')

@section('content')
<div class="max-w-lg mx-auto space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.users.edit', $user) }}" class="text-gray-400 hover:text-gray-600">←</a>
        <h1 class="page-title">Ubah Password</h1>
    </div>

    {{-- Info user --}}
    <div class="card">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-indigo-600 text-white font-bold text-sm flex items-center justify-center flex-shrink-0">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <p class="font-semibold text-gray-900 truncate">{{ $user->name }}</p>
                    <span class="flex-shrink-0 px-2 py-0.5 rounded text-xs font-medium
                        @switch($user->role->slug)
                            @case('admin')   bg-purple-100 text-purple-700 @break
                            @case('manager') bg-blue-100 text-blue-700 @break
                            @case('cashier') bg-green-100 text-green-700 @break
                            @default         bg-orange-100 text-orange-700
                        @endswitch">
                        {{ $user->role->name }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <div class="card">
        <h3 class="font-semibold text-gray-800 mb-6">Buat Password Baru</h3>

        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
            @csrf
            @method('PATCH')

            <div class="space-y-4">
                {{-- Password Baru --}}
                <div x-data="{ show: false }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Password Baru <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required
                               placeholder="Masukkan password baru"
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
                    <p class="mt-1.5 text-xs text-gray-400">Min. 8 karakter, mengandung huruf besar, huruf kecil, dan angka</p>
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div x-data="{ show2: false }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="show2 ? 'text' : 'password'" name="password_confirmation" required
                               placeholder="Ulangi password baru"
                               class="form-input pr-10">
                        <button type="button" @click="show2 = !show2"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg x-show="!show2" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show2" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 mt-6 pt-5 border-t border-gray-100">
                <button type="submit" class="btn-primary">Simpan Password Baru</button>
                <a href="{{ route('admin.users.edit', $user) }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection