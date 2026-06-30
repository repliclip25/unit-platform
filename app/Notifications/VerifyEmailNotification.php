<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        // Force APP_URL as the base — prevents MAMP/Apache port leaking into the link
        $appUrl = rtrim(config('app.url'), '/');
        $parsed = parse_url($url);
        $url    = $appUrl . ($parsed['path'] ?? '') . (isset($parsed['query']) ? '?' . $parsed['query'] : '');

        return (new MailMessage)
            ->subject('Verify your UNIT account')
            ->view('emails.verify-email', ['url' => $url, 'name' => $notifiable->name]);
    }
}
