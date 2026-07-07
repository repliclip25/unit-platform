<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'profile_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('profile_code', 12)->nullable()->unique()->after('email');
            });
        }

        // Backfill existing users
        $users = DB::table('users')->whereNull('profile_code')->pluck('id');
        foreach ($users as $id) {
            $code = self::generateCode();
            DB::table('users')->where('id', $id)->update(['profile_code' => $code]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_code');
        });
    }

    private static function generateCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no 0/O/1/I ambiguity
        do {
            $suffix = '';
            for ($i = 0; $i < 5; $i++) {
                $suffix .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $code = 'UNIT-' . $suffix;
        } while (DB::table('users')->where('profile_code', $code)->exists());

        return $code;
    }
};
