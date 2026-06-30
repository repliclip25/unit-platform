<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transaction_stage_log', fn($t) => $t->softDeletes());
        Schema::table('email_templates',       fn($t) => $t->softDeletes());
    }

    public function down(): void
    {
        Schema::table('transaction_stage_log', fn($t) => $t->dropSoftDeletes());
        Schema::table('email_templates',       fn($t) => $t->dropSoftDeletes());
    }
};
