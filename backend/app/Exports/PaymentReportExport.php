<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentReportExport implements FromCollection, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private $payments,       // Collection<Payment> with order, cashier loaded
        private $unpaidOrders,   // Collection<Order> yang belum punya payment paid
        private string $from,
        private string $to,
    ) {}

    public function collection(): Collection
    {
        $rows       = collect();
        $payCol     = collect($this->payments);
        $unpaid     = collect($this->unpaidOrders);

        // Sudah 1 payment per order, tinggal kelompokkan per status
        $paidGroup    = $payCol->where('status', 'paid');
        $refundGroup  = $payCol->where('status', 'refunded');
        $pendingGroup = $payCol->where('status', 'pending');

        // ── HEADER ────────────────────────────────────────────────────────────
        $rows->push(['LAPORAN PEMBAYARAN', '', '', '', '', '', '', '']);
        $rows->push(['Periode', $this->from . ' s/d ' . $this->to, '', '', '', '', '', '']);
        $rows->push(['Dicetak', now()->format('d M Y, H:i'), '', '', '', '', '', '']);
        $rows->push(['Catatan', '1 baris = 1 pesanan (payment status terbaru)', '', '', '', '', '', '']);
        $rows->push(['', '', '', '', '', '', '', '']);

        // ── RINGKASAN EKSEKUTIF ───────────────────────────────────────────────
        $rows->push(['RINGKASAN EKSEKUTIF', '', '', '', '', '', '', '']);
        $rows->push(['Keterangan', 'Jumlah Pesanan', 'Total (Rp)', '', '', '', '', '']);
        $rows->push(['Pembayaran Lunas',      $paidGroup->count(),    (float) $paidGroup->sum('amount'),    '', '', '', '', '']);
        $rows->push(['Refund',                $refundGroup->count(),  (float) $refundGroup->sum('amount'),  '', '', '', '', '']);
        $rows->push(['Pending (belum bayar)', $pendingGroup->count(), (float) $pendingGroup->sum('amount'), '', '', '', '', '']);
        $rows->push(['Pesanan Aktif Belum Bayar', $unpaid->count(),   (float) $unpaid->sum('total_amount'), '', '', '', '', '']);
        $rows->push(['TOTAL LUNAS (NET)',
            $paidGroup->count(),
            (float) $paidGroup->sum('amount') - (float) $refundGroup->sum('refund_amount'),
            '', '', '', '', '',
        ]);
        $rows->push(['', '', '', '', '', '', '', '']);

        // ── RINGKASAN PER METODE (hanya paid) ────────────────────────────────
        $rows->push(['RINGKASAN PER METODE PEMBAYARAN (Lunas)', '', '', '', '', '', '', '']);
        $rows->push(['Metode', 'Jumlah Pesanan', 'Total (Rp)', '', '', '', '', '']);

        $methodLabels = [
            'cash'          => 'Tunai',
            'card'          => 'Kartu (EDC)',
            'qris'          => 'QRIS',
            'ewallet'       => 'E-Wallet',
            'bank_transfer' => 'Transfer Bank',
        ];

        $byMethod    = $paidGroup->groupBy('payment_method');
        $methodOrder = ['cash', 'card', 'qris', 'ewallet', 'bank_transfer'];
        foreach ($methodOrder as $method) {
            $group = $byMethod->get($method, collect());
            if ($group->isNotEmpty()) {
                $rows->push([
                    $methodLabels[$method] ?? ucfirst($method),
                    $group->count(),
                    (float) $group->sum('amount'),
                    '', '', '', '', '',
                ]);
            }
        }
        foreach ($byMethod as $method => $group) {
            if (! in_array($method, $methodOrder)) {
                $rows->push([ucfirst($method), $group->count(), (float) $group->sum('amount'), '', '', '', '', '']);
            }
        }
        $rows->push(['TOTAL', $paidGroup->count(), (float) $paidGroup->sum('amount'), '', '', '', '', '']);
        $rows->push(['', '', '', '', '', '', '', '']);

        // ── BAGIAN A: TRANSAKSI LUNAS ─────────────────────────────────────────
        $rows->push(['A. TRANSAKSI LUNAS', '', '', '', '', '', '', '']);
        $rows->push(['No. Pesanan', 'Pelanggan', 'Kasir', 'Metode', 'Detail Metode', 'Jumlah (Rp)', 'Status', 'Waktu']);

        foreach ($paidGroup->sortByDesc('created_at') as $p) {
            $rows->push([
                $p->order?->order_number  ?? '-',
                $p->order?->customer_name ?? '-',
                $p->cashier?->name        ?? '-',
                $methodLabels[$p->payment_method] ?? ucfirst($p->payment_method),
                $this->buildMethodDetail($p),
                (float) $p->amount,
                'Lunas',
                $p->created_at->format('d M Y H:i'),
            ]);
        }

        if ($paidGroup->isEmpty()) {
            $rows->push(['Tidak ada transaksi lunas', '', '', '', '', '', '', '']);
        } else {
            $rows->push(['Sub-total Lunas', $paidGroup->count(), (float) $paidGroup->sum('amount'), '', '', '', '', '']);
        }
        $rows->push(['', '', '', '', '', '', '', '']);

        // ── BAGIAN B: REFUND ──────────────────────────────────────────────────
        $rows->push(['B. TRANSAKSI REFUND', '', '', '', '', '', '', '']);
        $rows->push(['No. Pesanan', 'Pelanggan', 'Kasir', 'Metode', 'Detail Metode', 'Jumlah Refund (Rp)', 'Alasan Refund', 'Waktu Refund']);

        foreach ($refundGroup->sortByDesc('refunded_at') as $p) {
            $rows->push([
                $p->order?->order_number  ?? '-',
                $p->order?->customer_name ?? '-',
                $p->cashier?->name        ?? '-',
                $methodLabels[$p->payment_method] ?? ucfirst($p->payment_method),
                $this->buildMethodDetail($p),
                (float) ($p->refund_amount ?? $p->amount),
                $p->refund_reason ?? '-',
                $p->refunded_at?->format('d M Y H:i') ?? '-',
            ]);
        }

        if ($refundGroup->isEmpty()) {
            $rows->push(['Tidak ada transaksi refund', '', '', '', '', '', '', '']);
        } else {
            $rows->push([
                'Sub-total Refund',
                $refundGroup->count(),
                (float) $refundGroup->sum('refund_amount') ?: (float) $refundGroup->sum('amount'),
                '', '', '', '', '',
            ]);
        }
        $rows->push(['', '', '', '', '', '', '', '']);

        // ── BAGIAN C: PENDING GATEWAY ─────────────────────────────────────────
        $rows->push(['C. PENDING GATEWAY (Menunggu Konfirmasi)', '', '', '', '', '', '', '']);
        $rows->push(['No. Pesanan', 'Pelanggan', 'Kasir', 'Metode', 'Detail Metode', 'Jumlah (Rp)', 'Gateway', 'Waktu Inisiasi']);

        foreach ($pendingGroup->sortByDesc('created_at') as $p) {
            $rows->push([
                $p->order?->order_number  ?? '-',
                $p->order?->customer_name ?? '-',
                $p->cashier?->name        ?? '-',
                $methodLabels[$p->payment_method] ?? ucfirst($p->payment_method),
                $this->buildMethodDetail($p),
                (float) $p->amount,
                ucfirst($p->gateway ?? 'midtrans'),
                $p->created_at->format('d M Y H:i'),
            ]);
        }

        if ($pendingGroup->isEmpty()) {
            $rows->push(['Tidak ada transaksi pending', '', '', '', '', '', '', '']);
        } else {
            $rows->push(['Sub-total Pending', $pendingGroup->count(), (float) $pendingGroup->sum('amount'), '', '', '', '', '']);
        }
        $rows->push(['', '', '', '', '', '', '', '']);

        // ── BAGIAN D: PESANAN AKTIF BELUM BAYAR ──────────────────────────────
        $rows->push(['D. PESANAN AKTIF BELUM BAYAR', '', '', '', '', '', '', '']);
        $rows->push(['No. Pesanan', 'Pelanggan', 'Kasir', 'Tipe Pesanan', 'Meja', 'Total Tagihan (Rp)', 'Status Pesanan', 'Dibuat']);

        foreach ($unpaid->sortByDesc('created_at') as $o) {
            $statusLabels = ['pending' => 'Pending', 'cooking' => 'Dimasak', 'ready' => 'Siap Saji'];
            $rows->push([
                $o->order_number   ?? '-',
                $o->customer_name  ?? '-',
                $o->cashier?->name ?? '-',
                ucwords(str_replace('_', ' ', $o->order_type ?? '')),
                $o->table?->number ?? '-',
                (float) $o->total_amount,
                $statusLabels[$o->status] ?? ucfirst($o->status),
                $o->created_at->format('d M Y H:i'),
            ]);
        }

        if ($unpaid->isEmpty()) {
            $rows->push(['Tidak ada pesanan aktif yang belum dibayar', '', '', '', '', '', '', '']);
        } else {
            $rows->push(['Total Belum Bayar', $unpaid->count() . ' pesanan', (float) $unpaid->sum('total_amount'), '', '', '', '', '']);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet): void
    {
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        // Default semua sel: font Arial 10pt, vertical center
        $sheet->getStyle("A1:{$highestCol}{$highestRow}")->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 10],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Iterasi baris untuk styling berdasarkan konten
        for ($row = 1; $row <= $highestRow; $row++) {
            $cellValue = (string) $sheet->getCell("A{$row}")->getValue();

            // ── Judul utama (baris 1) ──
            if ($row === 1) {
                $sheet->getStyle("A{$row}:{$highestCol}{$row}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '181375']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(24);
                continue;
            }

            // ── Section headers (RINGKASAN, BAGIAN A/B/C/D) ──
            if (
                str_starts_with($cellValue, 'RINGKASAN') ||
                str_starts_with($cellValue, 'BAGIAN') ||
                str_starts_with($cellValue, 'A. ') ||
                str_starts_with($cellValue, 'B. ') ||
                str_starts_with($cellValue, 'C. ') ||
                str_starts_with($cellValue, 'D. ')
            ) {
                $sheet->getStyle("A{$row}:{$highestCol}{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D54BF']],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(20);
                continue;
            }

            // ── Column headers (baris judul kolom — setelah section header) ──
            if (in_array($cellValue, [
                'Keterangan', 'Metode', 'No. Pesanan',
                'No. Pesanan', 'Tipe Pesanan',
            ])) {
                $sheet->getStyle("A{$row}:{$highestCol}{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '374151']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                    'borders' => [
                        'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
                    ],
                ]);
                continue;
            }

            // ── Sub-total / Total rows ──
            if (
                str_starts_with($cellValue, 'Sub-total') ||
                str_starts_with($cellValue, 'TOTAL') ||
                str_starts_with($cellValue, 'Total Belum Bayar')
            ) {
                $sheet->getStyle("A{$row}:{$highestCol}{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                    'borders' => [
                        'top'    => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
                        'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
                    ],
                ]);
                continue;
            }

            // ── Zebra striping untuk baris data ──
            // Baris genap mendapat background sangat terang
            if ($row % 2 === 0 && $cellValue !== '' && ! str_starts_with($cellValue, 'Tidak ada')) {
                $sheet->getStyle("A{$row}:{$highestCol}{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FAFAFA']],
                ]);
            }
        }

        // ── Format kolom angka (Jumlah / Total) ──
        // Kolom F (index 6) biasanya berisi angka di semua bagian
        $numberFormat = '#,##0';
        $sheet->getStyle("C2:C{$highestRow}")->getNumberFormat()->setFormatCode($numberFormat);
        $sheet->getStyle("F2:F{$highestRow}")->getNumberFormat()->setFormatCode($numberFormat);

        // ── Freeze baris pertama ──
        $sheet->freezePane('A2');

        // ── Lebar kolom manual untuk kolom yang sering overflow ──
        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(28);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(28);
        $sheet->getColumnDimension('H')->setWidth(20);
    }

    public function title(): string
    {
        return 'Laporan Pembayaran';
    }

    // ── Helper: bangun string detail metode pembayaran ────────────────────
    private function buildMethodDetail($payment): string
    {
        return match ($payment->payment_method) {
            'card' => collect([
                $payment->card_type,
                $payment->card_last_four ? "(...{$payment->card_last_four})" : null,
                $payment->approval_code  ? "Kode: {$payment->approval_code}" : null,
            ])->filter()->implode(' '),

            'ewallet' => $payment->ewalletLabel(),

            'bank_transfer' => collect([
                match (strtolower($payment->bank ?? $payment->ewallet_type ?? '')) {
                    'bca'     => 'BCA',
                    'bni'     => 'BNI',
                    'bri'     => 'BRI',
                    'mandiri' => 'Mandiri',
                    'permata' => 'Permata',
                    default   => strtoupper($payment->bank ?? $payment->ewallet_type ?? ''),
                },
                $payment->va_number ? "VA: {$payment->va_number}" : null,
            ])->filter()->implode(' · '),

            'qris' => $payment->gateway_trx_id
                ? 'ID: ' . substr($payment->gateway_trx_id, 0, 16) . '...'
                : 'QRIS',

            default => '-',
        };
    }
}