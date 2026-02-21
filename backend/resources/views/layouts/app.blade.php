<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DePOS') }} — @yield('title', 'Dashboard')</title>

    {{-- Vite (Breeze default) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100">

    {{-- Navbar --}}
    <nav class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">

                {{-- Logo --}}
                <div class="flex items-center gap-4">
                    <a href="{{ auth()->user()->dashboardRoute() }}" class="text-xl font-bold text-indigo-600 tracking-tight">
                        🍽️ DePOS
                    </a>
                    {{-- Store name --}}
                    @if(auth()->user()->store)
                        <span class="text-sm text-gray-500 hidden sm:block">
                            {{ auth()->user()->store->name }}
                        </span>
                    @endif
                </div>

                {{-- Nav links per role --}}
                <div class="hidden sm:flex items-center gap-1">
                    @php $role = auth()->user()->role->slug; @endphp

                    @if(in_array($role, ['admin', 'manager']))
                        <a href="{{ route('manager.dashboard') }}" class="nav-link @active('manager/dashboard')">Dashboard</a>
                        <a href="{{ route('manager.products.index') }}" class="nav-link @active('manager/products*')">Menu</a>
                        <a href="{{ route('manager.categories.index') }}" class="nav-link @active('manager/categories*')">Kategori</a>
                        <a href="{{ route('manager.reports.sales') }}" class="nav-link @active('manager/reports*')">Laporan</a>
                    @endif

                    @if(in_array($role, ['admin', 'manager', 'cashier']))
                        <a href="{{ route('cashier.orders.index') }}" class="nav-link @active('cashier/orders*')">Pesanan</a>
                        <a href="{{ route('cashier.tables.index') }}" class="nav-link @active('cashier/tables*')">Meja</a>
                        <a href="{{ route('cashier.payments.history') }}" class="nav-link @active('cashier/payments*')">Pembayaran</a>
                    @endif

                    @if($role === 'kitchen_staff')
                        <a href="{{ route('kitchen.display') }}" class="nav-link @active('kitchen*')">Tampilan Dapur</a>
                    @endif

                    @if($role === 'admin')
                        <a href="{{ route('admin.users.index') }}" class="nav-link @active('admin/users*')">Users</a>
                    @endif
                </div>

                {{-- User Dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-700 hover:text-indigo-600 focus:outline-none">
                        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <span class="hidden sm:block">{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-xs font-semibold text-gray-500 uppercase">{{ auth()->user()->role->name }}</p>
                            <p class="text-sm text-gray-700 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">👤 Profil Saya</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">🚪 Logout</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success') || session('status'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
                ✅ {{ session('success') ?? session('status') }}
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
                ❌ {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    {{-- Alpine.js (Breeze ships with it) --}}
    @stack('scripts')
</body>
</html>
