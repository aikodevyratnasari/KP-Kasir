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
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                style="background-color: #2D54BF; border: 1px solid #2D54BF;"
                onmouseover="this.style.backgroundColor='#1e3d8f'"
                onmouseout="this.style.backgroundColor='#2D54BF'">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="6" x2="20" y2="6"/>
                    <circle cx="16" cy="6" r="2"/>
                    <line x1="4" y1="12" x2="20" y2="12"/>
                    <circle cx="8" cy="12" r="2"/>
                    <line x1="4" y1="18" x2="20" y2="18"/>
                    <circle cx="14" cy="18" r="2"/>
                </svg>
                Filter
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn-secondary text-sm inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74"/>
                    <path d="M3 3v6h6"/>
                </svg>
                Reset
            </a>
            <div class="ml-auto flex items-center gap-2">
                <a href="{{ route('admin.users.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                    style="background-color: #2D54BF; border: 1px solid #2D54BF;"
                    onmouseover="this.style.backgroundColor='#1e3d8f'"
                    onmouseout="this.style.backgroundColor='#2D54BF'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <line x1="19" y1="8" x2="19" y2="14"/>
                        <line x1="22" y1="11" x2="16" y2="11"/>
                    </svg>
                    Tambah User
                </a>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $users->total() }} user terdaftar</p>
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
                    <th class="py-2.5 px-4 text-center text-xs font-semibold text-gray-500 uppercase">AKSI</th>
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
                        <span class="inline-block py-0.5 rounded-md text-xs font-medium
                            @switch($user->role->slug)
                                @case('admin')    bg-100 text-700 @break
                                @case('manager')  bg-100 text-700 @break
                                @case('cashier')  bg-100 text-700 @break
                                @default          bg-100 text-700
                            @endswitch"
                            style="min-width:70px; text-align:center;">
                            {{ $user->role->name }}
                        </span>
                    </td>

                    <td class="py-3 px-4 text-gray-600 text-xs whitespace-nowrap">
                        {{ $user->store->name ?? '—' }}
                    </td>

                    <td class="py-3 px-4 text-center">
                        @if($user->hasVerifiedEmail())
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                                style="background-color:#e8eaf6; color:#1a237e;">
                                Terverifikasi
                            </span>
                        @else
                            <div class="flex flex-col items-center gap-1">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
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
                        <span class="inline-flex items-center justify-center rounded-full text-xs font-semibold
                            {{ $user->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}"
                            style="width:80px; padding: 2px 0;">
                            {{ $user->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>

                    <td class="py-3 px-4 text-center">
                        <div class="flex justify-center items-center gap-2">

                            {{-- Edit --}}
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="inline-flex items-center justify-center transition-all p-1"
                               style="color: #EF8F00;"
                               onmouseover="this.style.color='#cc7a00';"
                               onmouseout="this.style.color='#EF8F00';"
                               title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>

                            @if($user->id !== auth()->id())

                                {{-- Toggle status --}}
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}"
                                      onsubmit="return confirm('{{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }} user {{ addslashes($user->name) }}?')">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="inline-flex items-center justify-center transition-all p-1"
                                            style="{{ $user->status === 'active' ? 'color:#dc2626;' : 'color:#16a34a;' }} background:none; border:none;"
                                            onmouseover="this.style.color='{{ $user->status === 'active' ? '#b91c1c' : '#15803d' }}';"
                                            onmouseout="this.style.color='{{ $user->status === 'active' ? '#dc2626' : '#16a34a' }}';"
                                            title="{{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        @if($user->status === 'active')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"/>
                                                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                                <polyline points="22 4 12 14.01 9 11.01"/>
                                            </svg>
                                        @endif
                                    </button>
                                </form>

                                {{-- Hapus --}}
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                      onsubmit="return confirm('Hapus user {{ addslashes($user->name) }}?\n({{ addslashes($user->email) }})\n\nTindakan ini tidak dapat dibatalkan.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center justify-center transition-all p-1"
                                            style="color:#9ca3af; background:none; border:none;"
                                            onmouseover="this.style.color='#dc2626';"
                                            onmouseout="this.style.color='#9ca3af';"
                                            title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                            <path d="M10 11v6"/>
                                            <path d="M14 11v6"/>
                                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                        </svg>
                                    </button>
                                </form>

                            @else
                                {{-- Spacer agar alignment tetap rapi untuk baris admin sendiri --}}
                                <span style="width: 52px; display: inline-block;"></span>
                            @endif

                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-gray-400">
                        <div class="flex justify-center mb-2">
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
</div>
@endsection