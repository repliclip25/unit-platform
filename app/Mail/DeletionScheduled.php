<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DeletionScheduled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $deletionDate,
    ) {}

    public function build(): static
    {
        return $this
            ->subject('Your UNIT account is scheduled for deletion')
            ->view('emails.deletion-scheduled');
    }
}
