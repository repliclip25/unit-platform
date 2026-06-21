<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('blocked_at')->nullable()->after('remember_token');
            $table->string('block_reason')->nullable()->after('blocked_at');
            $table->decimal('monthly_spend_cap', 8, 2)->nullable()->after('block_reason');
        });

        // Add indexes to usage_events for fast monthly aggregation
        Schema::table('usage_events', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'ue_user_month');
            $table->index(['deployment_id', 'created_at'], 'ue_dep_month');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['blocked_at', 'block_reason', 'monthly_spend_cap']);
        });
        Schema::table('usage_events', function (Blueprint $table) {
            $table->dropIndex('ue_user_month');
            $table->dropIndex('ue_dep_month');
        });
    }
};
