@extends('layouts.app')
@section('title', 'Manajemen User')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="page-title">Manajemen User</h1>
        <a href="{{ route('admin.users.create') }}" class="btn-primary">+ Tambah User</a>
    </div>

    {{-- Filter --}}
    <div class="card">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / email..." class="form-input w-48"></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                <select name="role" class="form-input w-auto">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->slug }}" {{ request('role')===$role->slug?'selected':'' }}>{{ $role->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="form-input w-auto">
                    <option value="">Semua</option>
                    <option value="active" {{ request('status')==='active'?'selected':'' }}>Aktif</option>
                    <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Nonaktif</option>
                </select></div>
            <button type="submit" class="btn-primary">Cari</button>
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Reset</a>
        </form>
    </div>

    <div class="card p-0">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Store</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="py-3 px-4 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center">
                                {{ strtoupper(substr($user->name,0,2)) }}
                            </div>
                            <span class="font-medium text-gray-900">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-gray-600">{{ $user->email }}</td>
                    <td class="py-3 px-4"><span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs">{{ $user->role->name }}</span></td>
                    <td class="py-3 px-4 text-gray-600">{{ $user->store->name ?? '-' }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $user->status==='active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $user->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-xs text-indigo-600 hover:underline">Edit</a>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-xs {{ $user->status==='active' ? 'text-red-500' : 'text-green-600' }} hover:underline">
                                        {{ $user->status==='active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-gray-400">Tidak ada user ditemukan</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection