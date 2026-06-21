<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GmailConnected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $name, public string $gmailAddress) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Gmail Connected — UNIT is now monitoring your inbox');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.gmail-connected');
    }

    public function attachments(): array { return []; }
}
