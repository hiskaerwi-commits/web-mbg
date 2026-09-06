<?php

namespace App\Console\Commands;

use App\Models\MbgEducationRecap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportMbgRegencyRecaps extends Command
{
    protected $signature = 'mbg:import-regencies
        {--url-template= : URL with province and level placeholder tokens, see config/mbg.php}
        {--province=* : Limit to specific province code(s); defaults to every province in config/mbg_provinces.php}
        {--level=* : Limit to specific jenjang(s); defaults to config/mbg.php levels}
        {--delay=800 : Milliseconds to wait between requests}
        {--retries=3 : How many times to retry a request that gets blocked (HTTP 403/429)}';

    protected $description = 'Import MBG regency/kabupaten-kota recaps (per province, per jenjang) from the official source';

    public function handle(): int
    {
        $template = $this->option('url-template') ?: config('mbg.regency_recap_url_template');

        if (! $template || ! str_contains($template, '{province}') || ! str_contains($template, '{level}')) {
            $this->error('Provide a URL template containing {province} and {level} placeholders, e.g. --url-template="https://.../getrekapkabupaten?kode_prov={province}&jenjang={level}".');

            return self::FAILURE;
        }

        $provinceCodes = $this->option('province') ?: array_keys(config('mbg_provinces'));
        $levels = $this->option('level') ?: config('mbg.levels');
        $delayMs = (int) $this->option('delay');
        $maxRetries = (int) $this->option('retries');

        $totalImported = 0;
        $failed = [];

        foreach ($provinceCodes as $provinceCode) {
            $provinceName = config('mbg_provinces.' . $provinceCode, $provinceCode);
            $this->line("Provinsi {$provinceName} ({$provinceCode})");

            foreach ($levels as $level) {
                $url = strtr($template, ['{province}' => $provinceCode, '{level}' => $level]);

                $response = $this->fetch($url, $maxRetries);

                if ($response === null) {
                    $this->warn("  [{$level}] Gagal mengambil data setelah {$maxRetries}x percobaan.");
                    $failed[] = "{$provinceCode}/{$level}";

                    continue;
                }

                if (! $response->successful() || $response->json('status') !== 'success') {
                    $this->warn("  [{$level}] Gagal: HTTP {$response->status()}");
                    $failed[] = "{$provinceCode}/{$level}";

                    continue;
                }

                $imported = MbgEducationRecap::importPayload($response->json('data', []), $url);
                $totalImported += $imported;

                if ($imported > 0) {
                    $this->info("  [{$level}] {$imported} baris kabupaten/kota diimpor.");
                }

                if ($delayMs > 0) {
                    usleep($delayMs * 1000);
                }
            }
        }

        $this->newLine();
        $this->info("Selesai. Total {$totalImported} baris diimpor dari " . count($provinceCodes) . ' provinsi x ' . count($levels) . ' jenjang.');

        if ($failed) {
            $this->warn('Gagal (provinsi/jenjang): ' . implode(', ', $failed));
        }

        return self::SUCCESS;
    }

    private function fetch(string $url, int $maxRetries): ?\Illuminate\Http\Client\Response
    {
        $baseUrl = (string) parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . '/';

        for ($attempt = 1; $attempt <= max(1, $maxRetries); $attempt++) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                    'Accept' => 'application/json, text/plain, */*',
                    'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                    'Referer' => $baseUrl,
                ])->timeout(30)->get($url);
            } catch (\Throwable) {
                $response = null;
            }

            if ($response && $response->successful()) {
                return $response;
            }

            if ($response && ! in_array($response->status(), [403, 429, 503], true)) {
                return $response;
            }

            if ($attempt < $maxRetries) {
                usleep(1_500_000 * $attempt);
            }
        }

        return $response ?? null;
    }
}
