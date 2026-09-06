<?php

namespace App\Console\Commands;

use App\Models\MbgEducationRecap;
use Illuminate\Console\Command;

class ImportMbgDirectory extends Command
{
    protected $signature = 'mbg:import-directory {path : Directory containing MBG recap JSON files (e.g. from scripts/fetch-mbg-regencies.mjs)}';

    protected $description = 'Import every *.json MBG recap file in a directory';

    public function handle(): int
    {
        $path = rtrim($this->argument('path'), '/\\');

        if (! is_dir($path)) {
            $this->error("Directory not found: {$path}");

            return self::FAILURE;
        }

        $files = glob($path . '/*.json');

        if (! $files) {
            $this->warn("No .json files found in {$path}.");

            return self::SUCCESS;
        }

        $totalImported = 0;
        $failedFiles = [];

        foreach ($files as $file) {
            $payload = json_decode((string) file_get_contents($file), true);

            if (! $payload || ($payload['status'] ?? null) !== 'success') {
                $this->warn('  Lewati (bukan respons sukses): ' . basename($file));
                $failedFiles[] = basename($file);

                continue;
            }

            $imported = MbgEducationRecap::importPayload($payload['data'] ?? []);
            $totalImported += $imported;

            $this->info(basename($file) . ": {$imported} baris diimpor.");
        }

        $this->newLine();
        $this->info("Selesai. Total {$totalImported} baris diimpor dari " . count($files) . ' file.');

        if ($failedFiles) {
            $this->warn('File dilewati: ' . implode(', ', $failedFiles));
        }

        return self::SUCCESS;
    }
}
