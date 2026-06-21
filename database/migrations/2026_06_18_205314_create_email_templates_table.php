<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // null = platform default
            $table->string('name');
            $table->string('category');        // SSL Expiry, Domain Renewal, etc.
            $table->string('tone');            // Professional, Friendly, etc.
            $table->text('subject_template'); // e.g. "Action Required: {{asset}} expires {{due_date}}"
            $table->text('body_template');    // Full email body with {{placeholders}}
            $table->boolean('approval_required')->default(true);
            $table->boolean('is_default')->default(false); // platform default for this category
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
