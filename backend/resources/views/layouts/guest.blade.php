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
            @php
                $guestLogo = asset('images/image.png');
                $guestStore = \App\Models\Store::whereNotNull('logo_path')->where('is_active', true)->first()
                    ?? \App\Models\Store::where('is_active', true)->first();
                if ($guestStore) {
                    $guestLogo = $guestStore->logo_url;
                }
            @endphp
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4 shadow-lg overflow-hidden">
                    <img src="{{ $guestLogo }}" alt="DePOS Logo" class="w-full h-full object-contain">
                </div>
                <h1 class="text-2xl font-bold text-gray-900">DePOS</h1>
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