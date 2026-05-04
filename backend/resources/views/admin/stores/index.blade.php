@extends('layouts.app')
@section('title', 'Manajemen Toko')
@section('page-title', 'Manajemen Toko')

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $stores->count() }} toko terdaftar</p>
        <a href="{{ route('admin.stores.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
           style="background-color: #2D54BF; border: 1px solid #2D54BF;"
           onmouseover="this.style.backgroundColor='#1e3d8f'"
           onmouseout="this.style.backgroundColor='#2D54BF'">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
                <line x1="12" y1="6" x2="12" y2="6"/>
            </svg>
            Tambah Toko
        </a>
    </div>

    {{-- Tabel --}}
    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">Toko</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">Kontak</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase">Pajak</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase">User</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase">Dapur</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase">Pusat</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($stores as $store)
                <tr class="hover:bg-gray-50 transition-colors">

                    {{-- Toko --}}
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            <div class="relative flex-shrink-0">
                                <img src="{{ $store->logo_url }}"
                                     class="w-9 h-9 rounded-lg object-cover border border-gray-200"
                                     alt="Logo {{ $store->name }}">
                                @if($store->is_headquarters)
                                    <span class="absolute w-4 h-4 rounded-full flex items-center justify-center"
                                        style="background-color: #f59e0b; top: -4px; left: -4px;"
                                        title="Cabang Pusat">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5 text-white" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    </span>
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <p class="font-medium text-gray-900">{{ $store->name }}</p>
                                </div>
                                <p class="text-xs text-gray-400 max-w-xs truncate">{{ $store->address ?? '—' }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Kontak --}}
                    <td class="py-3 px-4 text-gray-600 text-xs whitespace-nowrap">
                        <p>{{ $store->phone ?? '—' }}</p>
                        <p>{{ $store->email ?? '—' }}</p>
                    </td>

                    {{-- Pajak --}}
                    <td class="py-3 px-4 text-center text-gray-600 text-xs whitespace-nowrap">
                        {{ number_format($store->tax_rate, 2) }}%
                    </td>

                    {{-- User count --}}
                    <td class="py-3 px-4 text-center whitespace-nowrap">
                        <a href="{{ route('admin.users.index', ['store' => $store->id]) }}"
                           class="text-xs font-medium hover:underline"
                           style="color: #2D54BF;"
                           title="Lihat semua user toko ini">
                            {{ $store->users_count }} user
                        </a>
                        @if($store->active_users_count > 0)
                            <span class="block text-xs text-gray-400">
                                {{ $store->active_users_count }} aktif
                            </span>
                        @endif
                    </td>

                    {{-- Dapur --}}
                    <td class="py-3 px-4 text-center">
                        <span class="inline-flex items-center justify-center rounded-full text-xs font-semibold
                            {{ $store->has_kitchen ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-100 text-gray-500' }}"
                            style="min-width:52px; padding: 2px 8px;">
                            {{ $store->has_kitchen ? 'Ya' : 'Tidak' }}
                        </span>
                    </td>

                    {{-- Cabang Pusat --}}
                    <td class="py-3 px-4 text-center">
                        @if($store->is_headquarters)
                            {{-- Sudah HQ — tampilkan badge, tidak bisa dicabut lewat klik --}}
                            <span class="inline-flex items-center gap-1 rounded-full text-xs font-semibold"
                                  style="background-color:#fef3c7; color:#92400e; border:1px solid #fcd34d; padding:2px 10px; white-space:nowrap;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                Pusat
                            </span>
                        @else
                            {{-- Bukan HQ — tombol untuk menjadikan HQ --}}
                            <form method="POST" action="{{ route('admin.stores.set-headquarters', $store) }}"
                                  onsubmit="return confirm('Jadikan \'{{ addslashes($store->name) }}\' sebagai cabang pusat?\nLogo toko ini akan digunakan di halaman login.')">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center gap-1 rounded-full text-xs font-medium transition-colors"
                                        style="background-color:#f3f4f6; color:#6b7280; border:1px solid #e5e7eb; padding:2px 10px; white-space:nowrap; cursor:pointer;"
                                        onmouseover="this.style.backgroundColor='#fef3c7'; this.style.color='#92400e'; this.style.borderColor='#fcd34d';"
                                        onmouseout="this.style.backgroundColor='#f3f4f6'; this.style.color='#6b7280'; this.style.borderColor='#e5e7eb';"
                                        title="Jadikan cabang pusat">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                    </svg>
                                    Cabang
                                </button>
                            </form>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="py-3 px-4 text-center">
                        <span class="inline-flex items-center justify-center rounded-full text-xs font-semibold
                            {{ $store->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}"
                            style="min-width:72px; padding: 2px 0;">
                            {{ $store->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>

                    {{-- Aksi --}}
                    <td class="py-3 px-4 text-center">
                        <div class="flex justify-center items-center gap-2">

                            {{-- Edit --}}
                            <a href="{{ route('admin.stores.edit', $store) }}"
                               class="inline-flex items-center justify-center transition-all p-1"
                               style="color: #EF8F00;"
                               onmouseover="this.style.color='#cc7a00';"
                               onmouseout="this.style.color='#EF8F00';"
                               title="Edit toko">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>

                            {{-- Toggle status --}}
                            <form method="POST"
                                  action="{{ route('admin.stores.toggle-status', $store) }}"
                                  onsubmit="return confirm('{{ $store->is_active ? 'Nonaktifkan' : 'Aktifkan' }} toko \'{{ addslashes($store->name) }}\'?')">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center justify-center transition-all p-1"
                                        style="{{ $store->is_active ? 'color:#dc2626;' : 'color:#16a34a;' }} background:none; border:none;"
                                        onmouseover="this.style.color='{{ $store->is_active ? '#b91c1c' : '#15803d' }}';"
                                        onmouseout="this.style.color='{{ $store->is_active ? '#dc2626' : '#16a34a' }}';"
                                        title="{{ $store->is_active ? 'Nonaktifkan toko' : 'Aktifkan toko' }}">
                                    @if($store->is_active)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/>
                                            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                            <polyline points="22 4 12 14.01 9 11.01"/>
                                        </svg>
                                    @endif
                                </button>
                            </form>

                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="py-12 text-center text-gray-400">
                        <div class="flex justify-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-300"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                        </div>
                        <p>Belum ada toko terdaftar</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection