<?php

namespace App\Platform\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MemoryImportService
{
    private const CLIENT_MAP = [
        'client' => 'name', 'client name' => 'name', 'company' => 'name',
        'company name' => 'name', 'organization' => 'name', 'name' => 'name',
        'industry' => 'industry', 'sector' => 'industry',
        'style' => 'preferred_style', 'preferred style' => 'preferred_style',
        'communication style' => 'preferred_style',
        'notes' => 'notes', 'note' => 'notes', 'comments' => 'notes',
    ];

    private const CONTACT_MAP = [
        'contact' => 'name', 'contact name' => 'name', 'full name' => 'name', 'name' => 'name',
        'email' => 'email', 'email address' => 'email',
        'phone' => 'phone', 'phone number' => 'phone', 'mobile' => 'phone',
        'role' => 'role', 'title' => 'role', 'job title' => 'role', 'position' => 'role',
        'client' => 'client_name', 'company' => 'client_name',
        'client name' => 'client_name', 'organization' => 'client_name',
    ];

    private const ASSET_MAP = [
        'asset' => 'name', 'asset name' => 'name', 'name' => 'name',
        'domain' => 'name', 'service' => 'name',
        'type' => 'type', 'asset type' => 'type', 'category' => 'type',
        'vendor' => 'vendor', 'provider' => 'vendor', 'supplier' => 'vendor',
        'renewal date' => 'renewal_date', 'renews' => 'renewal_date',
        'expiry' => 'renewal_date', 'expiry date' => 'renewal_date',
        'expiration' => 'renewal_date', 'expiration date' => 'renewal_date',
        'due date' => 'renewal_date',
        'cost' => 'cost_per_year', 'cost per year' => 'cost_per_year',
        'annual cost' => 'cost_per_year', 'price' => 'cost_per_year',
        'client' => 'client_name', 'company' => 'client_name', 'client name' => 'client_name',
    ];

    public function readFile(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        return $ext === 'csv' ? $this->readCsv($file->getRealPath()) : $this->readExcel($file->getRealPath());
    }

    private function readCsv(string $path): array
    {
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);
        $headers = $csv->getHeader();
        $rows = [];
        foreach ($csv->getRecords() as $record) {
            $rows[] = array_values($record);
            if (count($rows) >= 200) break;
        }
        return compact('headers', 'rows');
    }

    private function readExcel(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, true, false);
        $headers = array_map('strval', array_shift($data) ?? []);
        $rows = array_slice(array_values($data), 0, 200);
        return compact('headers', 'rows');
    }

    public function suggestMapping(array $headers, string $type): array
    {
        $map = match($type) {
            'clients' => self::CLIENT_MAP,
            'contacts' => self::CONTACT_MAP,
            'assets' => self::ASSET_MAP,
            default => [],
        };
        $mapping = [];
        foreach ($headers as $i => $header) {
            $key = strtolower(trim($header));
            $mapping[$i] = $map[$key] ?? null;
        }
        return $mapping;
    }

    public function import(array $headers, array $rows, array $mapping, string $type, int $userId): array
    {
        $inserted = 0;
        $skipped = 0;
        $clientLookup = DB::table('clients')->where('user_id', $userId)
            ->pluck('id', 'name')->map(fn($id) => (int) $id)->toArray();

        foreach ($rows as $row) {
            $data = [];
            foreach ($mapping as $colIdx => $field) {
                if ($field && isset($row[$colIdx])) {
                    $data[$field] = trim((string) $row[$colIdx]);
                }
            }
            if (empty(array_filter($data))) { $skipped++; continue; }
            try {
                match($type) {
                    'clients' => $this->insertClient($data, $userId),
                    'contacts' => $this->insertContact($data, $userId, $clientLookup),
                    'assets' => $this->insertAsset($data, $userId, $clientLookup),
                };
                $inserted++;
            } catch (\Throwable) { $skipped++; }
        }
        return compact('inserted', 'skipped');
    }

    private function insertClient(array $data, int $userId): void
    {
        if (empty($data['name'])) return;
        $existing = DB::table('clients')->where('user_id', $userId)->where('name', $data['name'])->first();
        if ($existing) {
            DB::table('clients')->where('id', $existing->id)->update(array_filter([
                'industry' => $data['industry'] ?? null,
                'preferred_style' => $data['preferred_style'] ?? null,
                'notes' => $data['notes'] ?? null,
                'updated_at' => now(),
            ]));
        } else {
            DB::table('clients')->insert([
                'user_id' => $userId, 'name' => $data['name'],
                'industry' => $data['industry'] ?? null,
                'preferred_style' => $data['preferred_style'] ?? 'Professional',
                'notes' => $data['notes'] ?? null,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    private function insertContact(array $data, int $userId, array &$clientLookup): void
    {
        if (empty($data['email'])) return;
        $clientId = $this->resolveClient($data['client_name'] ?? null, $userId, $clientLookup);
        $existing = DB::table('contacts')->where('user_id', $userId)->where('email', $data['email'])->first();
        if ($existing) {
            DB::table('contacts')->where('id', $existing->id)->update(array_filter([
                'name' => $data['name'] ?? null, 'phone' => $data['phone'] ?? null,
                'role' => $data['role'] ?? null, 'client_id' => $clientId, 'updated_at' => now(),
            ]));
        } else {
            DB::table('contacts')->insert([
                'user_id' => $userId, 'client_id' => $clientId,
                'name' => $data['name'] ?? '', 'email' => $data['email'],
                'phone' => $data['phone'] ?? null, 'role' => $data['role'] ?? null,
                'is_primary' => false, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    private function insertAsset(array $data, int $userId, array &$clientLookup): void
    {
        if (empty($data['name'])) return;
        $clientId = $this->resolveClient($data['client_name'] ?? null, $userId, $clientLookup);
        $renewalDate = null;
        if (!empty($data['renewal_date'])) {
            try { $renewalDate = \Carbon\Carbon::parse($data['renewal_date'])->toDateString(); } catch (\Throwable) {}
        }
        DB::table('assets')->insert([
            'user_id' => $userId, 'client_id' => $clientId,
            'name' => $data['name'], 'type' => $data['type'] ?? 'Other',
            'vendor' => $data['vendor'] ?? null, 'renewal_date' => $renewalDate,
            'cost_per_year' => !empty($data['cost_per_year'])
                ? (float) preg_replace('/[^0-9.]/', '', $data['cost_per_year']) : null,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function resolveClient(?string $name, int $userId, array &$lookup): ?int
    {
        if (!$name) return null;
        if (isset($lookup[$name])) return $lookup[$name];
        DB::table('clients')->insert([
            'user_id' => $userId, 'name' => $name,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $id = (int) DB::getPdo()->lastInsertId();
        $lookup[$name] = $id;
        return $id;
    }
}
