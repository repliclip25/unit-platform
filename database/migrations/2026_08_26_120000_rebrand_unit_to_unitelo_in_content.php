<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data-only migration: applies the UNIT → UNITELO brand rename to
 * customer-facing content that the code-level rebrand sweep couldn't
 * reach because it lives in the database, not a template.
 *
 * Scans every string column of the three tables the audit found real
 * hits in (blog_posts, platform_email_templates, email_templates) and
 * replaces the whole word "UNIT" wherever it appears, rather than
 * hardcoding specific columns — platform_email_templates alone had it
 * in from_name, subject, body, and topic, not just the one field
 * originally flagged.
 *
 * Deliberately NOT touched (out of scope, historical record): audit
 * trail fields like worker_registry.installed_by and tenant_email_log.
 */
return new class extends Migration
{
    private array $tables = ['blog_posts', 'platform_email_templates', 'email_templates'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            $this->renameInTable($table, 'UNIT', 'UNITELO');
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            $this->renameInTable($table, 'UNITELO', 'UNIT');
        }
    }

    private function renameInTable(string $table, string $from, string $to): void
    {
        $pattern = '/\b' . preg_quote($from, '/') . '\b/';

        DB::table($table)->get()->each(function ($row) use ($table, $pattern, $to) {
            $updates = [];
            foreach ((array) $row as $col => $val) {
                if ($col === 'id' || !is_string($val)) continue;
                if (preg_match($pattern, $val)) {
                    $updates[$col] = preg_replace($pattern, $to, $val);
                }
            }
            if ($updates) {
                DB::table($table)->where('id', $row->id)->update($updates);
            }
        });
    }
};
