<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('user_gmail_credentials', 'has_insert_scope')) {
            Schema::table('user_gmail_credentials', function (Blueprint $table) {
                $table->boolean('has_insert_scope')->default(false)->after('refresh_token');
            });
        }

        if (!Schema::hasColumn('assets', 'deleted_at')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('user_gmail_credentials', 'has_insert_scope')) {
            Schema::table('user_gmail_credentials', function (Blueprint $table) {
                $table->dropColumn('has_insert_scope');
            });
        }

        if (Schema::hasColumn('assets', 'deleted_at')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
        }
    }
};
