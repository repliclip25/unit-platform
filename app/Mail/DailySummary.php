<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySummary extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $summarySubject,
        public string $body,
        public string $date,
        public int $total,
        public int $urgent,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->summarySubject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.daily-summary');
    }

    public function attachments(): array { return []; }
}
