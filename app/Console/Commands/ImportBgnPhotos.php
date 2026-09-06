<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportBgnPhotos extends Command
{
    protected $signature = 'bgn:import-photos {path=bgn-photos.json}';

    protected $description = 'Replace carousel slides with a Puppeteer-exported official BGN photo feed';

    public function handle(): int
    {
        $payload = json_decode((string) file_get_contents($this->argument('path')), true);
        $photos = $payload['articles'] ?? [];

        if ($photos === []) {
            $this->error('No BGN photos found.');

            return self::FAILURE;
        }

        $prepared = [];
        foreach ($photos as $order => $photo) {
            $prepared[] = [
                'type' => NewsArticle::TYPE_SOROTAN,
                'category' => 'Foto',
                'title' => $photo['title'],
                'document_number' => $photo['documentNumber'] ?? null,
                'content' => $photo['content'],
                'image_path' => $this->downloadImage($photo['imageUrl'] ?? ''),
                'source' => $photo['source'] ?? 'BGN',
                'url' => $photo['href'],
                'published_at' => $this->parseDate($photo['date'] ?? null),
                'sort_order' => $order,
                'is_active' => true,
            ];
        }

        DB::transaction(function () use ($prepared): void {
            NewsArticle::query()->where('type', NewsArticle::TYPE_SOROTAN)->delete();

            foreach ($prepared as $photo) {
                NewsArticle::create($photo);
            }
        });

        $this->info('Imported '.count($photos).' official BGN photos for the carousel.');

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
