@extends('layouts.app')
@section('title', 'Buat Reservasi')
@section('page-title', 'Buat Reservasi')

@section('content')
<div class="max-w-xl space-y-5 mx-auto">
    <div class="card space-y-4">
        <h3 class="font-semibold text-gray-800 border-b border-gray-200 pb-3">Informasi Reservasi</h3>

        <form method="POST" action="{{ route('cashier.reservations.store') }}">
            @csrf

            {{-- Meja --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Meja <span class="text-red-500">*</span>
                </label>
                <select name="table_id" class="form-input @error('table_id') border-red-400 @enderror">
                    <option value="">Pilih Meja</option>
                    @foreach($tables as $table)
                        <option value="{{ $table->id }}" {{ old('table_id') == $table->id ? 'selected' : '' }}>
                            Meja {{ $table->number }} — {{ $table->capacity }} kursi
                            @if($table->section) ({{ $table->section }}) @endif
                        </option>
                    @endforeach
                </select>
                @error('table_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Nama Tamu --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Tamu <span class="text-red-500">*</span>
                </label>
                <input type="text" name="customer_name" value="{{ old('customer_name') }}"
                       placeholder="cth: Budi Santoso"
                       class="form-input @error('customer_name') border-red-400 @enderror">
                @error('customer_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- No. Telepon --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                <input type="text" name="customer_phone" value="{{ old('customer_phone') }}"
                       placeholder="08xxxxxxxxxx"
                       class="form-input">
            </div>

            {{-- Waktu Reservasi --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Waktu Reservasi <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" name="reserved_at"
                       value="{{ old('reserved_at') }}"
                       min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                       class="form-input @error('reserved_at') border-red-400 @enderror">
                @error('reserved_at')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Jumlah Tamu --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Tamu</label>
                <input type="number" name="guest_count" value="{{ old('guest_count') }}"
                       min="1" placeholder="cth: 4"
                       class="form-input">
            </div>

            {{-- Catatan --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2"
                          placeholder="Permintaan khusus, alergi, dll..."
                          class="form-input">{{ old('notes') }}</textarea>
            </div>
            </div>
            <div class="flex gap-3 justify-between">
                <a href="{{ route('cashier.tables.index') }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                        style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
                        onmouseover="this.style.backgroundColor='#f3f4f6';"
                        onmouseout="this.style.backgroundColor='white';">
                        Kembali
                    </a>
                <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all"
                style="color:white; background-color:#2D54BF; border:1px solid #2D54BF;"
                onmouseover="this.style.backgroundColor='#1e3d8f';"
                onmouseout="this.style.backgroundColor='#2D54BF';">
                Simpan Reservasi
                </button>
        </div>
        </form>
    </div>
    @push('scripts')
<script>
document.querySelectorAll('.form-input').forEach(input => {
    input.addEventListener('input', function() {
        this.classList.remove('border-red-400');
        const errorMsg = this.closest('div').querySelector('p.text-red-600');
        if (errorMsg) errorMsg.remove();
    });
    input.addEventListener('change', function() {
        this.classList.remove('border-red-400');
        const errorMsg = this.closest('div').querySelector('p.text-red-600');
        if (errorMsg) errorMsg.remove();
    });
});
</script>
@endpush
@endsection