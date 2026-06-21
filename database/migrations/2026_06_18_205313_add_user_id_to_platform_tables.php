<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add user_id to all memory and transaction tables
        $tables = ['clients', 'assets', 'ava_rules', 'transactions', 'renewal_register', 'processed_messages', 'ava_state'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }

        // Contacts inherit via client, but also need direct user_id for queries
        Schema::table('contacts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        $tables = ['clients', 'contacts', 'assets', 'ava_rules', 'transactions', 'renewal_register', 'processed_messages', 'ava_state'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['user_id']);
                $t->dropColumn('user_id');
            });
        }
    }
};
