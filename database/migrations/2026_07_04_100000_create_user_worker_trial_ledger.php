<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_worker_trial_ledger')) {
            Schema::create('user_worker_trial_ledger', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('worker_slug', 60)->index();
                $table->unsignedInteger('granted')->default(0);
                $table->unsignedInteger('used')->default(0);
                $table->timestamp('first_deployed_at')->nullable();
                $table->timestamp('trial_expires_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'worker_slug']);
            });
        }

        // Make deployment_id nullable so billing rows survive deployment deletion.
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('deployment_billing', function (Blueprint $table) {
                $table->unsignedBigInteger('deployment_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_worker_trial_ledger');
    }
};
