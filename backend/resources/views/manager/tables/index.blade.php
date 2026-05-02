@extends('layouts.app')
@section('title', 'Manajemen Meja')
@section('page-title', 'Manajemen Meja')

@section('content')
<div class="space-y-5">

    {{-- Ringkasan --}}
    <div class="flex items-center justify-between">
    <p class="text-sm text-gray-500">{{ $tables->count() }} meja terdaftar</p>
    <div class="flex gap-2">
        <button onclick="document.getElementById('modal-bulk').classList.remove('hidden')"
            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
            style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
            onmouseover="this.style.backgroundColor='#f3f4f6';"
            onmouseout="this.style.backgroundColor='white';">
            Tambah Massal
        </button>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
            style="background-color: #2D54BF; border: 1px solid #2D54BF;"
            onmouseover="this.style.backgroundColor='#1e3d8f'"
            onmouseout="this.style.backgroundColor='#2D54BF'">
            Tambah Meja
        </button>
    </div>
</div>

    {{-- Kelompokkan per section --}}
    @foreach($tables->groupBy('section') as $section => $sectionTables)
        <div class="card p-0 overflow-hidden">
            <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-200">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                    {{ $section ?: 'Umum' }}
                </h3>
            </div>
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100">
                    <tr>
                        <th class="py-2.5 px-4 text-left text-xs font-semibold text-gray-500">Nomor Meja</th>
                        <th class="py-2.5 px-4 text-center text-xs font-semibold text-gray-500">Kapasitas</th>
                        <th class="py-2.5 px-4 text-center text-xs font-semibold text-gray-500">Status</th>
                        <th class="py-2.5 px-4 text-center text-xs font-semibold text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($sectionTables->sortBy('number') as $table)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2.5 px-4 font-medium text-gray-900">{{ $table->number }}</td>
                            <td class="py-2.5 px-4 text-center text-gray-600">{{ $table->capacity }} kursi</td>
                            <td class="py-2.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                    @switch($table->status)
                                        @case('available') bg-green-100 text-green-700 @break
                                        @case('occupied')  bg-red-100 text-red-700 @break
                                        @case('reserved')  bg-yellow-100 text-yellow-700 @break
                                        @default           bg-gray-100 text-gray-500
                                    @endswitch">
                                    @switch($table->status)
                                        @case('available') Tersedia @break
                                        @case('occupied')  Terisi @break
                                        @case('reserved')  Reservasi @break
                                        @default           Ditutup
                                    @endswitch
                                </span>
                            </td>
                            <td class="py-2.5 px-4 text-center">
    <div class="flex justify-center items-center gap-2">
                                    <button onclick="openEdit({{ $table->id }}, '{{ $table->number }}', {{ $table->capacity }}, '{{ $table->section }}', '{{ $table->status }}')"
                                            class="inline-flex items-center justify-center transition-all p-1"
                                            style="color: #EF8F00;"
                                            onmouseover="this.style.color='#cc7a00';"
                                            onmouseout="this.style.color='#EF8F00';"
                                            title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </button>
                                    @if($table->status === 'available')
                                        <form method="POST" action="{{ route('manager.tables.destroy', $table) }}"
                                              onsubmit="return confirm('Hapus meja {{ $table->number }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center justify-center transition-all p-1"
                                                    style="color: #dc2626; background: none; border: none;"
                                                    onmouseover="this.style.color='#b91c1c';"
                                                    onmouseout="this.style.color='#dc2626';"
                                                    title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"/>
                                                    <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                                    <path d="M10 11v6M14 11v6"/>
                                                    <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center justify-center p-1 cursor-not-allowed"
                                              style="color: #d1d5db;"
                                              title="Tidak bisa dihapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                                <path d="M10 11v6M14 11v6"/>
                                                <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    @if($tables->isEmpty())
        <div class="card text-center py-12 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18M10 4v16M14 4v16"/>
            </svg>
            <p>Belum ada meja. Tambahkan meja pertama.</p>
        </div>
    @endif
</div>

{{-- ══ MODAL: Tambah Satu Meja ══ --}}
<div id="modal-add" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-semibold text-gray-800">Tambah Meja</h3>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('manager.tables.store') }}">
            @csrf
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nomor Meja <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="number" placeholder="mis. A01, VIP-1"
                               class="form-input @error('number') border-red-400 @enderror" required>
                        @error('number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jumlah Kursi <span class="text-red-500">*</span>
                        </label>
                        {{-- Input bebas, bukan dropdown --}}
                        <input type="number" name="capacity" placeholder="mis. 4"
                               min="1" max="100" value="{{ old('capacity', 4) }}"
                               class="form-input @error('capacity') border-red-400 @enderror" required>
                        @error('capacity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Seksi / Area</label>
                    {{-- Shortcut pilih seksi yang sudah ada --}}
                    <div class="flex gap-2 flex-wrap mb-2">
                        @foreach($sections as $sec)
                        <button type="button"
                            onclick="
                                document.getElementById('add-section').value = '{{ $sec }}';
                                this.parentElement.querySelectorAll('button').forEach(b => {
                                    b.style.backgroundColor = 'white';
                                    b.style.color = '#374151';
                                    b.style.borderColor = '#d1d5db';
                                });
                                this.style.backgroundColor = '#1e3d8f';
                                this.style.color = 'white';
                                this.style.borderColor = '#1e3d8f';"
                            class="edit-section-btn px-3 py-1 text-xs rounded-full border transition"
                            style="border-color: #d1d5db; color: #374151; background-color: white;">
                        {{ $sec }}
                    </button>
                    @endforeach
                    </div>
                    <input type="text" name="section" id="add-section"
                           placeholder="atau ketik area baru..."
                           class="form-input">
                </div>
            </div>
            <div class="flex justify-between mt-6">
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
    class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
    style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
    onmouseover="this.style.backgroundColor='#f3f4f6';"
    onmouseout="this.style.backgroundColor='white';">
    Batal
</button>
    <button type="submit"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
        style="background-color: #2D54BF; border: 1px solid #2D54BF;"
        onmouseover="this.style.backgroundColor='#1e3d8f'"
        onmouseout="this.style.backgroundColor='#2D54BF'">
        Simpan
    </button>
</div>
        </form>
    </div>
</div>

{{-- ══ MODAL: Tambah Massal ══ --}}
<div id="modal-bulk" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
        <div class="flex items-center justify-between mb-2">
            <h3 class="font-semibold text-gray-800">Tambah Meja Massal</h3>
            <button onclick="document.getElementById('modal-bulk').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <p class="text-xs text-gray-500 mb-5">
            Buat banyak meja sekaligus dengan awalan dan nomor urut.
            Contoh: awalan <strong>A</strong>, nomor <strong>1–6</strong>, kapasitas <strong>4</strong>
            → akan membuat <strong>A01, A02, A03, A04, A05, A06</strong>.
        </p>

        <form method="POST" action="{{ route('manager.tables.bulk') }}">
            @csrf
            <div class="space-y-4">
                {{-- Seksi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Seksi / Area</label>
                    <div class="flex gap-2 flex-wrap mb-2">
                        @foreach($sections as $sec)
                            <button type="button"
    onclick="
        document.getElementById('bulk-section').value = '{{ $sec }}';
        this.parentElement.querySelectorAll('button').forEach(b => {
            b.style.backgroundColor = 'white';
            b.style.color = '#374151';
            b.style.borderColor = '#d1d5db';
        });
        this.style.backgroundColor = '#1e3d8f';
        this.style.color = 'white';
        this.style.borderColor = '#1e3d8f';"
    class="add-section-btn px-3 py-1 text-xs rounded-full border transition"
    style="border-color: #d1d5db; color: #374151; background-color: white;">
{{ $sec }}
</button>
                        @endforeach
                    </div>
                    <input type="text" name="section" id="bulk-section"
                           placeholder="atau ketik area baru..."
                           class="form-input" value="{{ old('section') }}">
                    @error('section', 'bulk') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Awalan + Nomor + Kapasitas --}}
                <div class="grid grid-cols-4 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Awalan</label>
                        <input type="text" name="prefix" id="bulk-prefix"
                               placeholder="A" maxlength="5"
                               value="{{ old('prefix') }}"
                               oninput="updateBulkPreview()"
                               class="form-input text-center font-mono uppercase">
                        <p class="mt-1 text-xs text-gray-400">Opsional</p>
                        @error('prefix', 'bulk') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dari <span class="text-red-500">*</span></label>
                        <input type="number" name="start" id="bulk-start"
                               min="1" max="999" value="{{ old('start', 1) }}"
                               oninput="updateBulkPreview()"
                               class="form-input text-center" required>
                        @error('start', 'bulk') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sampai <span class="text-red-500">*</span></label>
                        <input type="number" name="end" id="bulk-end"
                               min="1" max="999" value="{{ old('end', 6) }}"
                               oninput="updateBulkPreview()"
                               class="form-input text-center" required>
                        @error('end', 'bulk') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kursi <span class="text-red-500">*</span></label>
                        <input type="number" name="capacity" id="bulk-capacity"
                               min="1" max="100" value="{{ old('capacity', 4) }}"
                               class="form-input text-center" required>
                        @error('capacity', 'bulk') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Preview --}}
                <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-200">
                    <p class="text-xs font-semibold text-gray-500 mb-2">Preview meja yang akan dibuat:</p>
                    <div id="bulk-preview" class="flex flex-wrap gap-1.5 text-xs font-mono text-indigo-700">
                        {{-- diisi oleh JS --}}
                    </div>
                    <p id="bulk-count" class="text-xs text-gray-400 mt-2"></p>
                </div>
            </div>

            <div class="flex justify-between mt-6">
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
    class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
    style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
    onmouseover="this.style.backgroundColor='#f3f4f6';"
    onmouseout="this.style.backgroundColor='white';">
    Batal
</button>
    <button type="submit"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
        style="background-color: #2D54BF; border: 1px solid #2D54BF;"
        onmouseover="this.style.backgroundColor='#1e3d8f'"
        onmouseout="this.style.backgroundColor='#2D54BF'">
        Buat Semua Meja
    </button>
</div>
        </form>
    </div>
</div>

{{-- ══ MODAL: Edit Meja ══ --}}
<div id="modal-edit" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-semibold text-gray-800">Edit Meja</h3>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form id="form-edit" method="POST" action="">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Meja <span class="text-red-500">*</span></label>
                        <input type="text" name="number" id="edit-number" class="form-input" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Kursi <span class="text-red-500">*</span></label>
                        {{-- Input bebas --}}
                        <input type="number" name="capacity" id="edit-capacity"
                               min="1" max="100" class="form-input" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Seksi / Area</label>
                    <div class="flex gap-2 flex-wrap mb-2">
                        @foreach($sections as $sec)
                            <button type="button"
        onclick="
            document.getElementById('edit-section').value = '{{ $sec }}';
            this.parentElement.querySelectorAll('button').forEach(b => {
                b.style.backgroundColor = 'white';
                b.style.color = '#374151';
                b.style.borderColor = '#d1d5db';
            });
            this.style.backgroundColor = '#1e3d8f';
            this.style.color = 'white';
            this.style.borderColor = '#1e3d8f';"
        class="px-3 py-1 text-xs rounded-full border transition"
        style="border-color: #d1d5db; color: #374151; background-color: white;">
    {{ $sec }}
</button>
                        @endforeach
                    </div>
                    <input type="text" name="section" id="edit-section" class="form-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="edit-status" class="form-input">
                        <option value="available">Tersedia</option>
                        <option value="closed">Ditutup</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-between mt-6">
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
    class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors"
    style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
    onmouseover="this.style.backgroundColor='#f3f4f6';"
    onmouseout="this.style.backgroundColor='white';">
    Batal
</button>
<button type="submit" 
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                    style="background-color: #2D54BF"
                    onmouseover="this.style.backgroundColor='#1e3d8f'"
                    onmouseout="this.style.backgroundColor='#2D54BF'">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Buka modal edit dan isi form ──
function openEdit(id, number, capacity, section, status) {
    document.getElementById('form-edit').action = `/manager/tables/${id}`;
    document.getElementById('edit-number').value   = number;
    document.getElementById('edit-capacity').value = capacity;
    document.getElementById('edit-section').value  = section;
    document.getElementById('edit-status').value   = status;

    // Highlight tombol seksi yang sesuai
    document.querySelectorAll('.edit-section-btn').forEach(btn => {
        btn.classList.remove('bg-indigo-600', 'text-white');
        btn.classList.add('border-gray-300', 'text-gray-600');
        if (btn.textContent.trim() === section) {
            btn.classList.add('bg-indigo-600', 'text-white');
            btn.classList.remove('border-gray-300', 'text-gray-600');
        }
    });

    document.getElementById('modal-edit').classList.remove('hidden');
}

// ── Preview meja yang akan dibuat (bulk) ──
function updateBulkPreview() {
    const prefix  = (document.getElementById('bulk-prefix').value || '').toUpperCase();
    const start   = parseInt(document.getElementById('bulk-start').value)  || 1;
    const end     = parseInt(document.getElementById('bulk-end').value)    || 1;
    const preview = document.getElementById('bulk-preview');
    const count   = document.getElementById('bulk-count');

    if (end < start || end - start > 49) {
        preview.innerHTML = '<span class="text-red-500">Rentang tidak valid (maks 50 meja)</span>';
        count.textContent = '';
        return;
    }

    const items = [];
    for (let i = start; i <= end; i++) {
        const num = prefix + String(i).padStart(2, '0');
        items.push(`<span class="bg-indigo-100 text-indigo-700 rounded px-1.5 py-0.5">${num}</span>`);
    }
    preview.innerHTML = items.join('');
    count.textContent = `${items.length} meja akan dibuat`;
}

// ── Tutup modal saat klik di luar ──
['modal-add', 'modal-bulk', 'modal-edit'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
});

// Inisialisasi preview saat halaman load
updateBulkPreview();
</script>
@endsection