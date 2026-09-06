<?php

namespace App\Console\Commands;

use App\Models\MbgEducationRecap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportMbgNationalRecap extends Command
{
    protected $signature = 'mbg:import-national {--url= : Override the official national recap endpoint}';

    protected $description = 'Import official MBG national education recaps into the local data service';

    public function handle(): int
    {
        $url = $this->option('url') ?: config('mbg.national_recap_url');
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'Referer' => (string) parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . '/',
        ])->timeout(30)->get($url);

        if (! $response->successful() || $response->json('status') !== 'success') {
            $this->error('MBG source could not be imported: '.$response->status());

            return self::FAILURE;
        }

        foreach ($response->json('data', []) as $recap) {
            MbgEducationRecap::query()->updateOrCreate(
                ['year' => $recap['tahun'], 'level' => $recap['jenjang'], 'province_code' => null, 'regency_code' => null, 'district_code' => null],
                [
                    'education_units' => $recap['jumlah_satuan_pendidikan'],
                    'male_students' => $recap['jumlah_laki'],
                    'female_students' => $recap['jumlah_perempuan'],
                    'beneficiaries' => $recap['jumlah_penerima_manfaat'],
                    'allergies' => $recap['jumlah_alergi'],
                    'phobias' => $recap['jumlah_fobia'],
                    'intolerances' => $recap['jumlah_intoleransi'],
                    'special_conditions' => $recap['jumlah_kondisi_khusus'],
                    'public_units' => $recap['jumlah_satpen_negeri'],
                    'private_units' => $recap['jumlah_satpen_swasta'],
                    'source_pulled_at' => $recap['date_pull'],
                    'source_url' => $url,
                ],
            );
        }

        $this->info('MBG national recap imported successfully.');

        return self::SUCCESS;
    }
}
