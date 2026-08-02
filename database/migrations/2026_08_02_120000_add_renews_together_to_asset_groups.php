<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asset_groups', function (Blueprint $table) {
            // Explicit, tenant-set — not inferred from renewal_date proximity.
            // A group's members can have wildly different individually-
            // tracked dates (see Baskel: domain/SSL expiring a year before
            // hosting on paper) and still genuinely renew together in
            // practice, because that's how the tenant actually bills the
            // client. When true, AssetExpiryWatchJob triggers the whole
            // group off whichever member's date comes first, bundled into
            // one transaction, instead of each asset firing independently.
            $table->boolean('renews_together')->default(false)->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('asset_groups', function (Blueprint $table) {
            $table->dropColumn(['renews_together']);
        });
    }
};
