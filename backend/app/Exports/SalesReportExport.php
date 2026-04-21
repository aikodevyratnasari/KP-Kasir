<?php

namespace App\Exports;

use Carbon\Carbon;
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
        private $byStatus,          // semua status termasuk cancelled & pending
        private float $totalSales,
        private int $totalOrders,
        private float $avgTransaction,
        private string $from,
        private string $to,
        private $byPaymentStatus = null,  // ringkasan per status payment (paid/refunded/pending)
    ) {}

    public function collection()
    {
        $rows = collect();

        // ── Header ───────────────────────────────────────────────────────
        $rows->push(['LAPORAN PENJUALAN', '', '']);
        $rows->push(['Periode', $this->from . ' s/d ' . $this->to, '']);
        $rows->push(['', '', '']);

        // ── Ringkasan Utama ───────────────────────────────────────────────
        $rows->push(['RINGKASAN', '', '']);
        $rows->push(['Total Penjualan (Lunas)', $this->totalSales, '']);
        $rows->push(['Total Pesanan Aktif (excl. cancelled)', $this->totalOrders, '']);
        $rows->push(['Rata-rata per Transaksi', $this->avgTransaction, '']);
        $rows->push(['', '', '']);

        // ── Ringkasan Status Payment ──────────────────────────────────────
        if ($this->byPaymentStatus) {
            $payStatus = collect($this->byPaymentStatus);
            $rows->push(['RINGKASAN STATUS PEMBAYARAN', '', '']);
            $rows->push(['Status Pembayaran', 'Jumlah Transaksi', 'Total (Rp)']);
            $statusLabels = ['paid' => 'Lunas', 'refunded' => 'Refund', 'pending' => 'Pending'];
            foreach (['paid', 'refunded', 'pending'] as $key) {
                $s = $payStatus->where('status', $key)->first();
                if ($s) {
                    $rows->push([$statusLabels[$key], (int) $s->count, (float) $s->total]);
                }
            }
            $rows->push(['', '', '']);
        }

        // ── Rekap Status Pesanan (semua status) ──────────────────────────
        $rows->push(['REKAP STATUS PESANAN', '', '']);
        $rows->push(['Status', 'Jumlah Pesanan', 'Total Nilai (Rp)']);

        $statusLabels = [
            'completed' => 'Selesai',
            'ready'     => 'Siap',
            'cooking'   => 'Dimasak',
            'pending'   => 'Pending',
            'cancelled' => 'Dibatalkan',
        ];
        $statusMap = collect($this->byStatus)->keyBy('status');
        foreach (['completed', 'ready', 'cooking', 'pending', 'cancelled'] as $key) {
            $s = $statusMap->get($key);
            $rows->push([
                $statusLabels[$key] ?? ucfirst($key),
                (int) ($s?->count ?? 0),
                (float) ($s?->total ?? 0),
            ]);
        }
        // Tambahkan status lain yang mungkin ada di luar daftar
        foreach (collect($this->byStatus) as $s) {
            if (!in_array($s->status, ['completed', 'ready', 'cooking', 'pending', 'cancelled'])) {
                $rows->push([ucfirst($s->status), (int) $s->count, (float) $s->total]);
            }
        }
        $rows->push([
            'TOTAL SEMUA PESANAN',
            collect($this->byStatus)->sum('count'),
            collect($this->byStatus)->sum('total'),
        ]);
        $rows->push(['', '', '']);

        // ── Tren Harian ───────────────────────────────────────────────────
        $rows->push(['TREN HARIAN (dari pembayaran lunas)', '', '']);
        $rows->push(['Tanggal', 'Total Penjualan (Rp)', 'Jumlah Transaksi']);
        $dailyCollection = collect($this->dailyTrend);
        foreach ($dailyCollection as $d) {
            $rows->push([
                Carbon::parse($d->date)->format('d M Y'),
                (float) $d->total,
                (int)   $d->count,
            ]);
        }
        if ($dailyCollection->isEmpty()) {
            $rows->push(['Tidak ada data', '', '']);
        } else {
            $rows->push([
                'TOTAL',
                (float) $dailyCollection->sum('total'),
                (int)   $dailyCollection->sum('count'),
            ]);
        }
        $rows->push(['', '', '']);

        // ── Per Metode Pembayaran ─────────────────────────────────────────
        $rows->push(['PER METODE PEMBAYARAN (hanya lunas)', '', '']);
        $rows->push(['Metode', 'Total (Rp)', 'Jumlah Transaksi']);
        $methodCollection = collect($this->byPaymentMethod);
        foreach ($methodCollection as $m) {
            $rows->push([ucfirst($m->payment_method), (float) $m->total, (int) $m->count]);
        }
        if ($methodCollection->isEmpty()) {
            $rows->push(['Tidak ada data', '', '']);
        }
        $rows->push(['', '', '']);

        // ── Per Tipe Pesanan ──────────────────────────────────────────────
        $rows->push(['PER TIPE PESANAN (excl. cancelled)', '', '']);
        $rows->push(['Tipe', 'Total (Rp)', 'Jumlah Pesanan']);
        $typeCollection = collect($this->byOrderType);
        foreach ($typeCollection as $t) {
            $rows->push([
                ucwords(str_replace('_', ' ', $t->order_type)),
                (float) $t->total,
                (int)   $t->count,
            ]);
        }
        if ($typeCollection->isEmpty()) {
            $rows->push(['Tidak ada data', '', '']);
        }

        return $rows;
    }

    public function headings(): array { return []; }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }

    public function title(): string { return 'Laporan Penjualan'; }
}