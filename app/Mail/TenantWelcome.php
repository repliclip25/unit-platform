<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $name) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome to UNIT Universe — Your AI Workforce Platform');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.tenant-welcome');
    }

    public function attachments(): array { return []; }
}
