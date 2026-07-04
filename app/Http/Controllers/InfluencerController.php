<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AdminMessagingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class InfluencerController extends Controller
{
    public function apply()
    {
        return view('influencer.apply');
    }

    public function submitApplication(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'channel'       => 'required|string|max:100',
            'audience_size' => 'required|string|max:50',
            'niche'         => 'nullable|string|max:100',
            'utm_source'    => 'nullable|string|max:100',
        ]);

        // Generate a unique slug from name
        $base = strtolower(preg_replace('/[^a-z0-9]/i', '', $request->name));
        $slug = substr($base, 0, 20);
        $i    = 1;
        while (DB::table('influencers')->where('slug', $slug)->exists()) {
            $slug = substr($base, 0, 18) . $i++;
        }

        $exists = DB::table('influencers')->where('email', $request->email)->exists();
        if ($exists) {
            return back()->with('error', 'An application with this email already exists.')->withInput();
        }

        DB::table('influencers')->insert([
            'name'            => $request->name,
            'email'           => $request->email,
            'slug'            => $slug,
            'channel'         => $request->channel,
            'audience_size'   => $request->audience_size,
            'niche'           => $request->niche,
            'utm_source'      => $request->utm_source ?? session('utm_source'),
            'status'          => 'pending',
            'tier'            => 'starter',
            'commission_rate' => 0.20,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Application received confirmation
        try {
            $tpl = AdminMessagingController::getTemplate('influencer_application_received');
            if ($tpl) {
                $body    = str_replace(['{name}', '{app_url}'], [$request->name, config('app.url')], $tpl->body);
                $subject = str_replace(['{name}', '{app_url}'], [$request->name, config('app.url')], $tpl->subject);
                Mail::raw($body, fn($m) => $m->to($request->email, $request->name)->subject($subject)->replyTo(config('services.unit.noreply_email'), $tpl->from_name));
            }
        } catch (\Throwable $e) {
            Log::error('Influencer application email failed', ['error' => $e->getMessage()]);
        }

        return redirect()->route('influencer.apply')->with('success', 'Application received! We\'ll review and get back to you within 2 business days.');
    }
}
