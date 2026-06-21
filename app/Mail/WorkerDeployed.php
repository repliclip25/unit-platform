<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WorkerDeployed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $workerName,
        public string $workerSlug,
        public string $workerDesc,
        public int    $deploymentId,
        public string $trialEndsAt,
    ) {}

    public function build(): static
    {
        return $this
            ->subject("{$this->workerName} is now live on UNIT")
            ->view('emails.worker-deployed');
    }
}
