<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DePOS') }} — @yield('title', 'Login')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen">

    <div class="flex flex-col items-center justify-center min-h-screen py-12 px-4">

        <div class="w-full max-w-md">

            {{-- Logo --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 text-white mb-4 shadow-lg">
                    {{-- utensils / fork-knife --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
                    </svg>
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

    </div>

</body>
</html>