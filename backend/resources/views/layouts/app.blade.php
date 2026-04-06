<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DePOS') }} — @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-nav {
            flex: 1 1 0%;
            overflow-y: auto;
            min-height: 0;
        }
        .sidebar-nav::-webkit-scrollbar { width: 0; }
        .sidebar-nav { scrollbar-width: none; }
        .sidebar        { transition: width 0.25s cubic-bezier(0.4,0,0.2,1); }
        .main-content   { transition: padding-left 0.25s cubic-bezier(0.4,0,0.2,1); }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">

<div x-data="{ open: localStorage.getItem('sidebarOpen') === 'true' }"
     x-init="$watch('open', val => localStorage.setItem('sidebarOpen', val))"
     class="min-h-screen">

    {{-- SIDEBAR --}}
    <aside
        x-cloak
        :style="open ? 'width:240px' : 'width:64px'"
        class="sidebar fixed inset-y-0 left-0 z-50 flex flex-col overflow-visible"
        style="background-color: #ffffff; border-right: 1px solid #d1d5db; box-shadow: 2px 0 8px rgba(0,0,0,0.08);">

        {{-- Logo --}}
        <div class="h-14 flex items-center justify-between px-3 flex-shrink-0" style="border-bottom: 2px solid #e5e7eb;">
            <a href="{{ auth()->user()->dashboardRoute() }}" class="flex items-center gap-2.5 min-w-0 overflow-hidden">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0"
                     style="background-color: #181375; color: white;">D</div>
                <span x-show="open" class="font-bold text-base whitespace-nowrap overflow-hidden" style="color: #181375;">DePOS</span>
            </a>
            <button x-show="open" @click="open = false" class="p-1 rounded-md transition flex-shrink-0" style="color: #6b7280;"
                    onmouseover="this.style.color='#181375';" onmouseout="this.style.color='#6b7280';" title="Tutup sidebar">«</button>
            <button x-show="!open" @click="open = true" class="absolute z-50 font-bold"
                    style="color:#fff; background-color:#2D54BF; width:22px; height:22px; border-radius:6px; display:flex; align-items:center; justify-content:center; box-shadow:2px 2px 6px rgba(0,0,0,0.2); right:-11px; top:16px; font-size:14px; line-height:1; padding:0;"
                    onmouseover="this.style.backgroundColor='#1e3d8f';" onmouseout="this.style.backgroundColor='#2D54BF';" title="Buka sidebar">»</button>
        </div>

        {{-- NAV --}}
        <nav class="sidebar-nav py-2">
            @php $role = auth()->user()->role->slug; @endphp

            {{-- MANAGER --}}
            @if(in_array($role, ['admin', 'manager']))
                <div x-show="open" class="px-4 pt-3 pb-1">
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9ca3af;">Menu Utama</p>
                </div>
                <div x-show="!open" class="my-1 mx-2" style="border-top: 2px solid #e5e7eb;"></div>

                @foreach([
    ['manager.dashboard',        'manager.dashboard',    '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>', 'Dashboard'],
    ['manager.products.index',   'manager.products.*',   '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>', 'Menu'],
    ['manager.categories.index', 'manager.categories.*', '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 7h18M3 12h18M3 17h18"/></svg>', 'Kategori'],
    ['manager.tables.index', 'manager.tables.*', '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><rect x="2" y="3" width="20" height="3" rx="0.5"/><rect x="11" y="6" width="2" height="5"/><path d="M4 18a8 8 0 0 1 16 0H4z"/><path d="M7 18a5 5 0 0 1 10 0H7z" fill="{{ request()->routeIs(\'manager.tables.*\') ? \'#181375\' : \'white\' }}"/></svg>', 'Kelola Meja'],
    ['manager.reports.sales',    'manager.reports.*',    '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>', 'Laporan'],
] as [$r, $m, $icon, $label])
    @php $a = request()->routeIs($m); @endphp
    <a href="{{ route($r) }}" title=""
       x-data="{ hovered: false, tipY: 0 }"
       @mouseenter="if(!open){ hovered = true; tipY = $el.getBoundingClientRect().top + $el.getBoundingClientRect().height / 2 }"
       @mouseleave="hovered = false"
       class="relative flex items-center gap-3 mx-1 py-2 rounded-lg text-sm transition-all mb-0.5"
       :class="open ? 'px-3' : 'justify-center px-0'"
       style="{{ $a ? 'background-color: #181375; color: white; font-weight: 600;' : 'color: #374151;' }}"
       onmouseover="{{ $a ? '' : "this.style.backgroundColor='#181375'; this.style.color='white';" }}"
       onmouseout="{{ $a ? '' : "this.style.backgroundColor=''; this.style.color='#374151';" }}"
       onmousedown="{{ $a ? '' : "this.style.transform='scale(0.98)';" }}"
       onmouseup="{{ $a ? '' : "this.style.transform='scale(1)';" }}">
        <span class="w-6 flex-shrink-0 flex items-center justify-center">{!! $icon !!}</span>
        <span x-show="open" class="whitespace-nowrap truncate">{{ $label }}</span>
        <span x-show="hovered"
              x-transition:enter="transition ease-out duration-150"
              x-transition:enter-start="opacity-0"
              x-transition:enter-end="opacity-100"
              x-transition:leave="transition ease-in duration-100"
              x-transition:leave-start="opacity-100"
              x-transition:leave-end="opacity-0"
              class="pointer-events-none fixed z-[9999]"
              :style="'left: 72px; top: ' + tipY + 'px; transform: translateY(-50%);'">
            <span style="position: absolute; left: -6px; top: 50%; transform: translateY(-50%); width: 0; height: 0; border-top: 6px solid transparent; border-bottom: 6px solid transparent; border-right: 7px solid #181375;"></span>
            <span class="block px-3 py-1.5 text-sm font-medium rounded-lg whitespace-nowrap"
                  style="background: #181375; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                {{ $label }}
            </span>
        </span>
    </a>
@endforeach
            @endif

            {{-- KASIR --}}
            @if(in_array($role, ['admin', 'manager', 'cashier']))
                <div x-show="open" class="px-4 pt-4 pb-1">
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9ca3af;">Pemesanan</p>
                </div>
                <div x-show="!open" class="my-1 mx-2" style="border-top: 2px solid #e5e7eb;"></div>

                @foreach([
    ['cashier.orders.index',     'cashier.orders.*',    '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 12h6M9 16h6"/></svg>', 'Pesanan'],
    ['cashier.tables.index',     'cashier.tables.*',    '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3C7.03 3 3 7.03 3 12h18c0-4.97-4.03-9-9-9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 15h20"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 15v2"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 15v2"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 17h16"/></svg>', 'Dine-In'],
    ['cashier.payments.history', 'cashier.payments.*',  '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>', 'Pembayaran'],
] as [$r, $m, $icon, $label])
    @php $a = request()->routeIs($m) && !request()->routeIs('cashier.orders.create'); @endphp
    <a href="{{ route($r) }}" title=""
       x-data="{ hovered: false, tipY: 0 }"
       @mouseenter="if(!open){ hovered = true; tipY = $el.getBoundingClientRect().top + $el.getBoundingClientRect().height / 2 }"
       @mouseleave="hovered = false"
       class="relative flex items-center gap-3 mx-1 py-2 rounded-lg text-sm transition-all mb-0.5"
       :class="open ? 'px-3' : 'justify-center px-0'"
       style="{{ $a ? 'background-color: #181375; color: white; font-weight: 600;' : 'color: #374151;' }}"
       onmouseover="{{ $a ? '' : "this.style.backgroundColor='#181375'; this.style.color='white';" }}"
       onmouseout="{{ $a ? '' : "this.style.backgroundColor=''; this.style.color='#374151';" }}"
       onmousedown="{{ $a ? '' : "this.style.transform='scale(0.98)';" }}"
       onmouseup="{{ $a ? '' : "this.style.transform='scale(1)';" }}">
        <span class="w-6 flex-shrink-0 flex items-center justify-center">{!! $icon !!}</span>
        <span x-show="open" class="whitespace-nowrap truncate">{{ $label }}</span>
        <span x-show="hovered"
              x-transition:enter="transition ease-out duration-150"
              x-transition:enter-start="opacity-0"
              x-transition:enter-end="opacity-100"
              x-transition:leave="transition ease-in duration-100"
              x-transition:leave-start="opacity-100"
              x-transition:leave-end="opacity-0"
              class="pointer-events-none fixed z-[9999]"
              :style="'left: 72px; top: ' + tipY + 'px; transform: translateY(-50%);'">
            <span style="position: absolute; left: -6px; top: 50%; transform: translateY(-50%); width: 0; height: 0; border-top: 6px solid transparent; border-bottom: 6px solid transparent; border-right: 7px solid #181375;"></span>
            <span class="block px-3 py-1.5 text-sm font-medium rounded-lg whitespace-nowrap"
                  style="background: #181375; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                {{ $label }}
            </span>
        </span>
    </a>
@endforeach

{{-- Buat Pesanan --}}
@php $isCreate = request()->routeIs('cashier.orders.create'); @endphp
<a href="{{ route('cashier.orders.create') }}" title=""
   x-data="{ hovered: false, tipY: 0 }"
   @mouseenter="if(!open){ hovered = true; tipY = $el.getBoundingClientRect().top + $el.getBoundingClientRect().height / 2 }"
   @mouseleave="hovered = false"
   class="relative flex items-center gap-3 mx-1 py-2 rounded-lg text-sm transition-all mb-0.5"
   :class="open ? 'px-3' : 'justify-center px-0'"
   style="{{ $isCreate ? 'background-color: #181375; color: white; font-weight: 600;' : 'color: #374151;' }}"
   onmouseover="{{ $isCreate ? '' : "this.style.backgroundColor='#181375'; this.style.color='white';" }}"
   onmouseout="{{ $isCreate ? '' : "this.style.backgroundColor=''; this.style.color='#374151';" }}"
   onmousedown="{{ $isCreate ? '' : "this.style.transform='scale(0.98)';" }}"
   onmouseup="{{ $isCreate ? '' : "this.style.transform='scale(1)';" }}">
    <span class="w-6 flex-shrink-0 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
    </span>
    <span x-show="open" class="whitespace-nowrap truncate">Buat Pesanan</span>
    <span x-show="hovered"
          x-transition:enter="transition ease-out duration-150"
          x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100"
          x-transition:leave="transition ease-in duration-100"
          x-transition:leave-start="opacity-100"
          x-transition:leave-end="opacity-0"
          class="pointer-events-none fixed z-[9999]"
          :style="'left: 72px; top: ' + tipY + 'px; transform: translateY(-50%);'">
        <span style="position: absolute; left: -6px; top: 50%; transform: translateY(-50%); width: 0; height: 0; border-top: 6px solid transparent; border-bottom: 6px solid transparent; border-right: 7px solid #181375;"></span>
        <span class="block px-3 py-1.5 text-sm font-medium rounded-lg whitespace-nowrap"
              style="background: #181375; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
            Buat Pesanan
        </span>
    </span>
</a>
            @endif

            {{-- DAPUR --}}
            @if($role === 'kitchen_staff')
                <div x-show="open" class="px-4 pt-4 pb-1">
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9ca3af;">Dapur</p>
                </div>
                <div x-show="!open" class="my-1 mx-2" style="border-top: 2px solid #e5e7eb;"></div>
                @php $a = request()->routeIs('kitchen.*'); @endphp
<a href="{{ route('kitchen.display') }}"
   x-data="{ hovered: false, tipY: 0 }"
   @mouseenter="if(!open){ hovered = true; tipY = $el.getBoundingClientRect().top + $el.getBoundingClientRect().height / 2 }"
   @mouseleave="hovered = false"
   class="relative flex items-center gap-3 mx-1 py-2 rounded-lg text-sm transition-all mb-0.5"
   :class="open ? 'px-3' : 'justify-center px-0'"
   style="{{ $a ? 'background-color: #181375; color: white; font-weight: 600;' : 'color: #374151;' }}"
   onmouseover="{{ $a ? '' : "this.style.backgroundColor='#181375'; this.style.color='white';" }}"
   onmouseout="{{ $a ? '' : "this.style.backgroundColor=''; this.style.color='#374151';" }}"
   onmousedown="{{ $a ? '' : "this.style.transform='scale(0.98)';" }}"
   onmouseup="{{ $a ? '' : "this.style.transform='scale(1)';" }}">

    <span class="w-6 flex-shrink-0 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 28" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 2c0 1.5 1.5 1.5 1.5 3S8 6.5 8 8" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 1c0 1.5 1.5 1.5 1.5 3S12 5.5 12 7" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 2c0 1.5 1.5 1.5 1.5 3S16 6.5 16 8" />
            <rect x="4" y="12" width="16" height="8" rx="2" ry="2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M2 14h2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 14h2" />
        </svg>
    </span>

    <span x-show="open" class="whitespace-nowrap">Tampilan Dapur</span>

    {{-- Tooltip hanya saat sidebar tertutup --}}
    <span x-show="hovered"
          x-transition:enter="transition ease-out duration-150"
          x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100"
          x-transition:leave="transition ease-in duration-100"
          x-transition:leave-start="opacity-100"
          x-transition:leave-end="opacity-0"
          class="pointer-events-none fixed z-[9999]"
          :style="'left: 72px; top: ' + tipY + 'px; transform: translateY(-50%);'">
        <span style="position: absolute; left: -6px; top: 50%; transform: translateY(-50%);
                     width: 0; height: 0;
                     border-top: 6px solid transparent;
                     border-bottom: 6px solid transparent;
                     border-right: 7px solid #181375;">
        </span>
        <span class="block px-3 py-1.5 text-sm font-medium rounded-lg whitespace-nowrap"
              style="background: #181375; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
            Tampilan Dapur
        </span>
    </span>
</a>
            @endif

            {{-- ADMIN --}}
            @if($role === 'admin')
                <div x-show="open" class="px-4 pt-4 pb-1">
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9ca3af;">Akun</p>
                </div>
                <div x-show="!open" class="my-1 mx-2" style="border-top: 2px solid #e5e7eb;"></div>
                @php $a = request()->routeIs('admin.users.*'); @endphp
                <a href="{{ route('admin.users.index') }}" title="Users"
   x-data="{ hovered: false, tipY: 0 }"
   @mouseenter="if(!open){ hovered = true; tipY = $el.getBoundingClientRect().top + $el.getBoundingClientRect().height / 2 }"
   @mouseleave="hovered = false"
   class="relative flex items-center gap-3 mx-1 py-2 rounded-lg text-sm transition-all mb-0.5"
   :class="open ? 'px-3' : 'justify-center px-0'"
   style="{{ $a ? 'background-color: #181375; color: white; font-weight: 600;' : 'color: #374151;' }}"
   onmouseover="{{ $a ? '' : "this.style.backgroundColor='#181375'; this.style.color='white';" }}"
   onmouseout="{{ $a ? '' : "this.style.backgroundColor=''; this.style.color='#374151';" }}"
   onmousedown="{{ $a ? '' : "this.style.transform='scale(0.98)';" }}"
   onmouseup="{{ $a ? '' : "this.style.transform='scale(1)';" }}">
    <span class="w-6 flex-shrink-0 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
    </span>
    <span x-show="open" class="whitespace-nowrap">Users</span>
    <span x-show="hovered"
          x-transition:enter="transition ease-out duration-150"
          x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100"
          x-transition:leave="transition ease-in duration-100"
          x-transition:leave-start="opacity-100"
          x-transition:leave-end="opacity-0"
          class="pointer-events-none fixed z-[9999]"
          :style="'left: 72px; top: ' + tipY + 'px; transform: translateY(-50%);'">
        <span style="position: absolute; left: -6px; top: 50%; transform: translateY(-50%); width: 0; height: 0; border-top: 6px solid transparent; border-bottom: 6px solid transparent; border-right: 7px solid #181375;"></span>
        <span class="block px-3 py-1.5 text-sm font-medium rounded-lg whitespace-nowrap"
              style="background: #181375; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
            Users
        </span>
    </span>
</a>
            @endif

            <div class="h-4"></div>
        </nav>

        {{-- USER --}}
        <div class="flex-shrink-0 relative" style="border-top: 2px solid #e5e7eb;">
            <div class="p-3 relative" x-data="{ menuOpen: false }">
                <button @click="menuOpen = !menuOpen"
                    class="flex items-center gap-3 w-full rounded-lg p-2 transition text-left"
                    onmouseover="this.style.backgroundColor='#181375'; this.querySelector('.user-name').style.color='white'; this.querySelector('.user-role').style.color='#c7d2fe'; this.querySelector('.avatar-circle').style.backgroundColor='#2D54BF';"
                    onmouseout="this.style.backgroundColor=''; this.querySelector('.user-name').style.color='#1f2937'; this.querySelector('.user-role').style.color='#6b7280'; this.querySelector('.avatar-circle').style.backgroundColor='#181375';">
                    <div class="avatar-circle w-8 h-8 rounded-full text-white text-xs font-bold flex items-center justify-center flex-shrink-0 transition-all duration-200"
                        style="background-color: #181375;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <span x-show="open" class="flex-1 min-w-0">
                        <p class="user-name text-sm font-medium truncate" style="color: #1f2937;">{{ auth()->user()->name }}</p>
                        <p class="user-role text-xs capitalize" style="color: #6b7280;">{{ auth()->user()->role->name }}</p>
                    </span>
                </button>

                <div x-show="menuOpen"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     @click.outside="menuOpen = false"
                     class="absolute bottom-full left-2 right-2 mb-2 bg-white border border-gray-100 rounded-xl shadow-lg z-[9999]"
                     style="position: absolute; bottom: 100%; left: 8px; right: 8px; margin-bottom: 8px;">
                    <div class="px-4 pt-3 pb-1"></div>

                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors"
                       style="color: #374151;"
                       onmouseover="this.style.backgroundColor='#f3f4f6';"
                       onmouseout="this.style.backgroundColor='';"
                       onmousedown="this.style.backgroundColor='#e5e7eb';"
                       onmouseup="this.style.backgroundColor='#f3f4f6';">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span x-show="open">Profil Saya</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-3 w-full px-4 py-2.5 text-sm transition-colors"
                            style="color: #ef4444;"
                            onmouseover="this.style.backgroundColor='#fff1f2';"
                            onmouseout="this.style.backgroundColor='';"
                            onmousedown="this.style.backgroundColor='#fee2e2';"
                            onmouseup="this.style.backgroundColor='#fff1f2';">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span x-show="open">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <div class="main-content flex flex-col min-h-screen"
         :style="open ? 'padding-left:240px' : 'padding-left:64px'">

        {{-- Topbar --}}
        <header class="h-14 border-b flex items-center justify-between px-6 sticky top-0 z-40 flex-shrink-0" style="background-color: #181375;">
            <h2 class="text-base font-semibold text-white truncate">@yield('page-title', 'Dashboard')</h2>
            <span class="text-sm text-white whitespace-nowrap">{{ now()->format('l, d F Y') }}</span>
        </header>

        {{-- Flash messages --}}
        @if(session('success') || session('status') || session('error') || $errors->any())
    <div class="px-6 pt-4 space-y-2">
        @if((session('success') || session('status')) && !request()->routeIs('manager.categories.*'))
                    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm flex items-start gap-2.5">
                        {{-- check-circle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 mt-0.5 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        <span>{{ session('success') ?? session('status') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm flex items-start gap-2.5">
                        {{-- x-circle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 mt-0.5 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                        <p class="font-medium mb-1 flex items-center gap-2">
                            {{-- alert-triangle --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                            Terdapat kesalahan:
                        </p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        {{-- Content --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>

    </div>
</div>

@stack('scripts')
</body>
</html>