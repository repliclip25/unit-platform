<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('platform_email_templates')->where('key', 'memory_access_invite')->exists()) return;

        DB::table('platform_email_templates')->insert([
            'key'               => 'memory_access_invite',
            'sequence'          => 'platform',
            'audience'          => 'tenant',
            'worker_slug'       => null,
            'label'             => 'Memory Access — Invite',
            'description'       => 'Sent when a user grants another user access to their worker memory.',
            'trigger_condition' => 'on_memory_access_invite',
            'day_offset'        => null,
            'delay_hours'       => null,
            'trigger_state'     => null,
            'subject'           => '{owner_name} shared their {worker_name} memory with you',
            'body'              => "Hi {name},\n\n{owner_name} has invited you to collaborate on their {worker_name} worker memory on UNIT.\n\nWhat you can do:\n{permissions_list}\n\nThis is scoped to {owner_name}'s {worker_name} deployment only. You cannot delete any of their records.\n\nAccept the invitation:\n{accept_url}\n\nThis link expires in 72 hours. If you did not expect this invitation, you can ignore it.\n\nFranklin at UNIT",
            'from_name'         => 'Franklin at UNIT',
            'active'            => true,
            'sort_order'        => 20,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('platform_email_templates')->where('key', 'memory_access_invite')->delete();
    }
};
