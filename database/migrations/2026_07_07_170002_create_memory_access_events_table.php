<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memory_access_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grant_id');
            $table->unsignedBigInteger('actor_user_id');
            $table->enum('action', ['viewed', 'copied', 'uploaded', 'modified']);
            $table->string('table_name');              // clients | contacts | assets
            $table->unsignedBigInteger('record_id')->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('grant_id');
            $table->index('actor_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memory_access_events');
    }
};
