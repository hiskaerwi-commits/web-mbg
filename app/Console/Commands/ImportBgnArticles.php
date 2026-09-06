<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportBgnArticles extends Command
{
    protected $signature = 'bgn:import-articles {path=bgn-articles.json}';
    protected $description = 'Replace BGN articles with a Puppeteer-exported official BGN article feed';

    public function handle(): int
    {
        $payload = json_decode((string) file_get_contents($this->argument('path')), true);
        $articles = $payload['articles'] ?? [];
        if ($articles === []) { $this->error('No articles found.'); return self::FAILURE; }

        $prepared = [];
        foreach ($articles as $order => $article) {
            $path = null;
            $imageUrl = $article['imageUrl'] ?? '';
            if ($imageUrl !== '') {
                $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $path = 'news/bgn/'.Str::uuid().'.'.$extension;
                $image = Http::sink(Storage::disk('public')->path($path))->timeout(30)->get($imageUrl);
                if (! $image->successful()) {
                    Storage::disk('public')->delete($path);
                    $path = null;
                }
            }

            $prepared[] = [
                'type' => NewsArticle::TYPE_BGN, 'category' => 'Artikel', 'title' => $article['title'],
                'document_number' => $article['documentNumber'] ?? null,
                'content' => $article['content'], 'image_path' => $path, 'source' => $article['source'] ?? 'BGN',
                'url' => $article['href'], 'published_at' => $this->parseDate($article['date'] ?? null),
                'sort_order' => $order, 'is_active' => true,
            ];
        }

        DB::transaction(function () use ($prepared): void {
            NewsArticle::query()->where('type', NewsArticle::TYPE_BGN)->delete();
            foreach ($prepared as $article) {
                NewsArticle::create($article);
            }
        });

        $this->info('Imported '.count($articles).' official BGN articles.');
        return self::SUCCESS;
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
