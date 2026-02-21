<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DePOS') }} — @yield('title', 'Login')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md px-4">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 text-white text-3xl mb-4 shadow-lg">
                🍽️
            </div>
            <h1 class="text-2xl font-bold text-gray-900">DePOS</h1>
            <p class="text-sm text-gray-500 mt-1">Restaurant Point of Sale</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
            {{ $slot }}
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            © {{ date('Y') }} DePOS Restaurant POS — PENS MAGANG TRI
        </p>
    </div>

</body>
</html>
