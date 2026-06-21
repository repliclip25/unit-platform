<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_dna', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deployment_id');
            $table->string('key');
            $table->longText('value');                       // JSON-encoded — any scalar, array, or object
            $table->string('source')->default('human');      // human | worker | system
            $table->string('injected_by')->nullable();       // worker slug or user identifier
            $table->string('tx_id')->nullable();             // transaction that triggered this injection
            $table->timestamps();

            $table->unique(['deployment_id', 'key']);        // one live value per key per deployment
            $table->index('deployment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_dna');
    }
};
