<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AdminMessagingController;

class PublicPageController extends Controller
{
    public function about()       { return view('public.about'); }
    public function privacy()     { return view('public.privacy'); }
    public function terms()       { return view('public.terms'); }
    public function marketplace() { return view('public.marketplace'); }

    public function pricing()
    {
        $plans = DB::table('worker_pricing')->where('active', true)->orderBy('monthly_flat_rate')->get();
        return view('public.pricing', compact('plans'));
    }
    public function blog()
    {
        $dbPosts = DB::table('blog_posts')->where('status', 'published')->orderByDesc('created_at')->get();
        return view('public.blog.index', compact('dbPosts'));
    }

    public function blogPost(string $slug)
    {
        // Check DB first
        $dbPost = DB::table('blog_posts')->where('slug', $slug)->where('status', 'published')->first();
        if ($dbPost) {
            $post = $this->dbPostToArray($dbPost);
            return view('public.blog.show', compact('post'));
        }

        // Fall back to hardcoded posts
        $posts = $this->blogPostData();
        $post  = $posts[$slug] ?? null;
        if (!$post) abort(404);
        return view('public.blog.show', compact('post'));
    }

    private function dbPostToArray(object $row): array
    {
        $rawBody = $row->body;

        // Quill stores HTML — detect and pass through as a single html block
        if (str_starts_with(ltrim($rawBody), '<')) {
            $body = [['html', $rawBody]];
        } else {
            // Legacy markdown-lite parser
            $body   = [];
            $lines  = explode("\n", $rawBody);
            $buffer = '';

            $flush = function () use (&$buffer, &$body) {
                $t = trim($buffer);
                if ($t !== '') $body[] = ['p', $t];
                $buffer = '';
            };

            foreach ($lines as $line) {
                if (str_starts_with($line, '## ')) {
                    $flush();
                    $body[] = ['h2', ltrim(substr($line, 3))];
                } elseif (str_starts_with($line, '> ')) {
                    $flush();
                    $body[] = ['blockquote', ltrim(substr($line, 2))];
                } elseif (str_starts_with($line, '- ')) {
                    $flush();
                    $body[] = ['ul', [ltrim(substr($line, 2))]];
                } elseif (trim($line) === '') {
                    $flush();
                } else {
                    $buffer .= ($buffer ? ' ' : '') . $line;
                }
            }
            $flush();
        }

        return [
            'title'       => $row->title,
            'tag'         => $row->tag,
            'excerpt'     => $row->excerpt,
            'date'        => \Carbon\Carbon::parse($row->created_at)->format('F Y'),
            'read'        => ceil(str_word_count(strip_tags($rawBody)) / 200) . ' min',
            'body'        => $body,
            'cover_image' => $row->cover_image ?? null,
        ];
    }

    public function requestWorker(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:120',
            'email'           => 'required|email|max:200',
            'company'         => 'nullable|string|max:200',
            'role'            => 'nullable|string|max:120',
            'org'             => 'nullable|string|max:200',
            'current_process' => 'required|string|min:20|max:3000',
            'pain_points'     => 'nullable|string|max:2000',
            'volume'          => 'nullable|string|max:100',
        ]);

        // Save the request
        $id = DB::table('worker_requests')->insertGetId([
            'name'            => $request->name,
            'email'           => $request->email,
            'company'         => $request->company,
            'role'            => $request->role,
            'org'             => $request->org,
            'current_process' => $request->current_process,
            'pain_points'     => $request->pain_points,
            'volume'          => $request->volume,
            'status'          => 'pending',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Dispatch async job — AI generation + email send happen in the background
        // so the form redirect is immediate instead of waiting 1–5 sec for Claude
        \App\Jobs\WorkerRequestFollowupJob::dispatch($id, $request->all())
            ->onQueue('default');

        return redirect(route('marketplace') . '#request-worker')->with('request_sent', true);
    }

    private function generateFollowup(array $data): string
    {
        try {
            $settingRow = DB::table('platform_settings')->whereIn('key', ['worker_request_system','worker_request_user'])->get()->keyBy('key');

            $promptCtrl = new AdminPromptController();
            $defaults   = $promptCtrl->defaults();

            $system = $settingRow->has('worker_request_system')
                ? $settingRow->get('worker_request_system')->value
                : $defaults['worker_request_system'];

            $name        = $data['name'] ?? '';
            $company     = $data['company'] ?? '';
            $role        = $data['role'] ?? '';
            $org         = $data['org'] ?? '';
            $process     = $data['current_process'] ?? '';
            $painPoints  = $data['pain_points'] ?? '';
            $volume      = $data['volume'] ?? '';

            $userTemplate = $settingRow->has('worker_request_user')
                ? $settingRow->get('worker_request_user')->value
                : $defaults['worker_request_user'];

            $prompt = str_replace(
                ['{name}','{company}','{role}','{org}','{current_process}','{pain_points}','{volume}'],
                [$name, $company, $role, $org, $process, $painPoints, $volume],
                $userTemplate
            );

            $platform = new \App\Platform\Services\PlatformClaude();
            return $platform->ask($system, $prompt, 900, 'worker_request_followup', 'public:worker_request');
        } catch (\Throwable $e) {
            $process = $data['current_process'] ?? 'your workflow';
            return "We've gone through what you shared about {$process} and we'd like to learn more before we scope anything.\n\nA few questions:\n\n1. What does the input look like — is it an email, a form submission, a file, or something else?\n2. What does a completed output look like — a draft, a filed document, a sent message?\n3. Who reviews or approves the output before it goes anywhere?\n4. What tools or systems are already in place that a worker would need to read from or write to?\n5. What's the single biggest failure point in how this works today?\n\nReply to this email and we'll take it from there.";
        }
    }

    private function sendFollowupEmail(string $name, string $email, string $aiBody): void
    {
        try {
            $tpl = AdminMessagingController::getTemplate('inbound_worker_request_prospect');
            $subject  = $tpl ? $tpl->subject : 'Re: Your worker request — a few questions from us';
            $fromName = $tpl ? $tpl->from_name : 'Franklin at UNIT';

            // Render plain-text AI body as simple HTML
            $firstName = explode(' ', trim($name))[0];
            $lines = explode("\n", $aiBody);
            $htmlBody = '';
            $inList = false;
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    if ($inList) { $htmlBody .= '</ol>'; $inList = false; }
                    continue;
                }
                if (preg_match('/^\d+\.\s+(.+)$/', $line, $m2)) {
                    if (!$inList) { $htmlBody .= '<ol style="margin:16px 0 16px 20px;padding:0">'; $inList = true; }
                    $htmlBody .= '<li style="margin-bottom:10px;color:#1a1a1a;font-size:15px;line-height:1.7">' . e($m2[1]) . '</li>';
                } else {
                    if ($inList) { $htmlBody .= '</ol>'; $inList = false; }
                    $htmlBody .= '<p style="margin:0 0 14px;color:#1a1a1a;font-size:15px;line-height:1.75">' . e($line) . '</p>';
                }
            }
            if ($inList) $htmlBody .= '</ol>';

            Mail::send([], [], function ($m) use ($name, $email, $subject, $fromName, $firstName, $htmlBody) {
                $m->to($email, $name)
                  ->from('hello@unit.report', $fromName)
                  ->subject($subject)
                  ->html("<!DOCTYPE html><html><body style='font-family:Inter,Arial,sans-serif;background:#f4f4f2;margin:0;padding:40px 20px'>
<div style='max-width:580px;margin:0 auto'>
  <div style='background:#0a0a12;border-radius:12px 12px 0 0;padding:22px 32px'>
    <span style='font-family:Arial,sans-serif;font-weight:800;font-size:18px;color:#ffffff;letter-spacing:-0.5px'>UNIT</span>
  </div>
  <div style='background:#ffffff;padding:36px 32px;border-left:1px solid #e2e2e0;border-right:1px solid #e2e2e0'>
    <p style='margin:0 0 20px;color:#1a1a1a;font-size:15px;line-height:1.75'>Hi {$firstName},</p>
    {$htmlBody}
    <div style='margin-top:32px;padding-top:24px;border-top:1px solid #f0f0ee'>
      <p style='margin:0;color:#555555;font-size:14px'>— Franklin</p>
      <p style='margin:4px 0 0;color:#999999;font-size:12px'>UNIT &middot; hello@unit.report</p>
    </div>
  </div>
  <div style='background:#f9f9f7;border:1px solid #e2e2e0;border-top:none;border-radius:0 0 12px 12px;padding:14px 32px'>
    <p style='margin:0;color:#aaaaaa;font-size:12px'>You submitted a worker request at unit.report.</p>
  </div>
</div></body></html>");
            });
        } catch (\Throwable) {
            // Non-fatal — request is already saved
        }
    }

    private function notifyAdmin(array $data, string $followup): void
    {
        try {
            $tpl = AdminMessagingController::getTemplate('inbound_worker_request_admin');
            $subject = str_replace(
                ['{name}', '{company}'],
                [$data['name'], $data['company'] ?? ''],
                $tpl->subject ?? "Worker Request: {$data['name']} — {$data['company']}"
            );

            $body = "New worker request from {$data['name']} ({$data['email']})\n\n";
            $body .= "Company: {$data['company']}\nRole: {$data['role']}\nOrg: {$data['org']}\n";
            $body .= "Volume: {$data['volume']}\n\n";
            $body .= "CURRENT PROCESS:\n{$data['current_process']}\n\n";
            $body .= "PAIN POINTS:\n{$data['pain_points']}\n\n";
            $body .= "AI FOLLOW-UP SENT:\n{$followup}";

            Mail::send([], [], function ($m) use ($body, $subject) {
                $m->to('hello@unit.report', 'UNIT Admin')
                  ->from('hello@unit.report', 'UNIT System')
                  ->subject($subject)
                  ->text($body);
            });
        } catch (\Throwable) {}
    }

    private function blogPostData(): array
    {
        return [
            'how-ava-processes-nycsca-renewal' => [
                'slug'    => 'how-ava-processes-nycsca-renewal',
                'tag'     => 'Automation · AVA',
                'title'   => 'How AVA processes a NYCSCA renewal from inbox to draft in under 5 minutes',
                'excerpt' => 'A step-by-step walkthrough of AVA\'s 8-stage pipeline — what each job does, how memory lookup works, and why the human-review gate matters.',
                'date'    => 'June 2026',
                'read'    => '8 min',
                'body'    => [
                    ['h2', 'The problem with renewal inboxes'],
                    ['p', 'A typical compliance coordinator\'s inbox on a Monday morning looks like this: 14 emails from NYCSCA, 6 from DOB, 3 from FDNY — all requiring action, all with different deadlines, all requiring you to cross-reference a license database you have open in another tab. This is the problem AVA was built to solve.'],
                    ['p', 'AVA doesn\'t just read the emails. It runs each one through an 8-stage pipeline that mirrors exactly what an experienced coordinator would do — but in under 5 minutes, with a full audit trail.'],
                    ['h2', 'Stage 1 & 2: Inject & Read'],
                    ['p', 'When a new email arrives in the monitored Gmail inbox, a webhook fires and AVA creates a transaction record. The first job, ReadEmailJob, sends the raw email to Claude with a structured prompt asking for a plain-English summary, the action needed, the due date, and an urgency rating. The output is JSON — structured data that every downstream stage can use.'],
                    ['h2', 'Stage 3: Classify'],
                    ['p', 'ClassifyEmailJob takes the read output and categorizes the email: renewal, new application, status inquiry, or not relevant. It also assigns priority (Low/Medium/High/Critical) and determines what type of response is needed. Emails that don\'t match any renewal pattern are marked as not relevant and no further AI runs.'],
                    ['h2', 'Stage 4: Memory Lookup'],
                    ['p', 'MemoryLookupJob searches the tenant\'s client and asset records for the relevant entity — matching by license number, company name, or contact email. This is what allows AVA to say "this is John D. at Acme Corp, License #2847, renewal due August 15th" instead of treating every email as if it\'s the first time.'],
                    ['h2', 'Stage 5 & 6: Log & Select Template'],
                    ['p', 'LogTransactionJob writes the renewal to the register. SelectTemplateJob picks the best-matching email template for this type of response — considering the agency, the category, and the tone setting the tenant configured.'],
                    ['h2', 'Stage 7: Draft'],
                    ['p', 'DraftEmailJob is where the actual writing happens. It combines the read output, classification, memory data, and selected template into a single Claude prompt. The output is a complete email body — ready for human review. Nothing is sent at this stage.'],
                    ['h2', 'Stage 8: Push to Gmail Draft'],
                    ['p', 'PushToGmailJob takes the draft and pushes it directly into Gmail Drafts via the Gmail API. The coordinator opens Gmail, sees a draft already written and addressed, reads it, makes any edits, and hits send. The whole pipeline — inbox to draft — takes between 90 seconds and 5 minutes depending on API response times.'],
                    ['h2', 'The human gate'],
                    ['p', 'AVA never sends email. This is deliberate. Every draft requires your explicit approval. The pipeline surfaces the work; you make the call. This isn\'t a limitation — it\'s the design. Compliance work has consequences, and the human should always be in the loop on what goes out.'],
                ],
            ],
        ];
    }
}
