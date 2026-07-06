<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_deployments', function (Blueprint $table) {
            $table->string('persona')->nullable()->after('status');
        });

        // Back-fill from users.persona for existing deployments
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("
                UPDATE worker_deployments wd
                JOIN users u ON u.id = wd.user_id
                SET wd.persona = u.persona
                WHERE u.persona IS NOT NULL AND wd.persona IS NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::table('worker_deployments', fn($t) => $t->dropColumn('persona'));
    }
};
