<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->string('marketplace_status')->default('draft')->after('status');
            $table->text('description')->nullable()->after('marketplace_status');
            $table->json('qa_checklist')->nullable()->after('description');
            $table->timestamp('qa_passed_at')->nullable()->after('qa_checklist');
            $table->timestamp('published_at')->nullable()->after('qa_passed_at');
            $table->string('built_by')->default('UNIT Platform')->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn(['marketplace_status','description','qa_checklist','qa_passed_at','published_at','built_by']);
        });
    }
};
