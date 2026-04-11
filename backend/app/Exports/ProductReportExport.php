<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private $topByQty,
        private $topByRevenue,
        private string $from,
        private string $to,
    ) {}

    public function collection()
    {
        $rows = collect();

        $rows->push(['LAPORAN PRODUK', '', '', '']);
        $rows->push(['Periode', $this->from . ' s/d ' . $this->to, '', '']);
        $rows->push(['', '', '', '']);

        $rows->push(['TOP PRODUK BERDASARKAN QTY', '', '', '']);
        $rows->push(['Produk', 'Qty Terjual', 'Pendapatan', '']);
        foreach ($this->topByQty as $p) {
            $rows->push([$p->product_name, $p->total_qty, $p->total_revenue, '']);
        }
        $rows->push(['', '', '', '']);

        $rows->push(['TOP PRODUK BERDASARKAN REVENUE', '', '', '']);
        $rows->push(['Produk', 'Pendapatan', 'Qty', '']);
        foreach ($this->topByRevenue as $p) {
            $rows->push([$p->product_name, $p->total_revenue, $p->total_qty, '']);
        }

        return $rows;
    }

    public function headings(): array { return []; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true, 'size' => 13]]];
    }

    public function title(): string { return 'Laporan Produk'; }
}