<?php

namespace App\Jobs;

use App\Http\Controllers\AdminPromptController;
use App\Platform\Services\PlatformClaude;
use App\Platform\Services\UnitNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WorkerRequestFollowupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function backoff(): array
    {
        return [60, 180];
    }

    public function __construct(
        private readonly int   $requestId,
        private readonly array $data,
    ) {}

    public function handle(): void
    {
        $followup = $this->generateFollowup();

        DB::table('worker_requests')->where('id', $this->requestId)->update([
            'ai_followup' => $followup,
            'status'      => 'contacted',
            'updated_at'  => now(),
        ]);

        $this->sendFollowupEmail($followup);
        $this->notifyAdmin($followup);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('[WorkerRequestFollowupJob] failed', [
            'request_id' => $this->requestId,
            'error'      => $e->getMessage(),
        ]);
        UnitNotifier::adminAlert(
            'Worker Request Followup Failed',
            "Request #{$this->requestId} from {$this->data['name']} ({$this->data['email']}) failed to generate a follow-up.\nError: {$e->getMessage()}"
        );
    }

    private function generateFollowup(): string
    {
        try {
            $settings = DB::table('platform_settings')
                ->whereIn('key', ['worker_request_system', 'worker_request_user'])
                ->get()->keyBy('key');

            $defaults = (new AdminPromptController())->defaults();

            $system = $settings->has('worker_request_system')
                ? $settings->get('worker_request_system')->value
                : $defaults['worker_request_system'];

            $userTemplate = $settings->has('worker_request_user')
                ? $settings->get('worker_request_user')->value
                : $defaults['worker_request_user'];

            $prompt = str_replace(
                ['{name}', '{company}', '{role}', '{org}', '{current_process}', '{pain_points}', '{volume}'],
                [
                    $this->data['name']            ?? '',
                    $this->data['company']         ?? '',
                    $this->data['role']            ?? '',
                    $this->data['org']             ?? '',
                    $this->data['current_process'] ?? '',
                    $this->data['pain_points']     ?? '',
                    $this->data['volume']          ?? '',
                ],
                $userTemplate
            );

            $platform = new PlatformClaude();
            return $platform->ask($system, $prompt, 900, 'worker_request_followup', 'public:worker_request');
        } catch (\Throwable $e) {
            Log::warning('[WorkerRequestFollowupJob] AI failed, using fallback', ['error' => $e->getMessage()]);
            $process = $this->data['current_process'] ?? 'your workflow';
            return "We've reviewed what you shared about {$process} and have a few questions before we scope anything.\n\n1. What does the input look like — an email, a form submission, a file, or something else?\n2. What does a completed output look like — a draft, a filed document, a sent message?\n3. Who reviews or approves the output before it goes anywhere?\n4. What tools or systems are already in place that a worker would need to read from or write to?\n5. What's the single biggest failure point in how this works today?\n\nReply to this email and we'll take it from there.";
        }
    }

    private function sendFollowupEmail(string $body): void
    {
        try {
            $name      = $this->data['name'] ?? 'there';
            $email     = $this->data['email'] ?? null;
            $firstName = explode(' ', trim($name))[0];

            if (!$email) return;

            $senderName = DB::table('platform_settings')->where('key', 'sender_name')->value('value') ?? 'Franklin at UNIT';

            $lines    = explode("\n", $body);
            $htmlBody = '';
            $inList   = false;
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') { if ($inList) { $htmlBody .= '</ol>'; $inList = false; } continue; }
                if (preg_match('/^\d+\.\s+(.+)$/', $line, $m)) {
                    if (!$inList) { $htmlBody .= '<ol style="margin:16px 0 16px 20px;padding:0">'; $inList = true; }
                    $htmlBody .= '<li style="margin-bottom:10px;color:#1a1a1a;font-size:15px;line-height:1.7">' . e($m[1]) . '</li>';
                } else {
                    if ($inList) { $htmlBody .= '</ol>'; $inList = false; }
                    $htmlBody .= '<p style="margin:0 0 14px;color:#1a1a1a;font-size:15px;line-height:1.75">' . e($line) . '</p>';
                }
            }
            if ($inList) $htmlBody .= '</ol>';

            Mail::send([], [], function ($m) use ($name, $email, $firstName, $htmlBody, $senderName) {
                $m->to($email, $name)
                  ->from(config('services.unit.noreply_email'), $senderName)
                  ->subject('Re: Your worker request — a few questions from us')
                  ->html("<!DOCTYPE html><html><body style='font-family:Inter,Arial,sans-serif;background:#f4f4f2;margin:0;padding:40px 20px'>
<div style='max-width:580px;margin:0 auto'>
  <div style='background:#0a0a12;border-radius:12px 12px 0 0;padding:22px 32px'>
    <span style='font-family:Arial,sans-serif;font-weight:800;font-size:18px;color:#ffffff;letter-spacing:-0.5px'>UNIT</span>
  </div>
  <div style='background:#ffffff;padding:36px 32px;border-left:1px solid #e2e2e0;border-right:1px solid #e2e2e0'>
    <p style='margin:0 0 20px;color:#1a1a1a;font-size:15px;line-height:1.75'>Hi {$firstName},</p>
    {$htmlBody}
    <div style='margin-top:32px;padding-top:24px;border-top:1px solid #f0f0ee'>
      <p style='margin:0;color:#555555;font-size:14px'>— {$senderName}</p>
      <p style='margin:4px 0 0;color:#999999;font-size:12px'>UNIT &middot; " . config('services.unit.noreply_email') . "</p>
    </div>
  </div>
  <div style='background:#f9f9f7;border:1px solid #e2e2e0;border-top:none;border-radius:0 0 12px 12px;padding:14px 32px'>
    <p style='margin:0;color:#aaaaaa;font-size:12px'>You submitted a worker request at unit.report.</p>
  </div>
</div></body></html>");
            });
        } catch (\Throwable $e) {
            Log::warning('[WorkerRequestFollowupJob] email send failed', ['error' => $e->getMessage()]);
        }
    }

    private function notifyAdmin(string $followup): void
    {
        try {
            $d    = $this->data;
            $body = "New worker request from {$d['name']} ({$d['email']})\n\n"
                . "Company: {$d['company']}\nRole: {$d['role']}\nOrg: {$d['org']}\nVolume: {$d['volume']}\n\n"
                . "CURRENT PROCESS:\n{$d['current_process']}\n\nPAIN POINTS:\n{$d['pain_points']}\n\n"
                . "AI FOLLOW-UP SENT:\n{$followup}";

            Mail::send([], [], fn($m) => $m
                ->to(config('services.unit.admin_email'), config('services.unit.noreply_name') . ' Admin')
                ->from(config('services.unit.noreply_email'), config('services.unit.noreply_name') . ' System')
                ->subject("Worker Request: {$d['name']} — {$d['company']}")
                ->text($body)
            );
        } catch (\Throwable) {}
    }
}
