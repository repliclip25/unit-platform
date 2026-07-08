<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('tenant_activity_log');
    }

    public function down(): void
    {
        // Page view logging was removed — engagement is derived from transactions and platform_events
    }
};
