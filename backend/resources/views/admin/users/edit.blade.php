@extends('layouts.app')
@section('title', 'Edit User')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
        <h1 class="page-title">Edit User: {{ $user->name }}</h1>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input @error('name') form-input-error @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" value="{{ $user->email }}" disabled class="form-input bg-gray-50 text-gray-400 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-400">Email tidak dapat diubah</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                        <select name="role_id" class="form-input @error('role_id') form-input-error @enderror">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id',$user->role_id)==$role->id?'selected':'' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Store <span class="text-red-500">*</span></label>
                        <select name="store_id" class="form-input">
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ old('store_id',$user->store_id)==$store->id?'selected':'' }}>{{ $store->name }}</option>
                            @endforeach
                        </select>
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
                        <option value="active" {{ old('status',$user->status)==='active'?'selected':'' }}>Aktif</option>
                        <option value="inactive" {{ old('status',$user->status)==='inactive'?'selected':'' }}>Nonaktif</option>
                    </select>
                </div>
                @endif
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection