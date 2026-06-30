<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminPromptController extends Controller
{
    public function promptRegistry(): array
    {
        return [
            'Marketplace' => [
                'worker_request_system' => [
                    'label' => 'Worker Request — System Prompt',
                    'desc'  => 'Role and tone instructions for the AI when reviewing a "Request a Worker" form submission.',
                    'placeholders' => [],
                ],
                'worker_request_user' => [
                    'label' => 'Worker Request — Follow-up Email Body',
                    'desc'  => 'Template for generating the follow-up email sent to the requester. Use placeholders below.',
                    'placeholders' => ['{name}','{company}','{role}','{org}','{current_process}','{pain_points}','{volume}'],
                ],
            ],
            'Blog' => [
                'blog_rewrite_system' => [
                    'label' => 'Blog Rewrite — System Prompt',
                    'desc'  => 'Role instructions for the AI when rewriting a blog post draft.',
                    'placeholders' => [],
                ],
                'blog_rewrite_user' => [
                    'label' => 'Blog Rewrite — User Prompt',
                    'desc'  => 'Instructions for how to rewrite a draft. Use {draft} for the raw draft content.',
                    'placeholders' => ['{draft}', '{title}', '{tag}'],
                ],
            ],
            'Newsletter' => [
                'newsletter_signup_system' => [
                    'label' => 'Newsletter Signup — System Prompt',
                    'desc'  => 'Role instructions for the AI when generating a welcome/confirmation message for a new subscriber.',
                    'placeholders' => [],
                ],
                'newsletter_signup_user' => [
                    'label' => 'Newsletter Signup — Confirmation Email',
                    'desc'  => 'Template for the confirmation email sent after signup. Use {email} for the subscriber email.',
                    'placeholders' => ['{email}'],
                ],
            ],
            'Feedback & Support' => [
                'feedback_response_system' => [
                    'label' => 'Feedback Response — System Prompt',
                    'desc'  => 'Role instructions for responding to user feedback or support requests.',
                    'placeholders' => [],
                ],
                'feedback_response_user' => [
                    'label' => 'Feedback Response — Reply Body',
                    'desc'  => 'Template for AI-generated replies to feedback submissions.',
                    'placeholders' => ['{name}', '{message}', '{category}'],
                ],
            ],
            'Comments' => [
                'comment_moderation_system' => [
                    'label' => 'Comment Moderation — System Prompt',
                    'desc'  => 'Instructions for the AI when deciding whether a comment should be approved, flagged, or rejected.',
                    'placeholders' => [],
                ],
                'comment_moderation_user' => [
                    'label' => 'Comment Moderation — Review Prompt',
                    'desc'  => 'Prompt template for reviewing a submitted comment. Use {comment} and {context}.',
                    'placeholders' => ['{comment}', '{context}'],
                ],
            ],
            'Platform Identity' => [
                'sender_name' => [
                    'label' => 'Email Sender Name',
                    'desc'  => 'The "From" name shown on all outbound UNIT emails (worker request follow-ups, notifications, alerts). No AI involved — plain text only.',
                    'placeholders' => [],
                ],
            ],
        ];
    }

    public function index()
    {
        $registry = $this->promptRegistry();
        $allKeys  = collect($registry)->flatMap(fn($g) => array_keys($g))->all();
        $rows     = Cache::remember('platform_settings_all', 300, fn() =>
            DB::table('platform_settings')->whereIn('key', $allKeys)->get()->keyBy('key')
        );
        $defaults = $this->defaults();

        return view('admin.prompts.index', compact('registry', 'rows', 'defaults'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'key'   => 'required|string',
            'value' => 'required|string|max:12000',
        ]);

        $allKeys = collect($this->promptRegistry())->flatMap(fn($g) => array_keys($g))->all();
        if (!in_array($request->key, $allKeys)) {
            abort(422, 'Unknown prompt key.');
        }

        DB::table('platform_settings')->updateOrInsert(
            ['key' => $request->key],
            ['value' => $request->value, 'updated_at' => now(), 'created_at' => now()]
        );

        Cache::forget('platform_settings_all');

        return back()->with('saved', $request->key);
    }

    public function reset(Request $request)
    {
        $request->validate(['key' => 'required|string']);
        DB::table('platform_settings')->where('key', $request->key)->delete();
        Cache::forget('platform_settings_all');
        return back()->with('reset', $request->key);
    }

    public function defaults(): array
    {
        return [
            'worker_request_system' =>
                'You are the UNIT team — a company that builds purpose-built AI workers for specific business workflows. You review incoming worker requests and write intelligent follow-up emails. Your tone is warm, direct, and expert. You read every submission carefully and tailor your questions to exactly what this person described — never generic.',

            'worker_request_user' => <<<'P'
A person submitted a request asking us to build a custom AI worker. Read their submission carefully and write the BODY of a follow-up email (no greeting line -- start after "Hi [Name],").

THEIR SUBMISSION:
Name: {name}
Company: {company}
Role: {role}
Org / Agency: {org}
Current process: {current_process}
What goes wrong: {pain_points}
Volume: {volume}

Write the email body in this structure:
1. One short paragraph (2-3 sentences) that summarizes their intent in your own words -- show you understood the problem and the goal, not just the mechanics. Do NOT copy or paraphrase their wording directly. Write it as if you were explaining their situation to a colleague: what are they trying to accomplish, and what is stopping them today. Do NOT use generic openers like "Thank you for your interest."
2. A short transition sentence leading into your questions (e.g. "To scope what a worker could do here, we have a few questions:")
3. A numbered list of 4-5 specific follow-up questions tailored to THEIR use case. Questions should dig into: what data or inputs the worker would read, what a finished output looks like, who reviews or approves before anything goes out, what tools or systems are already in place, and what the single biggest failure point is today. Reference their specific context, not generic examples. Do NOT ask about renewals if they did not mention renewals.
4. One closing sentence: "Reply to this email and we'll take it from there."

STRICT FORMATTING RULES:
- Never use em dashes (the long dash character). Use commas, colons, or periods instead.
- Never use the word "straightforward".
- Keep total under 320 words. Be specific. No pleasantries.
P,

            'blog_rewrite_system' =>
                'You are an expert content writer for UNIT, a platform for deploying purpose-built AI workers. You rewrite blog drafts into polished, well-structured articles. Your writing is clear, direct, and expert. You preserve the author\'s intent and facts but improve structure, flow, and sentence quality.',

            'blog_rewrite_user' => <<<'P'
Rewrite the following blog draft into a polished article. Keep all the key ideas and facts. Improve clarity, structure, and readability. Format the output in clean HTML suitable for a blog reader: use <h2> for section headings, <p> for paragraphs, <ul>/<li> for lists, and <blockquote> for pull quotes.

Post title: {title}
Category: {tag}

DRAFT:
{draft}

RULES:
- Do not change the core message or invent facts.
- Do not use em dashes. Use commas, colons, or periods.
- Output ONLY the HTML body content. No <html>, <body>, or wrapper tags.
- Aim for 600-900 words unless the draft is much shorter or longer.
P,

            'newsletter_signup_system' =>
                'You are UNIT, a platform for deploying AI workers. Write brief, warm, and professional subscriber confirmation messages.',

            'newsletter_signup_user' =>
                'Write a short 2-sentence confirmation message for a new blog subscriber. Their email is {email}. Tell them they are subscribed and that they will receive new posts when published. Warm but brief.',

            'feedback_response_system' =>
                'You are a UNIT team member responding to user feedback. Be warm, direct, and genuine. Acknowledge specifically what they said.',

            'feedback_response_user' => <<<'P'
Write a brief response to this feedback submission.

Name: {name}
Category: {category}
Message: {message}

Write 2-3 sentences. Acknowledge what they shared. Let them know it has been received and will be reviewed. Do not use em dashes.
P,

            'comment_moderation_system' =>
                'You are a content moderator for the UNIT blog. Review submitted comments and return a JSON object with: decision ("approve", "flag", or "reject") and reason (one sentence).',

            'comment_moderation_user' => <<<'P'
Review this comment and decide whether to approve, flag for review, or reject it.

Article context: {context}
Comment: {comment}

Return ONLY valid JSON: {"decision": "approve|flag|reject", "reason": "one sentence explanation"}
P,

            'sender_name' => 'Franklin at UNIT',
        ];
    }
}
