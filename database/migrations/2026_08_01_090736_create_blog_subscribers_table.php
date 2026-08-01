<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Email capture for the /blog newsletter box. Deliberately separate from
    // `users`/tenants: most subscribers won't have a UNIT account.
    public function up(): void
    {
        Schema::create('blog_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('unsubscribe_token', 64)->unique();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('source')->nullable(); // e.g. utm_source at signup
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_subscribers');
    }
};
