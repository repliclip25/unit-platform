<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            return;
        }

        $indexes = collect(\Illuminate\Support\Facades\DB::select("SHOW INDEX FROM ava_rules"))->pluck('Key_name')->unique();

        if ($indexes->contains('ava_rules_rule_id_unique')) {
            Schema::table('ava_rules', function (Blueprint $table) {
                $table->dropUnique('ava_rules_rule_id_unique');
            });
        }

        if (!$indexes->contains('ava_rules_user_rule_unique')) {
            \Illuminate\Support\Facades\DB::statement('
                DELETE a FROM ava_rules a
                INNER JOIN ava_rules b
                ON a.user_id = b.user_id AND a.rule_id = b.rule_id AND a.id > b.id
            ');
            Schema::table('ava_rules', function (Blueprint $table) {
                $table->unique(['user_id', 'rule_id'], 'ava_rules_user_rule_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ava_rules', function (Blueprint $table) {
            $table->dropUnique('ava_rules_user_rule_unique');
            $table->unique('rule_id', 'ava_rules_rule_id_unique');
        });
    }
};
