<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_gmail_credentials', function (Blueprint $table) {
            $table->timestamp('token_last_used_at')->nullable()->after('watch_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_gmail_credentials', function (Blueprint $table) {
            $table->dropColumn('token_last_used_at');
        });
    }
};
