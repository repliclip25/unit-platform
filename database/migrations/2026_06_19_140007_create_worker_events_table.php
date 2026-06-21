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
        Schema::create('worker_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');                   // e.g. 'renewal.classified'
            $table->string('tx_id');                        // source transaction
            $table->unsignedInteger('source_deployment_id')->nullable();
            $table->unsignedInteger('source_user_id')->nullable();
            $table->json('payload');                        // data emitted by the worker
            $table->json('routed_to')->nullable();          // deployment_ids that received it
            $table->string('status')->default('pending');   // pending | routed | no_subscribers
            $table->timestamps();

            $table->index(['event_name', 'source_user_id']);
            $table->index('tx_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_events');
    }
};
