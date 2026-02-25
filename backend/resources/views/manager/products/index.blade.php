@extends('layouts.app')
@section('title', 'Manajemen Menu')
@section('page-title', 'Menu & Produk')

@section('content')
<div class="mp-wrapper">

    {{-- CARD 1: Filter + Tombol Aksi --}}
    <div class="mp-card-filter">

        <form method="GET" class="mp-filter-row">

            <div class="mp-field">
                <label class="mp-label">Cari Produk</label>
                <div class="mp-input-wrap">
                    <span class="mp-input-icon">🔍</span>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Nama produk...."
                           class="mp-input mp-input-search">
                </div>
            </div>

            <div class="mp-field">
                <label class="mp-label">Kategori</label>
                <select name="category" class="mp-input mp-select">
                    <option value="">Semua</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mp-field">
                <label class="mp-label">Urutkan</label>
                <select name="sort" class="mp-input mp-select">
                    <option value="">Terbaru</option>
                    <option value="name"  {{ request('sort')==='name'  ? 'selected':'' }}>Nama A–Z</option>
                    <option value="price" {{ request('sort')==='price' ? 'selected':'' }}>Harga ↑</option>
                </select>
            </div>

            <div class="mp-field">
                <label class="mp-label">Tersedia</label>
                <select name="available" class="mp-input mp-select">
                    <option value="">Semua</option>
                    <option value="1" {{ request('available')==='1' ? 'selected':'' }}>✓ Tersedia</option>
                    <option value="0" {{ request('available')==='0' ? 'selected':'' }}>✗ Tidak</option>
                </select>
            </div>

            <div class="mp-field">
                <label class="mp-label">&nbsp;</label>
                <div class="mp-filter-btns">
                    <button type="submit" class="mp-btn mp-btn-green">Filter</button>
                    <a href="{{ route('manager.products.index') }}" class="mp-btn mp-btn-ghost">↺ Reset</a>
                    <a href="{{ route('manager.products.create') }}" class="mp-btn mp-btn-green">+ Tambah Produk</a>
                    <a href="{{ route('manager.products.trashed') }}" class="mp-btn mp-btn-ghost">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="3 6 5 6 21 6"/>
        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
        <path d="M10 11v6M14 11v6"/>
        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
    </svg>
</a>
                </div>
            </div>

        </form>
    </div>

    {{-- CARD 2: Tabel Produk --}}
    <div class="mp-card-table">

        <div class="mp-table-header">
            <span class="mp-table-title">Daftar Produk</span>
            <span class="mp-count-pill">{{ $products->total() }} produk</span>
        </div>

        <div style="overflow-x:auto;">
            <table class="mp-table">
                <thead class="mp-thead">
                    <tr>
                        <th class="mp-th" style="text-align:left; padding-left:22px; width:36%;">Produk</th>
                        <th class="mp-th" style="text-align:left;">Kategori</th>
                        <th class="mp-th" style="text-align:right;">Harga</th>
                        <th class="mp-th" style="text-align:center;">Stok</th>
                        <th class="mp-th" style="text-align:center;">Tersedia</th>
                        <th class="mp-th" style="text-align:right; padding-right:22px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>

                @forelse($products as $product)
                    <tr class="mp-tr">

                        {{-- PRODUK --}}
                        <td class="mp-td" style="padding-left:22px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                @if($product->image)
                                    <img src="{{ Storage::url($product->image) }}"
                                         alt="{{ $product->name }}"
                                         class="mp-product-img"
                                         onclick="showImagePreview('{{ Storage::url($product->image) }}','{{ addslashes($product->name) }}')">
                                @else
                                    <div class="mp-product-placeholder">🍽️</div>
                                @endif
                                <div style="min-width:0;">
                                    <p class="mp-product-name" style="max-width:190px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                        {{ $product->name }}
                                    </p>
                                    @if($product->description)
                                        <span class="mp-product-desc" style="max-width:190px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:block;">
                                            {{ Str::limit($product->description, 48) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- KATEGORI --}}
                        <td class="mp-td">
                            <span class="mp-badge-cat">{{ $product->category?->name ?? '—' }}</span>
                        </td>

                        {{-- HARGA --}}
                        <td class="mp-td" style="text-align:right;">
                            <span class="mp-price">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                        </td>

                        {{-- STOK --}}
                        <td class="mp-td" style="text-align:center;">
                            @if($product->track_stock)
                                @php $isLow = $product->stock <= ($product->low_stock_alert ?? 5); @endphp
                                <span class="{{ $isLow ? 'mp-stock-low' : 'mp-stock-ok' }}">{{ $product->stock }}</span>
                                @if($isLow)<span class="mp-stock-warn">⚠ Menipis</span>@endif
                            @else
                                <span style="color:#CBD5E1;">—</span>
                            @endif
                        </td>

                        {{-- TERSEDIA --}}
                        <td class="mp-td" style="text-align:center;">
                            <span class="{{ $product->is_available ? 'mp-badge-yes' : 'mp-badge-no' }}">
                                {{ $product->is_available ? '✓ Ya' : '✗ Tidak' }}
                            </span>
                        </td>

                        {{-- AKSI --}}
                        <td class="mp-td" style="text-align:right; padding-right:22px;">
                            <div style="display:flex; justify-content:flex-end; align-items:center; gap:8px;">
                                <a href="{{ route('manager.products.edit', $product) }}" class="mp-btn-edit">
                                    ✏️ Edit
                                </a>
                                <button type="button"
                                        onclick="confirmDelete('{{ route('manager.products.destroy', $product) }}','{{ addslashes($product->name) }}')"
                                        class="mp-btn-del">
                                    🗑️ Hapus
                                </button>
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:72px 20px; text-align:center;">
                            <div style="font-size:52px; margin-bottom:12px;">🍽️</div>
                            <p style="font-weight:700; color:#1E293B; font-size:16px; margin:0 0 6px;">Belum ada produk</p>
                            <p style="color:#94A3B8; font-size:13px; margin:0 0 20px;">Mulai tambahkan menu restoran kamu</p>
                            <a href="{{ route('manager.products.create') }}" class="mp-btn mp-btn-green">+ Tambah Produk Pertama</a>
                        </td>
                    </tr>
                @endforelse

                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div style="padding:14px 22px; border-top:1px solid #F1F5F9; background:#FAFBFF;">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>


{{-- Modal Preview Gambar --}}
<div id="imageModal" class="mp-modal-overlay" onclick="closeImagePreview()">
    <div class="mp-modal-box" style="max-width:440px; padding:10px;" onclick="event.stopPropagation()">
        <img id="modalImage" src="" alt=""
             style="width:100%; border-radius:14px; object-fit:contain; max-height:65vh; display:block;">
        <div style="padding:12px 16px; display:flex; justify-content:space-between; align-items:center;">
            <p id="modalCaption" style="font-weight:600; font-size:14px; color:#1E293B; margin:0;"></p>
            <button onclick="closeImagePreview()"
                    style="background:none;border:none;font-size:26px;cursor:pointer;color:#94A3B8;line-height:1;">×</button>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div id="deleteModal" class="mp-modal-overlay">
    <div class="mp-modal-box" style="max-width:380px; padding:36px; text-align:center;">
        <div style="width:64px;height:64px;background:#FEE2E2;border-radius:18px;
                    display:flex;align-items:center;justify-content:center;
                    font-size:30px;margin:0 auto 16px;">🗑️</div>
        <h3 style="font-size:18px;font-weight:700;color:#111827;margin:0 0 8px;">Hapus Produk?</h3>
        <p style="font-size:13.5px;color:#6B7280;margin:0 0 24px;line-height:1.6;">
            "<span id="deleteProductName" style="font-weight:600;color:#374151;"></span>"
            akan dipindahkan ke sampah.
        </p>
        <div style="display:flex;gap:12px;">
            <button onclick="closeDeleteModal()" class="btn-secondary" style="flex:1;">Batal</button>
            <form id="deleteForm" method="POST" style="flex:1;">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger" style="width:100%;justify-content:center;">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showImagePreview(src, name) {
        document.getElementById('modalImage').src = src;
        document.getElementById('modalCaption').textContent = name;
        document.getElementById('imageModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeImagePreview() {
        document.getElementById('imageModal').classList.remove('active');
        document.body.style.overflow = '';
    }
    function confirmDelete(action, name) {
        document.getElementById('deleteForm').action = action;
        document.getElementById('deleteProductName').textContent = name;
        document.getElementById('deleteModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
        document.body.style.overflow = '';
    }
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') { closeImagePreview(); closeDeleteModal(); }
    });
</script>
@endpush
@endsection