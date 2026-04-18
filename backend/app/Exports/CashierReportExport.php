<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashierReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private $cashiers,
        private $cancelledPerCashier,  // keyed by cashier_id
        private string $from,
        private string $to,
    ) {}

    public function collection()
    {
        $rows = collect();

        // ── Header ───────────────────────────────────────────────────────
        $rows->push(['LAPORAN PERFORMA KASIR', '', '', '', '', '', '']);
        $rows->push(['Periode', $this->from . ' s/d ' . $this->to, '', '', '', '', '']);
        $rows->push(['', '', '', '', '', '', '']);

        // ── Sub-header kolom ─────────────────────────────────────────────
        $rows->push([
            'Nama Kasir',
            'Pesanan Aktif',
            'Total Penjualan (Rp)',
            'Rata-rata/Pesanan (Rp)',
            'Avg Proses (mnt)',
            'Dibatalkan (qty)',
            'Nilai Dibatalkan (Rp)',
        ]);

        // ── Data ─────────────────────────────────────────────────────────
        $cashierCollection = collect($this->cashiers);
        $cancelledMap      = collect($this->cancelledPerCashier);

        foreach ($cashierCollection as $row) {
            $cancelled = $cancelledMap->get($row->cashier_id);
            $rows->push([
                $row->cashier                    ?? '-',
                (int)   ($row->order_count        ?? 0),
                (float) ($row->total_sales        ?? 0),
                (float) round($row->avg_order_value ?? 0, 0),
                $row->avg_processing_min ? (float) round($row->avg_processing_min, 1) : '-',
                $cancelled ? (int)   $cancelled->cancelled_count  : 0,
                $cancelled ? (float) $cancelled->cancelled_amount : 0,
            ]);
        }

        if ($cashierCollection->isEmpty()) {
            $rows->push(['Tidak ada data untuk periode ini', '', '', '', '', '', '']);
        }

        // ── Baris total ───────────────────────────────────────────────────
        if ($cashierCollection->isNotEmpty()) {
            $rows->push(['', '', '', '', '', '', '']);
            $rows->push([
                'TOTAL',
                (int)   $cashierCollection->sum('order_count'),
                (float) $cashierCollection->sum('total_sales'),
                '',
                '',
                (int)   $cancelledMap->sum('cancelled_count'),
                (float) $cancelledMap->sum('cancelled_amount'),
            ]);
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

    public function title(): string { return 'Laporan Kasir'; }
}