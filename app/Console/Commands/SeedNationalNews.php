<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeedNationalNews extends Command
{
    protected $signature = 'news:seed-national';

    protected $description = 'Replace national news with the approved external media examples';

    public function handle(): int
    {
        $news = [
            ['title' => 'Surat Cinta Siswi SD di Nias Utara untuk Prabowo: Bisa Menabung Berkat MBG', 'source' => 'Suara.com', 'url' => 'https://www.suara.com/news/2026/06/22/092528/surat-cinta-siswi-sd-di-nias-utara-untuk-prabowo-bisa-menabung-berkat-mbg', 'imageUrl' => 'https://cdn-web.bgn.go.id/news/01KVSG9W3203BM3M5WTFXVYZDK.png', 'publishedAt' => '2026-06-22'],
            ['title' => 'Mendikdasmen: Banyak Dampak Positif, 43 Juta Siswa Ingin MBG Dilanjutkan', 'source' => 'VIVA', 'url' => 'https://www.viva.co.id/berita/nasional/1905462-mendikdasmen-banyak-dampak-positif-43-juta-siswa-ingin-mbg-dilanjutkan', 'imageUrl' => 'https://cdn-web.bgn.go.id/news/01KVC5ETCA20ZBJ5SN8QEB7NAM.png', 'publishedAt' => '2026-06-12'],
            ['title' => 'Prabowo Terima Hasil Kajian DEN Soal MBG, Dampaknya Positif untuk UMKM', 'source' => 'ANTARA', 'url' => 'https://www.antaranews.com/berita/5601304/prabowo-terima-hasil-kajian-den-soal-mbg-dampaknya-positif-untuk-umkm', 'imageUrl' => 'https://cdn-web.bgn.go.id/news/01KTQJNQ0DGVEN7ZW6VGXARXQC.png', 'publishedAt' => '2026-06-09'],
            ['title' => 'MBG Disebut Jadi Program yang Berdampak Langsung terhadap Kualitas SDM', 'source' => 'Metro TV', 'url' => 'https://www.metrotvnews.com/read/NrWC8mmO-mbg-disebut-jadi-program-yang-berdampak-langsung-terhadap-kualitas-sdm', 'imageUrl' => 'https://cdn-web.bgn.go.id/news/01KTJE9DEFRY3ZF9ZTAK68STDF.png', 'publishedAt' => '2026-06-06'],
        ];

        $prepared = [];
        foreach ($news as $order => $item) {
            $prepared[] = [
                'type' => NewsArticle::TYPE_NASIONAL,
                'category' => 'Berita',
                'title' => $item['title'],
                'content' => null,
                'image_path' => $this->downloadImage($item['imageUrl']),
                'source' => $item['source'],
                'url' => $item['url'],
                'published_at' => Carbon::parse($item['publishedAt']),
                'sort_order' => $order,
                'is_active' => true,
            ];
        }

        DB::transaction(function () use ($prepared): void {
            NewsArticle::query()->where('type', NewsArticle::TYPE_NASIONAL)->delete();

            foreach ($prepared as $item) {
                NewsArticle::create($item);
            }
        });

        $this->info('Seeded '.count($prepared).' external national news articles.');

        return self::SUCCESS;
    }

    private function downloadImage(string $imageUrl): ?string
    {
        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $path = 'news/national/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->makeDirectory('news/national');
        $response = Http::sink(Storage::disk('public')->path($path))->timeout(30)->get($imageUrl);

        if ($response->successful()) {
            return $path;
        }

        Storage::disk('public')->delete($path);

        return null;
    }
}
