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

<div x-data="{ open: true }" class="min-h-screen">

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
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9ca3af;">Manager</p>
                </div>
                <div x-show="!open" class="my-1 mx-2" style="border-top: 2px solid #e5e7eb;"></div>

                @foreach([
    ['manager.dashboard',        'manager.dashboard',    '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>', 'Dashboard'],
    ['manager.products.index',   'manager.products.*',   '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>', 'Menu'],
    ['manager.categories.index', 'manager.categories.*', '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 7h18M3 12h18M3 17h18"/></svg>', 'Kategori'],
    ['manager.reports.sales',    'manager.reports.*',    '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>', 'Laporan'],
] as [$r, $m, $icon, $label])
    @php $a = request()->routeIs($m); @endphp
    <a href="{{ route($r) }}" title="{{ $label }}"
       class="flex items-center gap-3 mx-1 py-2 rounded-lg text-sm transition-colors mb-0.5"
       :class="open ? 'px-3' : 'justify-center px-0'"
       style="{{ $a ? 'background-color: #181375; color: white; font-weight: 600;' : 'color: #374151;' }}"
       onmouseover="{{ $a ? '' : "this.style.backgroundColor='#f3f4f6'; this.style.color='#111827';" }}"
       onmouseout="{{ $a ? '' : "this.style.backgroundColor=''; this.style.color='#374151';" }}">
        <span class="w-6 flex-shrink-0 flex items-center justify-center">{!! $icon !!}</span>
        <span x-show="open" class="whitespace-nowrap truncate">{{ $label }}</span>
    </a>
@endforeach
            @endif

            {{-- KASIR --}}
            @if(in_array($role, ['admin', 'manager', 'cashier']))
                <div x-show="open" class="px-4 pt-4 pb-1">
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9ca3af;">Kasir</p>
                </div>
                <div x-show="!open" class="my-1 mx-2" style="border-top: 2px solid #e5e7eb;"></div>

                @foreach([
    ['cashier.orders.index',     'cashier.orders.*',    '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 12h6M9 16h6"/></svg>', 'Pesanan'],
    ['cashier.tables.index',     'cashier.tables.*',    '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 10h18M3 10V6a1 1 0 011-1h16a1 1 0 011 1v4M3 10l2 8h14l2-8M9 10v8M15 10v8"/></svg>', 'Meja'],
    ['cashier.payments.history', 'cashier.payments.*',  '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>', 'Pembayaran'],
] as [$r, $m, $icon, $label])
    @php $a = request()->routeIs($m) && !request()->routeIs('cashier.orders.create'); @endphp
    <a href="{{ route($r) }}" title="{{ $label }}"
       class="flex items-center gap-3 mx-1 py-2 rounded-lg text-sm transition-colors mb-0.5"
       :class="open ? 'px-3' : 'justify-center px-0'"
       style="{{ $a ? 'background-color: #181375; color: white; font-weight: 600;' : 'color: #374151;' }}"
       onmouseover="{{ $a ? '' : "this.style.backgroundColor='#f3f4f6'; this.style.color='#111827';" }}"
       onmouseout="{{ $a ? '' : "this.style.backgroundColor=''; this.style.color='#374151';" }}">
        <span class="w-6 flex-shrink-0 flex items-center justify-center">{!! $icon !!}</span>
        <span x-show="open" class="whitespace-nowrap truncate">{{ $label }}</span>
    </a>
@endforeach

{{-- Buat Pesanan --}}
@php $isCreate = request()->routeIs('cashier.orders.create'); @endphp
<a href="{{ route('cashier.orders.create') }}" title="Buat Pesanan"
   class="flex items-center gap-3 mx-1 py-2 rounded-lg text-sm transition-colors mb-0.5"
   :class="open ? 'px-3' : 'justify-center px-0'"
   style="{{ $isCreate ? 'background-color: #181375; color: white; font-weight: 600;' : 'color: #374151;' }}"
   onmouseover="{{ $isCreate ? '' : "this.style.backgroundColor='#f3f4f6'; this.style.color='#111827';" }}"
   onmouseout="{{ $isCreate ? '' : "this.style.backgroundColor=''; this.style.color='#374151';" }}">
    <span class="w-6 flex-shrink-0 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
    </span>
    <span x-show="open" class="whitespace-nowrap truncate">Buat Pesanan</span>
</a>
            @endif

            {{-- DAPUR --}}
            @if($role === 'kitchen_staff')
                <div x-show="open" class="px-4 pt-4 pb-1">
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9ca3af;">Dapur</p>
                </div>
                <div x-show="!open" class="my-1 mx-2" style="border-top: 2px solid #e5e7eb;"></div>
                @php $a = request()->routeIs('kitchen.*'); @endphp
                <a href="{{ route('kitchen.display') }}" title="Tampilan Dapur"
                   class="flex items-center gap-3 mx-1 py-2 rounded-lg text-sm transition-colors mb-0.5"
                   :class="open ? 'px-3' : 'justify-center px-0'"
                   style="{{ $a ? 'background-color: #181375; color: white; font-weight: 600;' : 'color: #374151;' }}"
                   onmouseover="{{ $a ? '' : "this.style.backgroundColor='#f3f4f6'; this.style.color='#111827';" }}"
                   onmouseout="{{ $a ? '' : "this.style.backgroundColor=''; this.style.color='#374151';" }}">
                    <span class="text-lg w-6 flex-shrink-0 text-center">👨‍🍳</span>
                    <span x-show="open" class="whitespace-nowrap">Tampilan Dapur</span>
                </a>
            @endif

            {{-- ADMIN --}}
            @if($role === 'admin')
                <div x-show="open" class="px-4 pt-4 pb-1">
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color: #9ca3af;">Admin</p>
                </div>
                <div x-show="!open" class="my-1 mx-2" style="border-top: 2px solid #e5e7eb;"></div>
                @php $a = request()->routeIs('admin.users.*'); @endphp
                <a href="{{ route('admin.users.index') }}" title="Users"
                   class="flex items-center gap-3 mx-1 py-2 rounded-lg text-sm transition-colors mb-0.5"
                   :class="open ? 'px-3' : 'justify-center px-0'"
                   style="{{ $a ? 'background-color: #181375; color: white; font-weight: 600;' : 'color: #374151;' }}"
                   onmouseover="{{ $a ? '' : "this.style.backgroundColor='#f3f4f6'; this.style.color='#111827';" }}"
                   onmouseout="{{ $a ? '' : "this.style.backgroundColor=''; this.style.color='#374151';" }}">
                    <span class="w-6 flex-shrink-0 flex items-center justify-center">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
    </svg>
</span>
                    <span x-show="open" class="whitespace-nowrap">Users</span>
                </a>
            @endif

            <div class="h-4"></div>
        </nav>

        {{-- USER --}}
        <div class="flex-shrink-0 relative" style="border-top: 2px solid #e5e7eb;">
            <div class="p-3 relative" x-data="{ menuOpen: false }">
                <button @click="menuOpen = !menuOpen"
                    class="flex items-center gap-3 w-full rounded-lg p-2 transition text-left"
                    onmouseover="this.style.backgroundColor='#f3f4f6'; this.querySelector('.avatar-circle').style.transform='scale(1.1)'; this.querySelector('.avatar-circle').style.boxShadow='0 4px 12px rgba(24,19,117,0.4)';"
                    onmouseout="this.style.backgroundColor=''; this.querySelector('.avatar-circle').style.transform='scale(1)'; this.querySelector('.avatar-circle').style.boxShadow='none';">
                    <div class="avatar-circle w-8 h-8 rounded-full text-white text-xs font-bold flex items-center justify-center flex-shrink-0 transition-all duration-200"
                        style="background-color: #181375;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <span x-show="open" class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate" style="color: #1f2937;">{{ auth()->user()->name }}</p>
                        <p class="text-xs capitalize" style="color: #6b7280;">{{ auth()->user()->role->name }}</p>
                    </span>
                </button>

                <div x-show="menuOpen"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     @click.outside="menuOpen = false"
                     class="mt-1 bg-white border border-gray-100 rounded-xl shadow-lg overflow-hidden">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                        <span>👤</span> Profil Saya
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                            <span>🚪</span> Logout
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
                @if(session('success') || session('status'))
                    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                        ✅ {{ session('success') ?? session('status') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                        ❌ {{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                        <p class="font-medium mb-1">⚠️ Terdapat kesalahan:</p>
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