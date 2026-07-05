<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountDeletionController extends Controller
{
    // Token is valid for 72 hours
    private const TOKEN_TTL_HOURS = 72;

    public function confirm(string $token)
    {
        $user = $this->resolveToken($token);

        if (!$user) {
            return view('auth.delete-confirm', ['invalid' => true]);
        }

        return view('auth.delete-confirm', [
            'invalid' => false,
            'token'   => $token,
            'name'    => $user->name,
            'email'   => $user->email,
        ]);
    }

    public function execute(Request $request, string $token)
    {
        $user = $this->resolveToken($token);

        if (!$user) {
            return redirect('/')->with('status', 'This deletion link has expired or already been used.');
        }

        // Hard-delete using the same nuke logic as self-delete
        ProfileController::hardDelete($user->id);

        Log::info('[AccountDeletion] Admin-initiated deletion completed', [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);

        return redirect('/')->with('status', 'Your UNIT account has been permanently deleted. We\'re sorry to see you go.');
    }

    private function resolveToken(string $token): ?object
    {
        $user = DB::table('users')
            ->where('deletion_token', $token)
            ->whereNotNull('admin_deletion_requested_at')
            ->first();

        if (!$user) return null;

        // Check token hasn't expired
        $requestedAt = \Carbon\Carbon::parse($user->admin_deletion_requested_at);
        if ($requestedAt->diffInHours(now()) > self::TOKEN_TTL_HOURS) {
            // Expired — clear the token so it can't be retried
            DB::table('users')->where('id', $user->id)->update([
                'deletion_token'              => null,
                'admin_deletion_requested_at' => null,
            ]);
            return null;
        }

        return $user;
    }
}
