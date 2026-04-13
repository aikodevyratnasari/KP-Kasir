<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RevenueReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private $data,
        private string $period,
        private string $from,
        private string $to,
    ) {}

    public function collection()
    {
        $rows = collect();
        $rows->push(['ANALITIK REVENUE', '', '']);
        $rows->push(['Periode', $this->from . ' s/d ' . $this->to, '']);
        $rows->push(['Granularitas', ucfirst($this->period), '']);
        $rows->push(['', '', '']);
        $rows->push(['Periode', 'Total Revenue', 'Jumlah Transaksi']);

        foreach ($this->data as $row) {
            $rows->push([
                $row->period ?? '-',
                $row->revenue ?? 0, // ReportService::revenueAnalytics returns 'revenue', not 'total'
                $row->count   ?? '-',
            ]);
        }

        return $rows;
    }

    public function headings(): array { return []; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true, 'size' => 13]]];
    }

    public function title(): string { return 'Analitik Revenue'; }
}