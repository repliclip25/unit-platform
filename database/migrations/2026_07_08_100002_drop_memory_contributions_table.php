<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('memory_contributions');
    }

    public function down(): void
    {
        // memory_contributions was a redundant audit log — platform_events covers this via self::log()
    }
};
