<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('memory_contributions', function (Blueprint $table) {
            $table->id();
            $table->string('tx_id');
            $table->string('worker_slug');
            $table->unsignedInteger('deployment_id');
            $table->unsignedInteger('user_id');
            $table->string('table_name');               // 'clients' | 'contacts' | 'assets'
            $table->unsignedBigInteger('record_id');    // ID of the row written/updated
            $table->string('action');                   // 'created' | 'updated' | 'merged'
            $table->json('data');                       // Contributed data snapshot
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['user_id', 'table_name']);
            $table->index(['worker_slug', 'user_id']);
            $table->index('tx_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memory_contributions');
    }
};
