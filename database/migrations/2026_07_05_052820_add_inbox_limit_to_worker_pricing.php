<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_pricing', function (Blueprint $table) {
            // null = unlimited inboxes; integer = max connected inboxes on this plan
            $table->unsignedSmallInteger('inbox_limit')->nullable()->after('transaction_limit');
        });

        // Backfill existing AVA rows
        DB::table('worker_pricing')->where('worker_slug', 'ava')->get()->each(function ($row) {
            $limit = match($row->plan_slug) {
                'starter'    => 1,
                'pro'        => 5,
                'enterprise' => null,
                default      => null,
            };
            DB::table('worker_pricing')->where('id', $row->id)->update(['inbox_limit' => $limit]);
        });
    }

    public function down(): void
    {
        Schema::table('worker_pricing', function (Blueprint $table) {
            $table->dropColumn('inbox_limit');
        });
    }
};
