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
        private string $reportType,
        private string $from,
        private string $to,
        private string $filePath,
        private string $fileName,
    ) {}

    public function envelope(): Envelope
    {
        $labels = [
            'sales'   => 'Laporan Penjualan',
            'products'=> 'Laporan Produk',
            'revenue' => 'Analitik Revenue',
        ];

        return new Envelope(
            subject: ($labels[$this->reportType] ?? 'Laporan') . ' · ' . $this->from . ' s/d ' . $this->to,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.report', with: [
            'reportType' => $this->reportType,
            'from'       => $this->from,
            'to'         => $this->to,
        ]);
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)->as($this->fileName),
        ];
    }
}