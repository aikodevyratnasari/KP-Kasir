@extends('layouts.app')
@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $users->total() }} user terdaftar</p>
        <a href="{{ route('admin.users.create') }}" class="btn-primary text-sm">+ Tambah User</a>
    </div>

    {{-- Filter --}}
    <div class="card">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nama atau email..." class="form-input w-48">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                <select name="role" class="form-input w-auto">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->slug }}" {{ request('role') === $role->slug ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="form-input w-auto">
                    <option value="">Semua Status</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <button type="submit" class="btn-primary text-sm">Filter</button>
            <a href="{{ route('admin.users.index') }}" class="btn-secondary text-sm">Reset</a>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">Role</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">Store</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase">Email</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="py-3 px-4 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    {{-- User info --}}
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Role --}}
                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 rounded-md text-xs font-medium
                            @switch($user->role->slug)
                                @case('admin')    bg-purple-100 text-purple-700 @break
                                @case('manager')  bg-blue-100 text-blue-700 @break
                                @case('cashier')  bg-green-100 text-green-700 @break
                                @default          bg-orange-100 text-orange-700
                            @endswitch">
                            {{ $user->role->name }}
                        </span>
                    </td>

                    {{-- Store --}}
                    <td class="py-3 px-4 text-gray-600 text-xs">
                        {{ $user->store->name ?? '—' }}
                    </td>

                    {{-- Email verification --}}
                    <td class="py-3 px-4 text-center">
                        @if($user->hasVerifiedEmail())
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                ✓ Terverifikasi
                            </span>
                        @else
                            <div class="flex flex-col items-center gap-1">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                    ⏳ Menunggu
                                </span>
                                <form method="POST" action="{{ route('admin.users.resend-verification', $user) }}">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs text-indigo-500 hover:text-indigo-700 hover:underline">
                                        Kirim ulang
                                    </button>
                                </form>
                            </div>
                        @endif
                    </td>

                    {{-- Account status --}}
                    <td class="py-3 px-4 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $user->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $user->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>

                    {{-- Aksi --}}
                    <td class="py-3 px-4 text-right">
                        <div class="flex justify-end items-center gap-3">
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="text-xs text-indigo-600 hover:underline font-medium">Edit</a>

                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}"
                                      onsubmit="return confirm('{{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }} user {{ addslashes($user->name) }}?')">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="text-xs font-medium
                                                   {{ $user->status === 'active'
                                                      ? 'text-red-500 hover:underline'
                                                      : 'text-green-600 hover:underline' }}">
                                        {{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-gray-400">
                        <p class="text-3xl mb-2">👥</p>
                        <p>Belum ada user terdaftar</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
            {{ $users->links() }}
        </div>
    </div>

</div>
@endsection