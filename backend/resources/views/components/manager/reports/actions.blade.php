@props(['type', 'from', 'to', 'period' => null, 'extraParams' => ''])

<div class="flex items-center gap-2">

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

            <a href="{{ route('manager.reports.download', $type) }}?from={{ $from }}&to={{ $to }}&format=xlsx{{ $period ? '&period='.$period : '' }}{{ $extraParams }}"
               class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M3 9h18M3 15h18M9 3v18"/>
                </svg>
                Excel (.xlsx)
            </a>

            <a href="{{ route('manager.reports.download', $type) }}?from={{ $from }}&to={{ $to }}&format=csv{{ $period ? '&period='.$period : '' }}{{ $extraParams }}"
               class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-500" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                CSV (.csv)
            </a>

            <a href="{{ route('manager.reports.download', $type) }}?from={{ $from }}&to={{ $to }}&format=pdf{{ $period ? '&period='.$period : '' }}{{ $extraParams }}"
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
    <button type="button"
            onclick="openSendModal('{{ $type }}')"
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

</div>

{{--
    Modal Kirim Laporan.
    extraParams di-encode JSON agar aman diteruskan ke JS tanpa risiko XSS / escaping salah.
    Nilai ini di-append ke URL fetch() untuk menyertakan filter aktif (misal method= & status=).
--}}
<div id="send-laporan-modal-{{ $type }}"
     style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5);"
     onclick="if(event.target===this){ closeSendModal('{{ $type }}'); }">

    <div style="display:flex; align-items:center; justify-content:center; min-height:100%; padding:16px;">
        <div style="background:#fff; border-radius:16px; padding:24px; width:100%; max-width:420px; box-shadow:0 20px 60px rgba(0,0,0,0.25);"
             onclick="event.stopPropagation()">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                    Kirim Laporan
                </h3>
                <button onclick="closeSendModal('{{ $type }}')"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            {{-- Pilih Format --}}
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Format</label>
            <div class="flex gap-4 mb-4">
                <label class="flex items-center gap-1.5 cursor-pointer text-sm text-gray-700">
                    <input type="radio" name="send_format_{{ $type }}" value="xlsx" checked
                           onchange="document.getElementById('send-format-{{ $type }}').value=this.value; updateFormatHint('{{ $type }}', this.value)"
                           class="accent-indigo-600"> Excel
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer text-sm text-gray-700">
                    <input type="radio" name="send_format_{{ $type }}" value="csv"
                           onchange="document.getElementById('send-format-{{ $type }}').value=this.value; updateFormatHint('{{ $type }}', this.value)"
                           class="accent-indigo-600"> CSV
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer text-sm text-gray-700">
                    <input type="radio" name="send_format_{{ $type }}" value="pdf"
                           onchange="document.getElementById('send-format-{{ $type }}').value=this.value; updateFormatHint('{{ $type }}', this.value)"
                           class="accent-indigo-600"> PDF
                </label>
            </div>
            <input type="hidden" id="send-format-{{ $type }}" value="xlsx">

            {{--
                extraParams disimpan sebagai data-attribute (sudah di-escape Blade otomatis)
                sehingga JS bisa membacanya dengan aman tanpa eval / innerHTML.
            --}}
            <div id="send-extra-container-{{ $type }}"
                 data-extra="{{ $extraParams }}"></div>

            {{-- Email --}}
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-indigo-500" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
                Alamat Email
            </label>
            <div class="flex gap-2">
                <input type="email"
                       id="send-email-{{ $type }}"
                       placeholder="email@contoh.com"
                       class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-200">
                <button type="button"
                        id="send-btn-{{ $type }}"
                        onclick="sendLaporanEmail('{{ $type }}', '{{ $from }}', '{{ $to }}', '{{ $period ?? 'daily' }}')"
                        class="px-4 py-2 text-sm font-medium text-white rounded-lg flex-shrink-0 transition-colors"
                        style="background-color:#6366f1;"
                        onmouseover="this.style.backgroundColor='#4f46e5';"
                        onmouseout="this.style.backgroundColor='#6366f1';">
                    Kirim
                </button>
            </div>
            <p id="format-hint-{{ $type }}" class="text-xs text-gray-400 mt-1.5">
                Laporan dikirim sebagai lampiran Excel (.xlsx).
            </p>
            <div id="send-feedback-{{ $type }}" style="display:none;"
                 class="mt-3 text-sm rounded-lg px-3 py-2"></div>
        </div>
    </div>
</div>

<script>
function openSendModal(type) {
    document.getElementById('send-laporan-modal-' + type).style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeSendModal(type) {
    document.getElementById('send-laporan-modal-' + type).style.display = 'none';
    document.body.style.overflow = '';
    var fb = document.getElementById('send-feedback-' + type);
    if (fb) fb.style.display = 'none';
    var btn = document.getElementById('send-btn-' + type);
    if (btn) {
        btn.textContent = 'Kirim';
        btn.style.backgroundColor = '#6366f1';
        btn.disabled = false;
        btn.onmouseover = function() { this.style.backgroundColor='#4f46e5'; };
        btn.onmouseout  = function() { this.style.backgroundColor='#6366f1'; };
    }
}

function updateFormatHint(type, format) {
    var hints = {
        xlsx: 'Laporan dikirim sebagai lampiran Excel (.xlsx).',
        csv:  'Laporan dikirim sebagai lampiran CSV.',
        pdf:  'Laporan dikirim sebagai lampiran PDF.'
    };
    var el = document.getElementById('format-hint-' + type);
    if (el) el.textContent = hints[format] || hints.xlsx;
}

function sendLaporanEmail(type, from, to, period) {
    var email    = document.getElementById('send-email-' + type).value.trim();
    var format   = document.getElementById('send-format-' + type).value;
    var btn      = document.getElementById('send-btn-' + type);
    var feedback = document.getElementById('send-feedback-' + type);

    // Baca extraParams dari data-attribute (aman dari XSS)
    var extraContainer = document.getElementById('send-extra-container-' + type);
    var extraVal = extraContainer ? (extraContainer.getAttribute('data-extra') || '') : '';

    if (!email) {
        document.getElementById('send-email-' + type).focus();
        return;
    }

    btn.disabled              = true;
    btn.textContent           = 'Mengirim...';
    btn.style.backgroundColor = '#a5b4fc';
    btn.onmouseover           = null;
    btn.onmouseout            = null;
    feedback.style.display    = 'none';

    // Bangun URL: sertakan from, to, period, format, dan filter tambahan (extraVal)
    var url = '/manager/reports/' + type + '/send-email'
            + '?from=' + encodeURIComponent(from)
            + '&to='   + encodeURIComponent(to)
            + '&period=' + encodeURIComponent(period)
            + extraVal;

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ email: email, from: from, to: to, period: period, format: format })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        btn.disabled = false;
        feedback.style.display = 'block';
        if (d.success) {
            feedback.className   = 'mt-3 text-sm rounded-lg px-3 py-2 bg-green-50 text-green-700 border border-green-200';
            feedback.textContent = '✓ Laporan berhasil dikirim ke ' + email;
            btn.textContent      = 'Terkirim ✓';
            btn.style.backgroundColor = '#22c55e';
            btn.onmouseover = null;
            btn.onmouseout  = null;
        } else {
            feedback.className   = 'mt-3 text-sm rounded-lg px-3 py-2 bg-red-50 text-red-700 border border-red-200';
            feedback.textContent = d.message || 'Gagal mengirim. Coba lagi.';
            btn.textContent      = 'Kirim';
            btn.style.backgroundColor = '#6366f1';
            btn.onmouseover = function() { this.style.backgroundColor='#4f46e5'; };
            btn.onmouseout  = function() { this.style.backgroundColor='#6366f1'; };
        }
    })
    .catch(function() {
        btn.disabled = false;
        feedback.style.display   = 'block';
        feedback.className       = 'mt-3 text-sm rounded-lg px-3 py-2 bg-red-50 text-red-700 border border-red-200';
        feedback.textContent     = 'Gagal mengirim. Periksa koneksi internet.';
        btn.textContent          = 'Kirim';
        btn.style.backgroundColor = '#6366f1';
        btn.onmouseover = function() { this.style.backgroundColor='#4f46e5'; };
        btn.onmouseout  = function() { this.style.backgroundColor='#6366f1'; };
    });
}
</script>