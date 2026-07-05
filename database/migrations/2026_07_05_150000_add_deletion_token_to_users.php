<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('deletion_token', 64)->nullable()->unique()->after('persona');
            $table->timestamp('admin_deletion_requested_at')->nullable()->after('deletion_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['deletion_token', 'admin_deletion_requested_at']);
        });
    }
};
