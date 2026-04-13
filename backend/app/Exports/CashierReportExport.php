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
        private string $from,
        private string $to,
    ) {}

    public function collection()
    {
        $rows = collect();

        $rows->push(['LAPORAN PERFORMA KASIR', '', '', '', '']);
        $rows->push(['Periode', $this->from . ' s/d ' . $this->to, '', '', '']);
        $rows->push(['', '', '', '', '']);
        $rows->push(['Nama Kasir', 'Total Pesanan', 'Total Penjualan', 'Rata-rata/Pesanan', 'Avg Proses (mnt)']);

        foreach ($this->cashiers as $row) {
            $rows->push([
                $row->cashier,
                $row->order_count,
                $row->total_sales,
                round($row->avg_order_value, 0),
                $row->avg_processing_min ? round($row->avg_processing_min, 1) : '-',
            ]);
        }

        return $rows;
    }

    public function headings(): array { return []; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true, 'size' => 13]]];
    }

    public function title(): string { return 'Laporan Kasir'; }
}