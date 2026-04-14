@props(['type', 'from', 'to', 'period' => null])

<div class="flex items-center gap-2" x-data="{ openSend: false, sendEmail: '', sendFormat: 'xlsx', sending: false, feedback: '', feedbackType: '' }">

    {{-- Dropdown Download --}}
    <div class="relative" x-data="{ open: false }">
        <button type="button" @click="open = !open" @click.outside="open = false"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all"
                style="color:white; background-color:#2D54BF; border:1px solid #2D54BF;"
                onmouseover="this.style.backgroundColor='#1e3d8f';"
                onmouseout="this.style.backgroundColor='#2D54BF';">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Unduh
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
        </button>

        <div x-show="open" x-transition
             style="position:absolute; right:0; top:calc(100% + 6px); background:#fff; border:1px solid #e5e7eb;
                    border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.12); min-width:170px; z-index:50; overflow:hidden;">

            <a href="{{ route('manager.reports.download', $type) }}?from={{ $from }}&to={{ $to }}&format=xlsx{{ $period ? '&period='.$period : '' }}"
               class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M3 9h18M3 15h18M9 3v18"/>
                </svg>
                Excel (.xlsx)
            </a>

            <a href="{{ route('manager.reports.download', $type) }}?from={{ $from }}&to={{ $to }}&format=csv{{ $period ? '&period='.$period : '' }}"
               class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-500" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                CSV (.csv)
            </a>

            <a href="{{ route('manager.reports.download', $type) }}?from={{ $from }}&to={{ $to }}&format=pdf{{ $period ? '&period='.$period : '' }}"
               class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-500" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                PDF (.pdf)
            </a>
        </div>
    </div>

    {{-- Tombol Kirim --}}
    <button type="button" @click="openSend = true"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all"
            style="color:#6366f1; background-color:#eef2ff; border:1px solid #c7d2fe;"
            onmouseover="this.style.backgroundColor='#e0e7ff';"
            onmouseout="this.style.backgroundColor='#eef2ff';">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2">
            <rect x="2" y="4" width="20" height="16" rx="2"/>
            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
        </svg>
        Kirim
    </button>

    {{--
        Modal: x-teleport="body" memastikan modal di-render langsung di <body>,
        bebas dari overflow/transform/z-index parent manapun — persis seperti
        modal "Kirim Struk" di halaman detail pesanan.
    --}}
    <template x-teleport="body">
            <div x-show="openSend"
                x-transition.opacity
                :style="openSend 
                    ? 'display:flex; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; padding:16px;' 
                    : 'display:none;'"
                @click.self="openSend = false; feedback = ''">

            <div x-show="openSend"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 @click.stop
                 style="background:#fff; border-radius:16px; padding:24px; width:100%;
                        max-width:420px; box-shadow:0 20px 60px rgba(0,0,0,0.25);">

                {{-- Header --}}
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-500" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                        </svg>
                        Kirim Laporan
                    </h3>
                    <button @click="openSend = false; feedback = ''"
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                {{-- WhatsApp (segera hadir) --}}
                <div class="mb-4">
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-green-500"
                             viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                        </svg>
                        WhatsApp
                        <span class="text-xs font-normal text-gray-400 normal-case tracking-normal">(segera hadir)</span>
                    </label>
                    <div class="flex gap-2">
                        <input type="text" placeholder="cth: 08123456789" disabled
                               class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-400 cursor-not-allowed">
                        <button type="button" disabled
                                class="px-4 py-2 text-sm font-medium rounded-lg flex-shrink-0 cursor-not-allowed"
                                style="background-color:#d1fae5; color:#6ee7b7;">
                            Kirim
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">Fitur WhatsApp sedang dalam pengembangan.</p>
                </div>

                <div style="height:1px; background:#f1f5f9; margin:14px 0;"></div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        Email
                    </label>

                    {{-- Pilih Format --}}
                    <div class="flex gap-2 mb-3">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" x-model="sendFormat" value="xlsx" class="accent-indigo-600"> 
                            <span class="text-sm text-gray-700">Excel</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" x-model="sendFormat" value="csv" class="accent-indigo-600"> 
                            <span class="text-sm text-gray-700">CSV</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" x-model="sendFormat" value="pdf" class="accent-indigo-600"> 
                            <span class="text-sm text-gray-700">PDF</span>
                        </label>
                    </div>

                    <div class="flex gap-2">
                        <input type="email" x-model="sendEmail"
                            placeholder="email@contoh.com"
                            class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-200">
                        <button type="button"
                                :disabled="sending"
                                @click="
                                    if (!sendEmail) return;
                                    sending = true; feedback = '';
                                    fetch('{{ route('manager.reports.send-email', $type) }}?from={{ $from }}&to={{ $to }}{{ isset($period) ? '&period='.$period : '' }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({ email: sendEmail, from: '{{ $from }}', to: '{{ $to }}', period: '{{ $period ?? 'daily' }}', format: sendFormat })
                                    })
                                    .then(r => r.json())
                                    .then(d => {
                                        sending = false;
                                        if (d.success) { feedbackType = 'ok'; feedback = 'Laporan berhasil dikirim ke ' + sendEmail; }
                                        else { feedbackType = 'err'; feedback = d.message || 'Gagal mengirim.'; }
                                    })
                                    .catch(() => { sending = false; feedbackType = 'err'; feedback = 'Gagal. Periksa koneksi.'; })
                                "
                                class="px-4 py-2 text-sm font-medium text-white rounded-lg flex-shrink-0 transition-colors"
                                :style="sending ? 'background-color:#a5b4fc;' : 'background-color:#6366f1;'"
                                x-text="sending ? 'Mengirim...' : 'Kirim'">
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5" x-text="sendFormat === 'pdf' ? 'Laporan dikirim sebagai lampiran PDF.' : sendFormat === 'csv' ? 'Laporan dikirim sebagai lampiran CSV.' : 'Laporan dikirim sebagai lampiran Excel (.xlsx).'"></p>

                    {{-- Feedback --}}
                    <div x-show="feedback"
                        :class="feedbackType === 'ok' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'"
                        class="mt-3 text-sm rounded-lg px-3 py-2 border"
                        x-text="(feedbackType === 'ok' ? '✓ ' : '') + feedback">
                    </div>
                </div>

            </div>
        </div>
    </template>
</div>