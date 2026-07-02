<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DraftReady extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string  $name,
        public string  $txId,
        public string  $asset,
        public string  $client,
        public ?string $contactName,
        public ?string $draftSubject,
        public ?int    $confidence,
        public bool    $fastTrack,
    ) {}

    public function build(): static
    {
        return $this
            ->subject("AVA draft ready — {$this->asset}")
            ->view('emails.draft-ready');
    }
}
