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

        /*
         * Kunci fix sidebar scroll:
         * 1. aside TIDAK pakai overflow:hidden — biarkan default (visible)
         * 2. nav pakai overflow-y:auto + flex:1 agar mengisi sisa ruang
         * 3. logo, store, user pakai flex-shrink:0 agar tidak ikut menyusut
         */
        .sidebar-nav {
            flex: 1 1 0%;       /* ambil semua sisa tinggi */
            overflow-y: auto;   /* scroll jika konten melebihi tinggi */
            min-height: 0;      /* PENTING: tanpa ini flex child tidak bisa scroll di beberapa browser */
        }
        /* Sembunyikan scrollbar (tetap bisa scroll) */
        .sidebar-nav::-webkit-scrollbar { width: 0; }
        .sidebar-nav { scrollbar-width: none; }

        /* Transisi lebar sidebar & padding konten */
        .sidebar        { transition: width 0.25s cubic-bezier(0.4,0,0.2,1); }
        .main-content   { transition: padding-left 0.25s cubic-bezier(0.4,0,0.2,1); }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">

<div x-data="{ open: true }" class="min-h-screen">

    {{-- ══════════ SIDEBAR ══════════ --}}
    {{--
        PENTING: jangan pakai overflow-hidden di aside!
        Cukup flex-col + tinggi penuh dari inset-y-0.
        Scroll dihandle oleh .sidebar-nav di dalam.
    --}}
    <aside
        x-cloak
        :style="open ? 'width:240px' : 'width:64px'"
        class="sidebar bg-white border-r border-gray-200 fixed inset-y-0 left-0 z-50
               flex flex-col shadow-sm"
    >

        {{-- Logo — flex-shrink-0 agar tidak ikut menyusut --}}
        <div class="h-14 flex items-center gap-2 px-3 border-b border-gray-100 flex-shrink-0">
            <a href="{{ auth()->user()->dashboardRoute() }}"
               class="flex items-center gap-2.5 min-w-0 overflow-hidden">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">
                    D
                </div>
                <span x-show="open"
                      class="font-bold text-indigo-600 text-base whitespace-nowrap overflow-hidden">
                    DePOS
                </span>
            </a>
            <button x-show="open"
                    @click="open = false"
                    class="ml-auto p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition flex-shrink-0"
                    title="Tutup sidebar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 19l-7-7 7-7M19 19l-7-7 7-7"/>
                </svg>
            </button>
        </div>

        {{-- Store name — flex-shrink-0 --}}
        <div x-show="open" class="px-4 pt-3 pb-2 border-b border-gray-50 flex-shrink-0">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Store</p>
            <p class="text-sm font-semibold text-gray-800 truncate mt-0.5">
                {{ auth()->user()->store->name ?? 'Restaurant' }}
            </p>
        </div>

        {{-- ── NAV: flex-1 + overflow-y-auto = bisa scroll ── --}}
        <nav class="sidebar-nav py-2">
            @php $role = auth()->user()->role->slug; @endphp

            {{-- MANAGER --}}
            @if(in_array($role, ['admin', 'manager']))
                <div x-show="open" class="px-4 pt-3 pb-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Manager</p>
                </div>
                <div x-show="!open" class="my-1 mx-2 border-t border-gray-100"></div>

                @foreach([
                    ['manager.dashboard',        'manager.dashboard',    '📊', 'Dashboard'],
                    ['manager.products.index',   'manager.products.*',   '🍜', 'Menu'],
                    ['manager.categories.index', 'manager.categories.*', '📁', 'Kategori'],
                    ['manager.reports.sales',    'manager.reports.*',    '📈', 'Laporan'],
                ] as [$r, $m, $icon, $label])
                    @php $a = request()->routeIs($m); @endphp
                    <a href="{{ route($r) }}" title="{{ $label }}"
                       class="flex items-center gap-3 px-3 mx-1 py-2 rounded-lg text-sm transition-colors mb-0.5
                              {{ $a ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                        <span class="text-lg w-6 flex-shrink-0 text-center">{{ $icon }}</span>
                        <span x-show="open" class="whitespace-nowrap truncate">{{ $label }}</span>
                    </a>
                @endforeach
            @endif

            {{-- KASIR --}}
            @if(in_array($role, ['admin', 'manager', 'cashier']))
                <div x-show="open" class="px-4 pt-4 pb-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Kasir</p>
                </div>
                <div x-show="!open" class="my-1 mx-2 border-t border-gray-100"></div>

                @foreach([
                    ['cashier.orders.index',     'cashier.orders.*',    '🧾', 'Pesanan'],
                    ['cashier.tables.index',     'cashier.tables.*',    '🪑', 'Meja'],
                    ['cashier.payments.history', 'cashier.payments.*',  '💳', 'Pembayaran'],
                ] as [$r, $m, $icon, $label])
                    @php $a = request()->routeIs($m); @endphp
                    <a href="{{ route($r) }}" title="{{ $label }}"
                       class="flex items-center gap-3 px-3 mx-1 py-2 rounded-lg text-sm transition-colors mb-0.5
                              {{ $a ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                        <span class="text-lg w-6 flex-shrink-0 text-center">{{ $icon }}</span>
                        <span x-show="open" class="whitespace-nowrap truncate">{{ $label }}</span>
                    </a>
                @endforeach
            @endif

            {{-- DAPUR --}}
            @if($role === 'kitchen_staff')
                <div x-show="open" class="px-4 pt-4 pb-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Dapur</p>
                </div>
                <div x-show="!open" class="my-1 mx-2 border-t border-gray-100"></div>
                @php $a = request()->routeIs('kitchen.*'); @endphp
                <a href="{{ route('kitchen.display') }}" title="Tampilan Dapur"
                   class="flex items-center gap-3 px-3 mx-1 py-2 rounded-lg text-sm transition-colors mb-0.5
                          {{ $a ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <span class="text-lg w-6 flex-shrink-0 text-center">👨‍🍳</span>
                    <span x-show="open" class="whitespace-nowrap">Tampilan Dapur</span>
                </a>
            @endif

            {{-- ADMIN --}}
            @if($role === 'admin')
                <div x-show="open" class="px-4 pt-4 pb-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Admin</p>
                </div>
                <div x-show="!open" class="my-1 mx-2 border-t border-gray-100"></div>
                @php $a = request()->routeIs('admin.users.*'); @endphp
                <a href="{{ route('admin.users.index') }}" title="Users"
                   class="flex items-center gap-3 px-3 mx-1 py-2 rounded-lg text-sm transition-colors mb-0.5
                          {{ $a ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <span class="text-lg w-6 flex-shrink-0 text-center">👥</span>
                    <span x-show="open" class="whitespace-nowrap">Users</span>
                </a>
            @endif

            {{-- Spacer bawah supaya item terakhir tidak menempel tepat di tepi --}}
            <div class="h-4"></div>
        </nav>

        {{-- ── USER / EXPAND — flex-shrink-0, selalu di bawah ── --}}
        <div class="border-t border-gray-100 flex-shrink-0">

            {{-- Collapsed: tombol expand --}}
            <div x-show="!open" class="p-2">
                <button @click="open = true" title="Buka sidebar"
                        class="w-full flex items-center justify-center p-2 rounded-lg
                               text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            {{-- Expanded: user profile dropdown --}}
            <div x-show="open" class="p-3" x-data="{ menuOpen: false }">
                <button @click="menuOpen = !menuOpen"
                        class="flex items-center gap-3 w-full rounded-lg p-2 hover:bg-gray-50 transition text-left">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role->name }}</p>
                    </div>
                    <svg :class="menuOpen ? 'rotate-180' : ''"
                         class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="menuOpen"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     @click.outside="menuOpen = false"
                     class="mt-1 bg-white border border-gray-100 rounded-xl shadow-lg overflow-hidden">
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                        <span>👤</span> Profil Saya
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                            <span>🚪</span> Logout
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </aside>

    {{-- ══════════ MAIN CONTENT ══════════ --}}
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