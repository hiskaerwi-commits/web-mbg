<?php

namespace App\Console\Commands;

use App\Models\MbgEducationRecap;
use Illuminate\Console\Command;

class ImportMbgRecapJson extends Command
{
    protected $signature = 'mbg:import-json {path : Absolute path to an MBG recap JSON response}';

    protected $description = 'Import a downloaded MBG provincial or regency recap JSON response';

    public function handle(): int
    {
        $path = $this->argument('path');

        if (! is_file($path) || ! ($payload = json_decode((string) file_get_contents($path), true)) || ($payload['status'] ?? null) !== 'success') {
            $this->error('The file is not a valid successful MBG JSON response.');

            return self::FAILURE;
        }

        $count = MbgEducationRecap::importPayload($payload['data']);

        $this->info("Imported {$count} MBG recaps.");

        return self::SUCCESS;
    }
}
