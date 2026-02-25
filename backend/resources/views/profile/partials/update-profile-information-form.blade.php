<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Informasi Profil</h2>
        <p class="mt-1 text-sm text-gray-600">
            Perbarui nama dan nomor telepon. Email tidak dapat diubah.
        </p>
    </header>

    @if(session('status') === 'profile-updated')
        <div class="mt-4 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            ✅ Profil berhasil diperbarui.
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf @method('PATCH')

        {{-- Nama --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <input type="text" id="name" name="name"
                   value="{{ old('name', $user->name) }}" required autofocus
                   class="form-input @error('name', 'profileInformation') border-red-400 @enderror">
            @error('name', 'profileInformation')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email (read-only) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <div class="flex items-center gap-2">
                <input type="email" value="{{ $user->email }}" disabled
                       class="form-input flex-1 bg-gray-50 text-gray-400 cursor-not-allowed">
                @if($user->hasVerifiedEmail())
                    <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 whitespace-nowrap">
                        ✓ Terverifikasi
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-yellow-100 text-yellow-700 whitespace-nowrap">
                        ⚠ Belum diverifikasi
                    </span>
                @endif
            </div>
            <p class="mt-1 text-xs text-gray-400">Email tidak dapat diubah. Hubungi Admin jika perlu perubahan.</p>
        </div>

        {{-- Telepon --}}
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
            <input type="text" id="phone" name="phone"
                   value="{{ old('phone', $user->phone) }}"
                   placeholder="08xxxxxxxxxx"
                   class="form-input">
        </div>

        {{-- Role & Store (read-only) --}}
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

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</section>