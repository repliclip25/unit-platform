<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memory_access_grants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_user_id');
            $table->unsignedBigInteger('grantee_user_id');
            $table->unsignedBigInteger('deployment_id');
            $table->json('permissions');               // ['view','copy','upload','modify']
            $table->enum('status', ['pending', 'accepted', 'revoked'])->default('pending');
            $table->string('invite_token', 64)->unique();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index('owner_user_id');
            $table->index('grantee_user_id');
            $table->index('deployment_id');
            $table->unique(['owner_user_id', 'grantee_user_id', 'deployment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memory_access_grants');
    }
};
