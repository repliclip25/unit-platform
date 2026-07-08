<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('asset_groups')) {
            Schema::create('asset_groups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('deployment_id');  // worker-scoped
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('client_id')->nullable();
                $table->string('name', 200);
                $table->string('type', 100)->nullable();       // from worker groupTypes()
                $table->text('notes')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index('deployment_id');
                $table->index(['user_id', 'deployment_id']);
                $table->index('client_id');
            });
        }

        if (!Schema::hasTable('asset_group_items')) {
            Schema::create('asset_group_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('group_id');
                $table->unsignedBigInteger('asset_id');
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['group_id', 'asset_id']);
                $table->index('group_id');
                $table->index('asset_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_group_items');
        Schema::dropIfExists('asset_groups');
    }
};
