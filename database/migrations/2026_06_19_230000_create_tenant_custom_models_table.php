<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_custom_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');             // display: "My Llama 3"
            $table->string('model_id')->unique();// stored in deployment config: "custom-llama3-xyz"
            $table->string('model_identifier'); // sent to API: "llama3.2:latest"
            $table->string('base_url');         // "http://localhost:11434/v1"
            $table->text('api_key_encrypted')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index('user_id');
        });
    }

    public function down(): void { Schema::dropIfExists('tenant_custom_models'); }
};
