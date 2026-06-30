<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('worker_slug', 60)->nullable()->after('author');
        });

        Schema::create('platform_usage_events', function (Blueprint $table) {
            $table->id();
            $table->string('prompt_key', 100);          // e.g. blog_rewrite, worker_request_followup
            $table->string('model', 80)->default('claude-sonnet-4-6');
            $table->unsignedInteger('tokens_input')->default(0);
            $table->unsignedInteger('tokens_output')->default(0);
            $table->decimal('cost_usd', 12, 8)->default(0);
            $table->string('triggered_by', 200)->nullable(); // e.g. "admin:1", "public:worker_request"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('worker_slug');
        });
        Schema::dropIfExists('platform_usage_events');
    }
};
