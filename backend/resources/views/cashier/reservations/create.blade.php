@extends('layouts.app')
@section('title', 'Buat Reservasi')
@section('page-title', 'Buat Reservasi')

@section('content')
<div class="max-w-xl space-y-5">

    <a href="{{ route('cashier.tables.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Kembali ke Status Meja</a>

    <div class="card space-y-4">
        <h3 class="font-semibold text-gray-800">Informasi Reservasi</h3>

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

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Simpan Reservasi</button>
                <a href="{{ route('cashier.tables.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>

</div>
@endsection