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
        private $data,                   // Collection: period, revenue, count
        private string $period,
        private string $from,
        private string $to,
        private $nonRevenueOrders = null, // Collection keyed by status
    ) {}

    public function collection()
    {
        $rows = collect();

        // ── Header ───────────────────────────────────────────────────────
        $rows->push(['ANALITIK REVENUE', '', '']);
        $rows->push(['Periode', $this->from . ' s/d ' . $this->to, '']);
        $rows->push(['Granularitas', $this->periodLabel(), '']);
        $rows->push(['Catatan', 'Revenue dihitung dari pembayaran lunas (paid) saja.', '']);
        $rows->push(['', '', '']);

        // ── Data Revenue ──────────────────────────────────────────────────
        $rows->push(['DATA REVENUE', '', '']);
        $rows->push(['Periode', 'Total Revenue (Rp)', 'Jumlah Transaksi']);
        $dataCollection = collect($this->data);
        foreach ($dataCollection as $row) {
            $rows->push([
                $row->period ?? '-',
                (float) ($row->revenue ?? 0),
                (int)   ($row->count   ?? 0),
            ]);
        }
        if ($dataCollection->isEmpty()) {
            $rows->push(['Tidak ada data untuk periode ini', '', '']);
        } else {
            $rows->push(['TOTAL', (float) $dataCollection->sum('revenue'), (int) $dataCollection->sum('count')]);
        }
        $rows->push(['', '', '']);

        // ── Pesanan Non-Revenue ───────────────────────────────────────────
        $rows->push(['PESANAN TIDAK MENGHASILKAN REVENUE', '', '']);
        $rows->push(['Status', 'Jumlah Pesanan', 'Total Nilai (Rp)']);

        $nonRev = collect($this->nonRevenueOrders ?? []);
        $statusLabels = [
            'cancelled' => 'Dibatalkan',
            'pending'   => 'Pending',
            'cooking'   => 'Dimasak',
            'ready'     => 'Siap Saji',
        ];
        foreach (['cancelled', 'pending', 'cooking', 'ready'] as $key) {
            $nr = $nonRev->get($key);
            if ($nr) {
                $rows->push([
                    $statusLabels[$key] ?? ucfirst($key),
                    (int)   $nr->count,
                    (float) $nr->total_amount,
                ]);
            }
        }
        if ($nonRev->isEmpty()) {
            $rows->push(['Tidak ada pesanan non-revenue dalam periode ini.', '', '']);
        }

        return $rows;
    }

    public function headings(): array { return []; }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 13]],
            6 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string { return 'Analitik Revenue'; }

    private function periodLabel(): string
    {
        return match ($this->period) {
            'weekly'  => 'Mingguan',
            'monthly' => 'Bulanan',
            'yearly'  => 'Tahunan',
            default   => 'Harian',
        };
    }
}