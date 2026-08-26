<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountDeleted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $email,
    ) {}

    public function build(): static
    {
        return $this
            ->subject('Your ' . config('app.name') . ' account has been deleted')
            ->view('emails.account-deleted');
    }
}
