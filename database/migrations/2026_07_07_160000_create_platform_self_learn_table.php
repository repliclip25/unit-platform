<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_self_learn', function (Blueprint $table) {
            $table->id();
            $table->string('page_key')->unique();
            $table->string('title');
            $table->text('body');
            $table->boolean('active')->default(true);
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        // Seed existing hardcoded entries
        DB::table('platform_self_learn')->insert([
            [
                'page_key'   => 'admin.qa',
                'title'      => 'QA Center',
                'body'       => 'This page gives you platform-wide health visibility — AI engine status, queue state, Stripe connectivity, Gmail watches, and the worker marketplace. Use it to diagnose pipeline issues, publish workers, and monitor platform health. Stuck transactions and pending drafts shown here are across all tenants.',
                'active'     => true,
                'version'    => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'page_key'   => 'admin.desk-cards',
                'title'      => 'How desk cards work',
                'body'       => 'Pipeline cards are declared by each WorkerContract::deskCards() and appear here only when at least one matching worker is deployed. Toggling \'Active\' hides the card from all users immediately. Toggling \'Default on\' affects new users only — existing users keep their own saved preference.',
                'active'     => true,
                'version'    => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_self_learn');
    }
};
