<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private $payments,
        private string $from,
        private string $to,
    ) {}

    public function collection()
    {
        $rows          = collect();
        $payCollection = collect($this->payments);

        // ── Header ───────────────────────────────────────────────────────
        $rows->push(['LAPORAN PEMBAYARAN', '', '', '', '', '', '']);
        $rows->push(['Periode', $this->from . ' s/d ' . $this->to, '', '', '', '', '']);
        $rows->push(['', '', '', '', '', '', '']);

        // ── Ringkasan per Status ───────────────────────────────────────────
        $rows->push(['RINGKASAN PER STATUS', '', '', '', '', '', '']);
        $rows->push(['Status', 'Jumlah', 'Total (Rp)', '', '', '', '']);

        $statusLabels = ['paid' => 'Lunas', 'pending' => 'Pending', 'refunded' => 'Refund'];
        $byStatus = $payCollection->groupBy('status');

        foreach (['paid', 'pending', 'refunded'] as $key) {
            $group = $byStatus->get($key, collect());
            if ($group->isNotEmpty()) {
                $rows->push([
                    $statusLabels[$key] ?? ucfirst($key),
                    $group->count(),
                    (float) $group->sum('amount'),
                    '', '', '', '',
                ]);
            }
        }
        $rows->push(['TOTAL', $payCollection->count(), (float) $payCollection->sum('amount'), '', '', '', '']);
        $rows->push(['', '', '', '', '', '', '']);

        // ── Sub-header kolom detail ───────────────────────────────────────
        $rows->push(['No. Pesanan', 'Pelanggan', 'Kasir', 'Metode', 'Jumlah (Rp)', 'Status', 'Waktu']);

        // ── Data detail ───────────────────────────────────────────────────
        foreach ($payCollection as $p) {
            $statusLabel = match ($p->status) {
                'paid'     => 'Lunas',
                'refunded' => 'Refund',
                'pending'  => 'Pending',
                default    => ucfirst($p->status),
            };
            $rows->push([
                $p->order?->order_number  ?? '-',
                $p->order?->customer_name ?? '-',
                $p->cashier?->name        ?? '-',
                $p->methodLabel(),
                (float) $p->amount,
                $statusLabel,
                $p->created_at->format('d M Y H:i'),
            ]);
        }

        if ($payCollection->isEmpty()) {
            $rows->push(['Tidak ada data pembayaran untuk periode ini', '', '', '', '', '', '']);
        }

        return $rows;
    }

    public function headings(): array { return []; }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 13]],
            4 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string { return 'Laporan Pembayaran'; }
}