<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $reportType,
        public string $dateFrom,   // $from adalah reserved property Mailable (sender address) — jangan pakai $from
        public string $dateTo,     // $to   adalah reserved property Mailable (recipients)      — jangan pakai $to
        public string $filePath,
        public string $fileName,
    ) {}

    public function envelope(): Envelope
    {
        $labels = [
            'sales'    => 'Laporan Penjualan',
            'products' => 'Laporan Produk',
            'revenue'  => 'Analitik Revenue',
            'cashiers' => 'Laporan Kasir',
        ];

        return new Envelope(
            subject: ($labels[$this->reportType] ?? 'Laporan')
                . ' · ' . $this->dateFrom . ' s/d ' . $this->dateTo,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.report', with: [
            'reportType' => $this->reportType,
            'dateFrom'   => $this->dateFrom,
            'dateTo'     => $this->dateTo,
        ]);
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)->as($this->fileName),
        ];
    }
}