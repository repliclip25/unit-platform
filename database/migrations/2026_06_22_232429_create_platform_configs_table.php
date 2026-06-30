<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group')->default('general');
            $table->longText('value')->nullable();
            $table->string('type')->default('string');
            $table->string('label')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        $smtpBase = [
            'host'         => env('MAIL_HOST'),
            'port'         => env('MAIL_PORT', 465),
            'encryption'   => env('MAIL_ENCRYPTION', 'ssl'),
            'username'     => env('MAIL_USERNAME'),
            'password'     => '',
            'from_address' => env('MAIL_FROM_ADDRESS'),
            'from_name'    => env('MAIL_FROM_NAME', 'UNIT'),
            'active'       => true,
        ];

        DB::table('platform_configs')->insert([
            'key'         => 'smtp_routes',
            'group'       => 'mail',
            'value'       => json_encode([
                array_merge($smtpBase, ['key' => 'transactional', 'name' => 'Transactional', 'purpose' => 'Verification, password reset, OTP, security alerts']),
                array_merge($smtpBase, ['key' => 'marketing',     'name' => 'Marketing',     'purpose' => 'Trial end, weekly digest, referral, promos']),
                array_merge($smtpBase, ['key' => 'alerts',        'name' => 'Alerts',        'purpose' => 'Admin platform alerts, spend warnings, policy violations']),
            ]),
            'type'        => 'json',
            'label'       => 'SMTP Routes',
            'description' => 'Named SMTP routes for different email purposes',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_configs');
    }
};
