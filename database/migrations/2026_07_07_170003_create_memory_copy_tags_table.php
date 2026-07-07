<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tracks provenance of records copied via memory access grants.
        // When a grantee copies a record into their own deployment, a tag is written here.
        // This allows: grantee can delete their own copy (it has a tag), original is untouched.
        Schema::create('memory_copy_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grant_id');          // which grant produced this copy
            $table->unsignedBigInteger('grantee_user_id');
            $table->unsignedBigInteger('grantee_deployment_id');
            $table->string('table_name');                    // clients | contacts | assets
            $table->unsignedBigInteger('record_id');         // grantee's copied record id
            $table->unsignedBigInteger('source_user_id');    // original owner
            $table->unsignedBigInteger('source_record_id');  // original record id
            $table->timestamp('created_at')->useCurrent();

            $table->index(['grantee_user_id', 'table_name', 'record_id']);
            $table->index('grant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memory_copy_tags');
    }
};
