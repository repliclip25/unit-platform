<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('sequence')->default('onboarding'); // onboarding, transactional, marketing
            $table->string('label');
            $table->string('description')->nullable();
            $table->string('trigger_condition')->nullable();
            $table->integer('day_offset')->nullable();          // for sequences: day 3, day 7, etc.
            $table->string('trigger_state')->nullable();        // no_gmail, no_tx, no_worker, no_activity
            $table->string('subject');
            $table->text('body');
            $table->string('from_name')->default('Franklin at UNIT');
            $table->boolean('active')->default(true);
            $table->text('ai_rewrite_notes')->nullable();
            $table->timestamp('last_ai_rewrite_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_email_templates');
    }
};
