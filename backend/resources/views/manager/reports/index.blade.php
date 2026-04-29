@extends('layouts.app')
@section('title', 'Laporan')
@section('page-title', 'Laporan')

@section('content')
<div class="space-y-6">

    <div>
        <p class="text-sm text-gray-500">Pilih jenis laporan yang ingin ditampilkan</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- Penjualan --}}
        <a href="{{ route('manager.reports.sales') }}"
           class="card group hover:shadow-md transition-all hover:-translate-y-0.5 cursor-pointer no-underline"
           style="border:1.5px solid #e5e7eb; text-decoration:none;"
           onmouseover="this.style.borderColor='#6366f1';"
           onmouseout="this.style.borderColor='#e5e7eb';">
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-full bg-indigo-50 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Laporan Penjualan</p>
                    <p class="text-sm text-gray-500 mt-0.5">Total penjualan, metode pembayaran, tren harian, dan tipe pesanan.</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-300 ml-auto flex-shrink-0 mt-1 group-hover:text-indigo-400 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
        </a>

        {{-- Produk --}}
        <a href="{{ route('manager.reports.products') }}"
           class="card group hover:shadow-md transition-all hover:-translate-y-0.5 cursor-pointer no-underline"
           style="border:1.5px solid #e5e7eb; text-decoration:none;"
           onmouseover="this.style.borderColor='#6366f1';"
           onmouseout="this.style.borderColor='#e5e7eb';">
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-full bg-orange-50 flex items-center justify-center flex-shrink-0">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
        <line x1="3" y1="6" x2="21" y2="6"/>
        <path d="M16 10a4 4 0 0 1-8 0"/>
    </svg>
</div>
                <div>
                    <p class="font-semibold text-gray-900">Laporan Produk</p>
                    <p class="text-sm text-gray-500 mt-0.5">Produk terlaris, top revenue, dan performa per kategori.</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-300 ml-auto flex-shrink-0 mt-1 group-hover:text-indigo-400 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
        </a>

        {{-- Revenue --}}
        <a href="{{ route('manager.reports.revenue') }}"
           class="card group hover:shadow-md transition-all hover:-translate-y-0.5 cursor-pointer no-underline"
           style="border:1.5px solid #e5e7eb; text-decoration:none;"
           onmouseover="this.style.borderColor='#6366f1';"
           onmouseout="this.style.borderColor='#e5e7eb';">
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Analitik Revenue</p>
                    <p class="text-sm text-gray-500 mt-0.5">Analisis revenue per hari, minggu, bulan, atau tahun.</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-300 ml-auto flex-shrink-0 mt-1 group-hover:text-indigo-400 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
        </a>

        {{-- Kasir --}}
        <a href="{{ route('manager.reports.cashiers') }}"
           class="card group hover:shadow-md transition-all hover:-translate-y-0.5 cursor-pointer no-underline"
           style="border:1.5px solid #e5e7eb; text-decoration:none;"
           onmouseover="this.style.borderColor='#6366f1';"
           onmouseout="this.style.borderColor='#e5e7eb';">
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Laporan Kasir</p>
                    <p class="text-sm text-gray-500 mt-0.5">Performa setiap kasir, jumlah transaksi, dan rata-rata nilai pesanan.</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-300 ml-auto flex-shrink-0 mt-1 group-hover:text-indigo-400 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
        </a>

        {{-- Pembayaran --}}
        <a href="{{ route('manager.reports.payments') }}"
        class="card group hover:shadow-md transition-all hover:-translate-y-0.5 cursor-pointer no-underline"
        style="border:1.5px solid #e5e7eb; text-decoration:none;"
        onmouseover="this.style.borderColor='#6366f1';"
        onmouseout="this.style.borderColor='#e5e7eb';">
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-full bg-purple-50 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Laporan Pembayaran</p>
                    <p class="text-sm text-gray-500 mt-0.5">Riwayat semua transaksi pembayaran, metode, dan status.</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-300 ml-auto flex-shrink-0 mt-1 group-hover:text-indigo-400 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
        </a>

    </div>
</div>
@endsection