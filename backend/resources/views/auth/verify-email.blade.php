<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DePOS — Verifikasi Email</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 text-white text-3xl mb-4 shadow-lg">
            🍽️
        </div>
        <h1 class="text-2xl font-bold text-gray-900">DePOS</h1>
        <p class="text-sm text-gray-500 mt-1">Restaurant Point of Sale</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 text-center">

        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-3xl">📧</span>
        </div>

        <h2 class="text-xl font-bold text-gray-900 mb-2">Verifikasi Email Anda</h2>

        <p class="text-sm text-gray-600 mb-6">
            Akun Anda sudah dibuat oleh Administrator. Sebelum melanjutkan, silakan cek email
            <strong class="text-gray-900">{{ $user->email }}</strong>
            dan klik link verifikasi yang telah dikirimkan.
        </p>

        {{-- Alert kirim ulang berhasil --}}
        @if(session('resent'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm mb-4">
                ✅ Email verifikasi baru telah dikirim! Silakan cek kotak masuk Anda.
            </div>
        @endif

        {{-- Tombol kirim ulang --}}
        <form method="POST" action="{{ route('verification.send') }}" class="mb-4">
            @csrf
            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors text-sm">
                Kirim Ulang Email Verifikasi
            </button>
        </form>

        {{-- Tips --}}
        <div class="bg-gray-50 rounded-xl p-4 text-left text-xs text-gray-500 space-y-1.5 mb-4">
            <p class="font-semibold text-gray-600 mb-2">💡 Tidak menerima email?</p>
            <p>• Cek folder <strong>Spam</strong> atau <strong>Promosi</strong></p>
            <p>• Pastikan email <strong>{{ $user->email }}</strong> benar</p>
            <p>• Link verifikasi berlaku selama <strong>72 jam</strong></p>
            <p>• Hubungi Administrator jika masih bermasalah</p>
        </div>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 hover:underline">
                Logout dari akun ini
            </button>
        </form>

    </div>

    <p class="text-center text-xs text-gray-400 mt-6">
        © {{ date('Y') }} DePOS Restaurant POS
    </p>
</div>

</body>
</html>