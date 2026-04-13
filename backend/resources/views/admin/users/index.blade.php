@extends('layouts.app')
@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')
<div class="space-y-5">

    {{-- Filter --}}
    <div class="card overflow-x-auto">
        <form method="GET" class="flex flex-wrap gap-3 items-end min-w-max">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nama atau email..." class="form-input w-48">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                <select name="role" class="form-input w-44">
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
                <select name="status" class="form-input w-44">
                    <option value="">Semua Status</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                style="background-color: #2D54BF; border: 1px solid #2D54BF;"
                onmouseover="this.style.backgroundColor='#1e3d8f'"
                onmouseout="this.style.backgroundColor='#2D54BF'">
                Filter
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn-secondary text-sm">Reset</a>
            <div class="ml-auto flex items-center gap-2">
                <a href= "{{ route('admin.users.create') }}" 
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                    style="background-color: #2D54BF; border: 1px solid #2D54BF;"
                    onmouseover="this.style.backgroundColor='#1e3d8f'"
                    onmouseout="this.style.backgroundColor='#2D54BF'">
                    Tambah User
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">Role</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">Store</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase">Email</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="py-2.5 px-4 text-xs font-semibold text-gray-500" style="text-align: right; padding-right: 90px;">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
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

                    <td class="py-3 px-4 whitespace-nowrap">
                        <span class="px-4 py-0.5 rounded-md text-xs font-medium
                            @switch($user->role->slug)
                                @case('admin')    bg-purple-100 text-purple-700 @break
                                @case('manager')  bg-blue-100 text-blue-700 @break
                                @case('cashier')  bg-green-100 text-green-700 @break
                                @default          bg-orange-100 text-orange-700
                            @endswitch">
                            {{ $user->role->name }}
                        </span>
                    </td>

                    <td class="py-3 px-4 text-gray-600 text-xs whitespace-nowrap">
                        {{ $user->store->name ?? '—' }}
                    </td>

                    <td class="py-3 px-4 text-center">
                        @if($user->hasVerifiedEmail())
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                {{-- check --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Terverifikasi
                            </span>
                        @else
                            <div class="flex flex-col items-center gap-1">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                    {{-- hourglass --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 22h14"/><path d="M5 2h14"/><path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"/><path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"/>
                                    </svg>
                                    Menunggu
                                </span>
                                <form method="POST" action="{{ route('admin.users.resend-verification', $user) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-indigo-500 hover:text-indigo-700 hover:underline">
                                        Kirim ulang
                                    </button>
                                </form>
                            </div>
                        @endif
                    </td>

                    <td class="py-3 px-4 text-center">
                        <span class="inline-flex items-center px-4 py-0.5 rounded-full text-xs font-semibold
                            {{ $user->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $user->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>

                    <td class="py-3 px-4 text-right">
                        <div class="flex justify-end items-center gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg transition-all"
                            style="color: #EF8F00; background-color: #fff8ec; border: 1 px solid #EF8F00;"
                            onmouseover="this.style.backgroundColor='#ffefd0';"
                            onmouseout="this.style.backgroundColor='#fff8ec';"
                            onmousedown="this.style.transform='scale(0.95)';"
                            onmouseup="this.style.transform='scale(1)';">
                                Edit
                            </a>
                            @if($user->id !== auth()->id())
    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}"
          onsubmit="return confirm('{{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }} user {{ addslashes($user->name) }}?')">
        @csrf @method('PATCH')
        <button type="submit"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg transition-all"
                style="{{ $user->status === 'active'
                    ? 'color: #dc2626; background-color: #fff1f2; border: 1px solid #fca5a5;'
                    : 'color: #16a34a; background-color: #f0fdf4; border: 1px solid #86efac;' }}"
                onmouseover="this.style.backgroundColor='{{ $user->status === 'active' ? '#fee2e2' : '#dcfce7' }}';"
                onmouseout="this.style.backgroundColor='{{ $user->status === 'active' ? '#fff1f2' : '#f0fdf4' }}';"
                onmousedown="this.style.transform='scale(0.98)';"
                onmouseup="this.style.transform='scale(1)';">
            {{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
        </button>
    </form>
@else
    <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg"
          style="color: transparent; background-color: transparent; border: 1px solid transparent; pointer-events: none;">
        Nonaktifkan
    </span>
@endif
    </div>
</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-gray-400">
                        <div class="flex justify-center mb-2">
                            {{-- users --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
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
<div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $users->total() }} user terdaftar</p>
    </div>
</div>
@endsection