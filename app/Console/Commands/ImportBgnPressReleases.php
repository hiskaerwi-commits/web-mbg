<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportBgnPressReleases extends Command
{
    protected $signature = 'bgn:import-press-releases {path=bgn-press-releases.json}';

    protected $description = 'Replace the BGN press release category with a Puppeteer-exported official feed';

    public function handle(): int
    {
        $payload = json_decode((string) file_get_contents($this->argument('path')), true);
        $releases = $payload['articles'] ?? [];

        if ($releases === []) {
            $this->error('No press releases found.');

            return self::FAILURE;
        }

        $prepared = [];
        foreach ($releases as $order => $release) {
            $imagePath = $this->downloadImage($release['imageUrl'] ?? '');
            $prepared[] = [
                'type' => NewsArticle::TYPE_BGN,
                'category' => 'Siaran Pers',
                'title' => $release['title'],
                'document_number' => $release['documentNumber'] ?? null,
                'content' => $release['content'],
                'image_path' => $imagePath,
                'source' => $release['source'] ?? 'BGN',
                'url' => $release['href'],
                'published_at' => $this->parseDate($release['date'] ?? null),
                'sort_order' => $order,
                'is_active' => true,
            ];
        }

        DB::transaction(function () use ($prepared): void {
            NewsArticle::query()
                ->where('type', NewsArticle::TYPE_BGN)
                ->where('category', 'Siaran Pers')
                ->delete();

            foreach ($prepared as $release) {
                NewsArticle::create($release);
            }
        });

        $this->info('Imported '.count($releases).' official BGN press releases.');

        return self::SUCCESS;
    }

    private function downloadImage(string $imageUrl): ?string
    {
        if ($imageUrl === '') {
            return null;
        }

        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $path = 'news/bgn/'.Str::uuid().'.'.$extension;
        $response = Http::sink(Storage::disk('public')->path($path))->timeout(30)->get($imageUrl);

        if ($response->successful()) {
            return $path;
        }

        Storage::disk('public')->delete($path);

        return null;
    }

    private function parseDate(?string $date): Carbon
    {
        $months = [
            'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
            'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
            'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12,
        ];

        if (preg_match('/^(\d{1,2})\s+(\w+)\s+(\d{4})$/u', trim((string) $date), $matches)
            && isset($months[$matches[2]])) {
            return Carbon::create((int) $matches[3], $months[$matches[2]], (int) $matches[1]);
        }

        return now();
    }
}
