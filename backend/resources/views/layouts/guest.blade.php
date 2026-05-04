<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DePOS') }} — @yield('title', 'Login')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
    @keyframes blob1 {
        0%   { transform: translate(0px, 0px) scale(1); }
        25%  { transform: translate(150px, -100px) scale(1.2); }
        50%  { transform: translate(200px, 150px) scale(0.85); }
        75%  { transform: translate(-100px, 100px) scale(1.1); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    @keyframes blob2 {
        0%   { transform: translate(0px, 0px) scale(1); }
        25%  { transform: translate(-150px, 100px) scale(1.15); }
        50%  { transform: translate(-200px, -150px) scale(0.85); }
        75%  { transform: translate(100px, -100px) scale(1.2); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    @keyframes blob3 {
        0%   { transform: translate(0px, 0px) scale(1); }
        25%  { transform: translate(100px, 150px) scale(0.85); }
        50%  { transform: translate(-150px, 100px) scale(1.2); }
        75%  { transform: translate(-100px, -150px) scale(1.1); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .blob {
        position: fixed;
        border-radius: 9999px;
        filter: blur(80px);
        opacity: 0.75;
    }
    .card-glass {
        background: linear-gradient(135deg, rgba(255,255,255,1) 0%, rgba(220,232,255,0.98) 100%);
        border: 1px solid rgba(30,61,143,0.2);
        box-shadow: 0 25px 60px rgba(10,20,77,0.4), 0 0 0 1px rgba(255,255,255,0.8) inset;
    }
    </style>
</head>
<body class="font-sans antialiased min-h-screen relative" style="background-color:#0a1f4d;">

    {{-- Blob background --}}
    <div class="blob" style="width:600px; height:600px; background:#1e3d8f; top:-150px; left:-150px; animation: blob1 4s ease-in-out infinite;"></div>
    <div class="blob" style="width:550px; height:550px; background:#2D54BF; top:40%; left:55%; animation: blob2 3s ease-in-out infinite;"></div>
    <div class="blob" style="width:500px; height:500px; background:#1a5fb4; bottom:-100px; left:25%; animation: blob3 5s ease-in-out infinite;"></div>
    <div class="blob" style="width:450px; height:450px; background:#2D54BF; top:20%; left:-80px; animation: blob1 3.5s ease-in-out infinite reverse;"></div>

    {{-- Blob putih bergerak --}}
    <div class="blob" style="width:300px; height:300px; background:rgba(255,255,255,0.25); top:10%; left:30%; animation: blob2 3s ease-in-out infinite;"></div>
    <div class="blob" style="width:200px; height:200px; background:rgba(255,255,255,0.2); top:60%; left:10%; animation: blob3 4s ease-in-out infinite reverse;"></div>
    <div class="blob" style="width:250px; height:250px; background:rgba(255,255,255,0.15); top:30%; left:65%; animation: blob1 2.5s ease-in-out infinite;"></div>

    <div class="relative z-10 flex flex-col items-center justify-center min-h-screen py-12 px-4">
        <div class="w-full max-w-md">

            {{-- Logo cabang pusat (HQ) --}}
            @php
                // Gunakan logo cabang pusat. Jika belum ada HQ, fallback ke toko aktif pertama.
                $guestLogoUrl = \App\Models\Store::headquartersLogoUrl();
            @endphp
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 mb-4 shadow-lg overflow-hidden bg-white" style="border-radius: 28px;">
                    <img src="{{ $guestLogoUrl }}" alt="DePOS Logo" class="w-full h-full object-cover">
                </div>
                <h1 class="text-2xl font-bold text-white">DePOS</h1>
            </div>

            {{-- Card --}}
            <div class="card-glass rounded-2xl p-8">
                {{ $slot }}
            </div>

            <p class="text-center text-xs text-white mt-6">
                © {{ date('Y') }} DePOS — Intern PENS
            </p>

        </div>
    </div>

</body>
</html>