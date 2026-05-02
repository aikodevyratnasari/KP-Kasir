<x-guest-layout>
    @section('title', 'Lupa Password')

    <h2 class="text-xl font-semibold text-gray-800 mb-2 text-center">Lupa Password?</h2>
    <p class="text-sm text-gray-500 text-center mb-6">
        Masukkan email Anda dan kami akan mengirimkan link reset password.
    </p>

    @if (session('status'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
            ✅ {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   required autofocus placeholder="nama@depos.id"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500
                          @error('email') border-red-400 bg-red-50 @enderror">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors mb-4"
            style="background-color: #2D54BF; border: 1px solid #2D54BF;"
            onmouseover="this.style.backgroundColor='#1e3d8f'"
            onmouseout="this.style.backgroundColor='#2D54BF'">
            Kirim Link Reset Password
        </button>

            <a href="{{ route('login') }}" 
    class="w-full inline-flex items-center justify-center py-2.5 text-sm font-medium rounded-lg transition-colors"
    style="border: 1.5px solid #dcdcdc; background-color: white; color: #374151;"
    onmouseover="this.style.backgroundColor='#f3f4f6';"
    onmouseout="this.style.backgroundColor='white';">
    Kembali ke halaman login
</a>
        
    </form>

    {{-- Info jika email tidak terkirim --}}
    <div class="mt-6 pt-4 border-t border-gray-100">
        <p class="text-xs text-center text-gray-400">
            Tidak menerima email? Hubungi Admin untuk reset password manual.
        </p>
    </div>
</x-guest-layout>