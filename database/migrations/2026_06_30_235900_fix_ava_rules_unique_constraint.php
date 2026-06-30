<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ava_rules', function (Blueprint $table) {
            $table->dropUnique('ava_rules_rule_id_unique');
            $table->unique(['user_id', 'rule_id'], 'ava_rules_user_rule_unique');
        });
    }

    public function down(): void
    {
        Schema::table('ava_rules', function (Blueprint $table) {
            $table->dropUnique('ava_rules_user_rule_unique');
            $table->unique('rule_id', 'ava_rules_rule_id_unique');
        });
    }
};
