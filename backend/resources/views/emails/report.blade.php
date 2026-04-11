<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; max-width: 480px; margin: 0 auto; padding: 24px 16px; background: #f9fafb; }
        .card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
        .header { text-align:center; padding-bottom:16px; margin-bottom:16px; border-bottom:2px solid #6366f1; }
        .title { font-size:20px; font-weight:bold; color:#111; }
        .footer { text-align:center; font-size:12px; color:#9ca3af; margin-top:20px; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <div class="title">
            @php $labels = ['sales'=>'Laporan Penjualan','products'=>'Laporan Produk','revenue'=>'Analitik Revenue']; @endphp
            {{ $labels[$reportType] ?? 'Laporan' }}
        </div>
        <div style="font-size:13px; color:#6b7280; margin-top:6px;">Periode: {{ $from }} s/d {{ $to }}</div>
    </div>
    <p style="font-size:14px; color:#374151;">Halo,</p>
    <p style="font-size:14px; color:#374151; margin-top:8px;">
        Terlampir {{ $labels[$reportType] ?? 'laporan' }} untuk periode <strong>{{ $from }}</strong> hingga <strong>{{ $to }}</strong>.
    </p>
    <p style="font-size:13px; color:#6b7280; margin-top:16px;">Silakan buka file lampiran untuk melihat detail laporan.</p>
</div>
<div class="footer">
    <p>Email ini dikirim otomatis dari sistem POS.</p>
</div>
</body>
</html>