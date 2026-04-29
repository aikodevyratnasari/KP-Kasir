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

{{-- Global Order Notification --}}
@auth
    <script src="{{ asset('js/order-notif.js') }}?v={{ filemtime(public_path('js/order-notif.js')) }}"></script>
@endauth

<body class="font-sans antialiased bg-gray-100">

<div x-data="{ open: localStorage.getItem('sidebarOpen') === 'true' }"
     x-init="$watch('open', val => localStorage.setItem('sidebarOpen', val))"
     class="min-h-screen">

    {{-- SIDEBAR --}}
    <aside
        :style="(open ? 'width:240px' : 'width:64px') + ';background-color:#181375;border-right:none;box-shadow:2px 0 12px rgba(24,19,117,0.4);'"
        class="sidebar fixed inset-y-0 left-0 z-50 flex flex-col overflow-visible">

        {{-- Logo --}}
        
<div class="h-14 flex items-center px-3 flex-shrink-0 gap-3" style="border-bottom: 1px solid rgba(255,255,255,0.15);">
    
{{-- Logo D + tulisan DePOS (hanya saat terbuka) --}}
    <a x-show="open" href="{{ auth()->user()->dashboardRoute() }}" class="flex items-center gap-2 min-w-0 overflow-hidden">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0"
             style="background-color: rgba(255,255,255,0.2); color: white;">D</div>
        <span class="font-bold text-base whitespace-nowrap overflow-hidden" style="color: #ffffff;">DePOS</span>
    </a>
    
{{-- Hamburger button (selalu tampil, buat buka/tutup) --}}
    {{-- Hamburger button (selalu tampil) --}}
<button @click="open = !open"
        class="flex items-center justify-center flex-shrink-0 transition-all ml-auto"
        style="width:36px;height:36px;border-radius:8px;color:white;border:none;cursor:pointer;"
        onmouseover="this.style.backgroundColor='rgba(255,255,255,0.15)';"
        onmouseout="this.style.backgroundColor='transparent';">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    

</div>

        {{-- NAV --}}
        <nav class="sidebar-nav py-2">
            @php $role = auth()->user()->role->slug; @endphp

            {{-- MANAGER --}}
            @if(in_array($role, ['admin', 'manager']))
                <div x-show="open" class="px-4 pt-3 pb-1">
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color: rgba(255,255,255,0.5);">Menu Utama</p>
                </div>
                <div x-show="!open" class="my-1 mx-2" style="border-top: 1px solid rgba(255,255,255,0.15);"></div>

                @foreach([
    ['manager.dashboard',        'manager.dashboard',    '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>', 'Dashboard'],
    ['manager.products.index',   'manager.products.*',   '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>', 'Menu'],
    ['manager.categories.index', 'manager.categories.*', '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 7h18M3 12h18M3 17h18"/></svg>', 'Kategori'],
    ['manager.tables.index', 'manager.tables.*', '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><rect x="2" y="3" width="20" height="3" rx="0.5"/><rect x="11" y="6" width="2" height="5"/><path d="M4 18a8 8 0 0 1 16 0H4z"/><path d="M7 18a5 5 0 0 1 10 0H7z" fill="rgba(255,255,255,0.3)"/></svg>', 'Kelola Meja'],
    [
    'manager.settings.index',
    'manager.settings.*',
    '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="3"/>
        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 
        1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 
        2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 
        2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 
        0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 
        1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 
        2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
    </svg>',
    'Pengaturan'],
    ['manager.reports.index',    'manager.reports.*',    '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>', 'Laporan'],
] as [$r, $m, $icon, $label])
    @php $a = request()->routeIs($m); @endphp
    <a href="{{ route($r) }}" title=""
       x-data="{ hovered: false, tipY: 0 }"
       @mouseenter="if(!open){ hovered = true; tipY = $el.getBoundingClientRect().top + $el.getBoundingClientRect().height / 2 }"
       @mouseleave="hovered = false"
       class="relative flex items-center gap-3 mx-1 py-2 rounded-lg text-sm transition-all mb-0.5"
       :class="open ? 'px-3' : 'justify-center px-0'"
       style="{{ $a ? 'background-color: rgba(255,255,255,0.2); color: white; font-weight: 600;' : 'color: rgba(255,255,255,0.75);' }}"
       onmouseover="{{ $a ? '' : "this.style.backgroundColor='rgba(255,255,255,0.12)'; this.style.color='white';" }}"
       onmouseout="{{ $a ? '' : "this.style.backgroundColor=''; this.style.color='rgba(255,255,255,0.75)';" }}"
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
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color: rgba(255,255,255,0.5);">Pemesanan</p>
                </div>
                <div x-show="!open" class="my-1 mx-2" style="border-top: 1px solid rgba(255,255,255,0.15);"></div>

                @foreach([
    ['cashier.orders.index',     'cashier.orders.*',    '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 12h6M9 16h6"/></svg>', 'Pesanan'],
    ['cashier.tables.index',     'cashier.tables.*',    '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3C7.03 3 3 7.03 3 12h18c0-4.97-4.03-9-9-9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 15h20"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 15v2"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 15v2"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 17h16"/></svg>', 'Dine-In'],
    /*['cashier.payments.history', 'cashier.payments.*',  '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>', 'Pembayaran'],*/
] as [$r, $m, $icon, $label])
    @php $a = request()->routeIs($m) && !request()->routeIs('cashier.orders.create'); @endphp
    <a href="{{ route($r) }}" title=""
       x-data="{ hovered: false, tipY: 0 }"
       @mouseenter="if(!open){ hovered = true; tipY = $el.getBoundingClientRect().top + $el.getBoundingClientRect().height / 2 }"
       @mouseleave="hovered = false"
       class="relative flex items-center gap-3 mx-1 py-2 rounded-lg text-sm transition-all mb-0.5"
       :class="open ? 'px-3' : 'justify-center px-0'"
       style="{{ $a ? 'background-color: rgba(255,255,255,0.2); color: white; font-weight: 600;' : 'color: rgba(255,255,255,0.75);' }}"
       onmouseover="{{ $a ? '' : "this.style.backgroundColor='rgba(255,255,255,0.12)'; this.style.color='white';" }}"
       onmouseout="{{ $a ? '' : "this.style.backgroundColor=''; this.style.color='rgba(255,255,255,0.75)';" }}"
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
   style="{{ $isCreate ? 'background-color: rgba(255,255,255,0.2); color: white; font-weight: 600;' : 'color: rgba(255,255,255,0.75);' }}"
   onmouseover="{{ $isCreate ? '' : "this.style.backgroundColor='rgba(255,255,255,0.12)'; this.style.color='white';" }}"
   onmouseout="{{ $isCreate ? '' : "this.style.backgroundColor=''; this.style.color='rgba(255,255,255,0.75)';" }}"
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
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color: rgba(255,255,255,0.5);">Dapur</p>
                </div>
                <div x-show="!open" class="my-1 mx-2" style="border-top: 1px solid rgba(255,255,255,0.15);"></div>
                @php $a = request()->routeIs('kitchen.*'); @endphp
<a href="{{ route('kitchen.display') }}"
   x-data="{ hovered: false, tipY: 0 }"
   @mouseenter="if(!open){ hovered = true; tipY = $el.getBoundingClientRect().top + $el.getBoundingClientRect().height / 2 }"
   @mouseleave="hovered = false"
   class="relative flex items-center gap-3 mx-1 py-2 rounded-lg text-sm transition-all mb-0.5"
   :class="open ? 'px-3' : 'justify-center px-0'"
   style="{{ $a ? 'background-color: rgba(255,255,255,0.2); color: white; font-weight: 600;' : 'color: rgba(255,255,255,0.75);' }}"
   onmouseover="{{ $a ? '' : "this.style.backgroundColor='rgba(255,255,255,0.12)'; this.style.color='white';" }}"
   onmouseout="{{ $a ? '' : "this.style.backgroundColor=''; this.style.color='rgba(255,255,255,0.75)';" }}"
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
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color: rgba(255,255,255,0.5);">Akun</p>
                </div>
                <div x-show="!open" class="my-1 mx-2" style="border-top: 1px solid rgba(255,255,255,0.15);"></div>
                @php $a = request()->routeIs('admin.users.*'); @endphp
                <a href="{{ route('admin.users.index') }}" title="Users"
   x-data="{ hovered: false, tipY: 0 }"
   @mouseenter="if(!open){ hovered = true; tipY = $el.getBoundingClientRect().top + $el.getBoundingClientRect().height / 2 }"
   @mouseleave="hovered = false"
   class="relative flex items-center gap-3 mx-1 py-2 rounded-lg text-sm transition-all mb-0.5"
   :class="open ? 'px-3' : 'justify-center px-0'"
   style="{{ $a ? 'background-color: rgba(255,255,255,0.2); color: white; font-weight: 600;' : 'color: rgba(255,255,255,0.75);' }}"
   onmouseover="{{ $a ? '' : "this.style.backgroundColor='rgba(255,255,255,0.12)'; this.style.color='white';" }}"
   onmouseout="{{ $a ? '' : "this.style.backgroundColor=''; this.style.color='rgba(255,255,255,0.75)';" }}"
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
        <div class="flex-shrink-0 relative" style="border-top: 1px solid rgba(255,255,255,0.15);">
            <div class="p-3 relative" x-data="{ menuOpen: false }">
                <button @click="menuOpen = !menuOpen"
    class="flex items-center gap-3 w-full rounded-lg transition text-left"
    :class="open ? 'p-2' : 'justify-center p-2'"
    onmouseover="this.style.backgroundColor='rgba(255,255,255,0.12)';"
    onmouseout="this.style.backgroundColor='';">
    <div class="avatar-circle w-8 h-8 rounded-full text-white text-xs font-bold flex items-center justify-center flex-shrink-0 transition-all duration-200"
        style="background-color: rgba(255,255,255,0.2);">
        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
    </div>
    <span x-show="open" class="flex-1 min-w-0">
        <p class="user-name text-sm font-medium truncate" style="color: #ffffff;">{{ auth()->user()->name }}</p>
        <p class="user-role text-xs capitalize" style="color: rgba(255,255,255,0.6);">{{ auth()->user()->role->name }}</p>
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
        <header class="h-14 border-b flex items-center justify-between px-6 sticky top-0 z-40 flex-shrink-0" style="background-color: #000000;">
            <h2 class="text-base font-semibold text-white truncate">@yield('page-title', 'Dashboard')</h2>
            <span class="text-sm text-white whitespace-nowrap">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</span>
        </header>

        {{-- Flash messages --}}
        @if(session('success') || session('status') || session('error') || $errors->any())
    <div class="px-6 pt-4 space-y-2 flex flex-col items-center">
        @if((session('success') || session('status')) && !request()->routeIs('manager.categories.*'))
                    <div id="flash-success" class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm flex items-start gap-2.5">
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
<div x-data="{ show: true }" x-show="show"
     class="fixed inset-0 z-[99999] flex items-center justify-center"
     style="background-color: rgba(0,0,0,0.5);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xs mx-4">
        {{-- Isi --}}
        <div class="px-6 py-8 flex flex-col items-center text-center">
            {{-- Icon X bulat merah --}}
            <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4" style="background-color: #e53e3e;">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9" viewBox="0 0 24 24" fill="white">
        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
    </svg>
</div>
            {{-- Pesan --}}
           <p class="text-sm text-gray-600 mb-6">Mohon lengkapi semua field yang wajib diisi dengan benar.</p>
            {{-- Button Close --}}
            <button @click="show = false"
                    class="w-full py-2.5 text-sm font-bold text-white rounded-lg transition-colors"
                    style="background-color: #e53e3e;"
                    onmouseover="this.style.backgroundColor='#c53030';"
                    onmouseout="this.style.backgroundColor='#e53e3e';"
                    onmousedown="this.style.transform='scale(0.98)';"
                    onmouseup="this.style.transform='scale(1)';">
                CLOSE
            </button>
        </div>
    </div>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const flash = document.getElementById('flash-success');
        if (flash) {
            setTimeout(() => {
                flash.style.transition = 'opacity 0.5s';
                flash.style.opacity = '0';
                setTimeout(() => flash.remove(), 500);
            }, 1000);
        }
    });
</script>
</body>
</html>