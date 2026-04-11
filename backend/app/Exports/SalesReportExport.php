<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        private $dailyTrend,
        private $byPaymentMethod,
        private $byOrderType,
        private float $totalSales,
        private int $totalOrders,
        private float $avgTransaction,
        private string $from,
        private string $to,
    ) {}

    public function collection()
    {
        $rows = collect();

        // Summary
        $rows->push(['LAPORAN PENJUALAN', '', '']);
        $rows->push(['Periode', $this->from . ' s/d ' . $this->to, '']);
        $rows->push(['Total Penjualan', 'Rp ' . number_format($this->totalSales, 0, ',', '.'), '']);
        $rows->push(['Total Pesanan', $this->totalOrders, '']);
        $rows->push(['Rata-rata Transaksi', 'Rp ' . number_format($this->avgTransaction, 0, ',', '.'), '']);
        $rows->push(['', '', '']);

        // Tren Harian
        $rows->push(['TREN HARIAN', '', '']);
        $rows->push(['Tanggal', 'Total Penjualan', 'Jumlah Transaksi']);
        foreach ($this->dailyTrend as $d) {
            $rows->push([
                \Carbon\Carbon::parse($d->date)->format('d M Y'),
                $d->total,
                $d->count,
            ]);
        }
        $rows->push(['', '', '']);

        // Per Metode Pembayaran
        $rows->push(['PER METODE PEMBAYARAN', '', '']);
        $rows->push(['Metode', 'Total', 'Jumlah Transaksi']);
        foreach ($this->byPaymentMethod as $m) {
            $rows->push([$m->payment_method, $m->total, $m->count]);
        }
        $rows->push(['', '', '']);

        // Per Tipe Pesanan
        $rows->push(['PER TIPE PESANAN', '', '']);
        $rows->push(['Tipe', 'Total', 'Jumlah']);
        foreach ($this->byOrderType as $t) {
            $rows->push([str_replace('_', ' ', $t->order_type), $t->total, $t->count]);
        }

        return $rows;
    }

    public function headings(): array { return []; }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 13]],
        ];
    }

    public function title(): string { return 'Laporan Penjualan'; }
}