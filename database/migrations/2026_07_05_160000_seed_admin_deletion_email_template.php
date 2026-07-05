<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('platform_email_templates')->where('key', 'admin_deletion_request')->exists()) return;

        $base = DB::table('platform_email_templates')->max('sort_order') ?? 0;

        DB::table('platform_email_templates')->insert([
            'key'         => 'admin_deletion_request',
            'sequence'    => 'platform',
            'worker_slug' => null,
            'label'       => 'Admin — Account Deletion Request',
            'subject'     => 'Your UNIT account has been scheduled for deletion',
            'body'        =>
"Hi {name},

The UNIT platform has initiated the deletion of your account.

This is permanent and irreversible — it will remove everything: your workers, transactions, client memory, Gmail connection, and billing history.

To confirm and complete the deletion, click the link below:
{confirm_url}

This link expires in 72 hours.

If you believe this is a mistake, do not click the link — simply reply to this email and we'll resolve it immediately.

Franklin at UNIT",
            'from_name'   => 'Franklin at UNIT',
            'active'      => true,
            'sort_order'  => $base + 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('platform_email_templates')->where('key', 'admin_deletion_request')->delete();
    }
};
