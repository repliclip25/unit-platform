<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class WorkerTemplateController extends Controller
{
    // ── Worker-scoped /workers/{slug}/templates routes ────────────────────────

    public function workerIndex(string $slug)
    {
        $dep = DB::table('worker_deployments')->where('user_id', auth()->id())
            ->where(fn($q) => $q->where('worker_slug', $slug)->when(is_numeric($slug), fn($q2) => $q2->orWhere('id', (int)$slug)))
            ->firstOrFail();
        $id        = $dep->id;
        $userId    = auth()->id();
        $templates = DB::table('email_templates')->where(function ($q) use ($userId, $dep) {
            $q->where(function ($q2) use ($userId, $dep) {
                $q2->where('user_id', $userId)->where('worker_slug', $dep->worker_slug);
            })->orWhere(function ($q2) use ($dep) {
                $q2->whereNull('user_id')->where('worker_slug', $dep->worker_slug);
            });
        })->orderBy('category')->get();
        return view('dashboard.worker-templates', compact('dep', 'templates'));
    }

    public function workerStore(int $id, Request $request)
    {
        $dep = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['name' => 'required', 'category' => 'required', 'subject_template' => 'required', 'body_template' => 'required']);
        DB::table('email_templates')->insert(['user_id' => auth()->id(), 'worker_slug' => $dep->worker_slug, 'name' => $request->name, 'category' => $request->category, 'tone' => $request->tone ?? 'Professional, concise', 'subject_template' => $request->subject_template, 'body_template' => $request->body_template, 'approval_required' => $request->boolean('approval_required'), 'is_default' => false, 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Template saved.');
    }

    public function workerDestroy(int $id, int $tid)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        DB::table('email_templates')->where('id', $tid)->where('user_id', auth()->id())->delete();
        return back()->with('success', 'Template removed.');
    }

    public function workerFork(int $id, int $tid)
    {
        $dep      = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $userId   = auth()->id();
        $original = DB::table('email_templates')->where('id', $tid)->whereNull('user_id')->firstOrFail();

        // If already forked, return the existing copy so the edit modal opens
        $existing = DB::table('email_templates')
            ->where('user_id', $userId)
            ->where('worker_slug', $dep->worker_slug)
            ->where('forked_from', $tid)
            ->first();

        if ($existing) {
            return response()->json(['template' => $existing]);
        }

        $newId = DB::table('email_templates')->insertGetId([
            'user_id'           => $userId,
            'worker_slug'       => $dep->worker_slug,
            'name'              => $original->name,
            'category'          => $original->category,
            'tone'              => $original->tone,
            'subject_template'  => $original->subject_template,
            'body_template'     => $original->body_template,
            'approval_required' => $original->approval_required,
            'is_default'        => false,
            'active'            => true,
            'forked_from'       => $tid,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $newTemplate = DB::table('email_templates')->where('id', $newId)->first();
        return response()->json(['template' => $newTemplate]);
    }

    public function workerUpdate(int $id, int $tid, Request $request)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate([
            'name'             => 'required|string|max:255',
            'tone'             => 'nullable|string|max:100',
            'subject_template' => 'required|string',
            'body_template'    => 'required|string',
            'approval_required'=> 'nullable|boolean',
        ]);
        DB::table('email_templates')
            ->where('id', $tid)
            ->where('user_id', auth()->id())
            ->update([
                'name'             => $request->name,
                'tone'             => $request->tone ?? 'Professional, concise',
                'subject_template' => $request->subject_template,
                'body_template'    => $request->body_template,
                'approval_required'=> $request->boolean('approval_required'),
                'updated_at'       => now(),
            ]);
        return back()->with('success', 'Template updated.');
    }

    public function workerTest(int $id, int $tid, Request $request)
    {
        $dep    = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $userId = auth()->id();
        $user   = auth()->user();

        // Tenant templates first, platform defaults as fallback
        $template = DB::table('email_templates')
            ->where('id', $tid)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->first();

        if (!$template) {
            return back()->with('error', 'Template not found.');
        }

        // Fill dummy variables so the test looks realistic
        $vars = [
            '{{contact_first_name}}' => $user->name,
            '{{asset}}'              => 'example.com',
            '{{client}}'             => 'Acme Corp',
            '{{due_date}}'           => now()->addDays(14)->format('M j, Y'),
            '{{sender_name}}'        => $user->name,
            '{{renewal_price}}'      => '$199.00',
            '{{days_until_expiry}}'  => '14',
        ];

        $subject = str_replace(array_keys($vars), array_values($vars), $template->subject_template);
        $body    = str_replace(array_keys($vars), array_values($vars), $template->body_template);

        // Try sending via tenant's connected Gmail; fall back to SMTP
        $credential = $dep->credential_id
            ? DB::table('user_gmail_credentials')->where('id', $dep->credential_id)->first()
            : null;

        try {
            if ($credential?->refresh_token) {
                $gmail = new \App\Platform\Services\Gmail\GmailService($credential);
                $gmail->sendEmail($user->email, '[TEST] ' . $subject, $body);
            } else {
                Mail::raw($body, function ($msg) use ($user, $subject) {
                    $msg->to($user->email)->subject('[TEST] ' . $subject);
                });
            }
            return back()->with('success', "Test email sent to {$user->email}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Send failed: ' . $e->getMessage());
        }
    }
}
