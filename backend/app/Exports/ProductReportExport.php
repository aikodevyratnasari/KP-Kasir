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
        private $cancelledSummary,   // stdClass|null: count, total_amount
        private string $from,
        private string $to,
        private $cancelledItems = null,  // Collection|null: product_name, total_qty, total_lost
    ) {}

    public function collection()
    {
        $rows = collect();

        // ── Header ───────────────────────────────────────────────────────
        $rows->push(['LAPORAN PRODUK', '', '', '']);
        $rows->push(['Periode', $this->from . ' s/d ' . $this->to, '', '']);
        $rows->push(['Catatan', 'Data di bawah hanya dari pesanan yang tidak dibatalkan.', '', '']);
        $rows->push(['', '', '', '']);

        // ── Top by Qty ────────────────────────────────────────────────────
        $rows->push(['TOP PRODUK BERDASARKAN QTY', '', '', '']);
        $rows->push(['Produk', 'Qty Terjual', 'Pendapatan (Rp)', '']);
        $qtyCollection = collect($this->topByQty);
        foreach ($qtyCollection as $p) {
            $rows->push([$p->product_name ?? '-', (int) ($p->total_qty ?? 0), (float) ($p->total_revenue ?? 0), '']);
        }
        if ($qtyCollection->isEmpty()) {
            $rows->push(['Tidak ada data', '', '', '']);
        } else {
            $rows->push(['TOTAL', (int) $qtyCollection->sum('total_qty'), (float) $qtyCollection->sum('total_revenue'), '']);
        }
        $rows->push(['', '', '', '']);

        // ── Top by Revenue ────────────────────────────────────────────────
        $rows->push(['TOP PRODUK BERDASARKAN REVENUE', '', '', '']);
        $rows->push(['Produk', 'Pendapatan (Rp)', 'Qty Terjual', '']);
        $revCollection = collect($this->topByRevenue);
        foreach ($revCollection as $p) {
            $rows->push([$p->product_name ?? '-', (float) ($p->total_revenue ?? 0), (int) ($p->total_qty ?? 0), '']);
        }
        if ($revCollection->isEmpty()) {
            $rows->push(['Tidak ada data', '', '', '']);
        } else {
            $rows->push(['TOTAL', (float) $revCollection->sum('total_revenue'), (int) $revCollection->sum('total_qty'), '']);
        }
        $rows->push(['', '', '', '']);

        // ── Produk dari Pesanan Dibatalkan ────────────────────────────────
        $rows->push(['PRODUK DALAM PESANAN DIBATALKAN', '', '', '']);
        if ($this->cancelledSummary && $this->cancelledSummary->count > 0) {
            $rows->push([
                'Total Pesanan Dibatalkan',
                (int) $this->cancelledSummary->count . ' pesanan',
                'Nilai Hilang: Rp ' . number_format($this->cancelledSummary->total_amount, 0, ',', '.'),
                '',
            ]);
            $rows->push(['', '', '', '']);
            $rows->push(['Produk', 'Qty Batal', 'Nilai Hilang (Rp)', '']);
            $cancelledItemsCollection = collect($this->cancelledItems ?? []);
            foreach ($cancelledItemsCollection as $ci) {
                $rows->push([
                    $ci->product_name ?? '-',
                    (int)   ($ci->total_qty  ?? 0),
                    (float) ($ci->total_lost ?? 0),
                    '',
                ]);
            }
            if ($cancelledItemsCollection->isNotEmpty()) {
                $rows->push(['TOTAL HILANG', (int) $cancelledItemsCollection->sum('total_qty'), (float) $cancelledItemsCollection->sum('total_lost'), '']);
            }
        } else {
            $rows->push(['Tidak ada pesanan yang dibatalkan dalam periode ini.', '', '', '']);
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

    public function title(): string { return 'Laporan Produk'; }
}