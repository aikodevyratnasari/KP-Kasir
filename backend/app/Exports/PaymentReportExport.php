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
        private $unpaidOrders,   // Order collection belum memiliki payment
        private string $from,
        private string $to,
    ) {}

    public function collection()
    {
        $rows          = collect();
        $payCollection = collect($this->payments);
        $unpaid        = collect($this->unpaidOrders);

        // ── Header ───────────────────────────────────────────────────────
        $rows->push(['LAPORAN PEMBAYARAN', '', '', '', '', '', '']);
        $rows->push(['Periode', $this->from . ' s/d ' . $this->to, '', '', '', '', '']);
        $rows->push(['', '', '', '', '', '', '']);

        // ── Ringkasan per Status ──────────────────────────────────────────
        $rows->push(['RINGKASAN PER STATUS', '', '', '', '', '', '']);
        $rows->push(['Status', 'Jumlah', 'Total (Rp)', '', '', '', '']);

        $statusLabels = [
            'paid'     => 'Lunas',
            'refunded' => 'Refund',
            'pending'  => 'Pending (Payment)',
        ];
        $byStatus = $payCollection->groupBy('status');

        foreach (['paid', 'refunded', 'pending'] as $key) {
            $group = $byStatus->get($key, collect());
            if ($group->isNotEmpty()) {
                $rows->push([
                    $statusLabels[$key],
                    $group->count(),
                    (float) $group->sum('amount'),
                    '', '', '', '',
                ]);
            }
        }

        // Pesanan belum bayar sama sekali (tidak ada record payment)
        if ($unpaid->isNotEmpty()) {
            $rows->push([
                'Belum Bayar (Pesanan Aktif)',
                $unpaid->count(),
                (float) $unpaid->sum('total_amount'),
                '', '', '', '',
            ]);
        }

        // Total seluruh transaksi (payment records)
        $rows->push([
            'TOTAL SELURUH TRANSAKSI (PAYMENT)',
            $payCollection->count(),
            (float) $payCollection->sum('amount'),
            '', '', '', '',
        ]);
        $rows->push([
            'TOTAL LUNAS',
            $byStatus->get('paid', collect())->count(),
            (float) $byStatus->get('paid', collect())->sum('amount'),
            '', '', '', '',
        ]);
        $rows->push(['', '', '', '', '', '', '']);

        // ── BAGIAN A: Transaksi yang sudah ada payment ────────────────────
        $rows->push(['A. TRANSAKSI PEMBAYARAN', '', '', '', '', '', '']);
        $rows->push(['No. Pesanan', 'Pelanggan', 'Kasir', 'Metode', 'Jumlah (Rp)', 'Status', 'Waktu']);

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

        // Sub-total per status di bagian A
        if ($payCollection->isNotEmpty()) {
            $rows->push(['', '', '', '', '', '', '']);
            foreach (['paid', 'refunded', 'pending'] as $key) {
                $group = $byStatus->get($key, collect());
                if ($group->isNotEmpty()) {
                    $rows->push([
                        'Sub-total ' . $statusLabels[$key],
                        $group->count() . ' transaksi',
                        (float) $group->sum('amount'),
                        '', '', '', '',
                    ]);
                }
            }
        }

        if ($payCollection->isEmpty()) {
            $rows->push(['Tidak ada data transaksi pembayaran', '', '', '', '', '', '']);
        }
        $rows->push(['', '', '', '', '', '', '']);

        // ── BAGIAN B: Pesanan belum bayar ─────────────────────────────────
        $rows->push(['B. PESANAN BELUM BAYAR (AKTIF)', '', '', '', '', '', '']);
        $rows->push(['No. Pesanan', 'Pelanggan', 'Kasir', 'Tipe', 'Total (Rp)', 'Status Pesanan', 'Dibuat']);

        foreach ($unpaid as $o) {
            $rows->push([
                $o->order_number           ?? '-',
                $o->customer_name          ?? '-',
                $o->cashier?->name         ?? '-',
                ucwords(str_replace('_', ' ', $o->order_type ?? '')),
                (float) $o->total_amount,
                ucfirst($o->status),
                $o->created_at->format('d M Y H:i'),
            ]);
        }

        if ($unpaid->isEmpty()) {
            $rows->push(['Tidak ada pesanan aktif yang belum dibayar', '', '', '', '', '', '']);
        } else {
            $rows->push([
                'TOTAL BELUM BAYAR',
                $unpaid->count() . ' pesanan',
                (float) $unpaid->sum('total_amount'),
                '', '', '', '',
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

    public function title(): string { return 'Laporan Pembayaran'; }
}