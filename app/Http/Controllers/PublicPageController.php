<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicPageController extends Controller
{
    public function about()       { return view('public.about'); }
    public function privacy()     { return view('public.privacy'); }
    public function terms()       { return view('public.terms'); }

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
