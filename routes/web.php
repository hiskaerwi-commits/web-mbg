<?php

use Illuminate\Support\Facades\Route;
use App\Models\NetworkLink;
use App\Models\NewsArticle;
use App\Models\SiteSetting;
use App\Http\Controllers\MbgDataController;
use App\Http\Controllers\JuknisController;
use App\Http\Controllers\FaqController;

Route::get('/', function (\Illuminate\Http\Request $request) {
    $networkSearch = trim((string) $request->query('network_search', ''));
    $requestedPerPage = (int) $request->query('network_per_page', 10);
    $networkPerPage = in_array($requestedPerPage, [10, 25, 50], true) ? $requestedPerPage : 10;

    $activeNewsQuery = fn (string $type) => NewsArticle::query()
        ->where('type', $type)
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderByDesc('published_at');

    return view('home', [
        'settings' => SiteSetting::current(),
        'beritaBgnHighlight' => $activeNewsQuery(NewsArticle::TYPE_SOROTAN)->limit(5)->get(),
        'beritaBgnList' => NewsArticle::query()
            ->where('type', NewsArticle::TYPE_BGN)
            ->where('is_active', true)
            ->orderByDesc('published_at')
            ->orderBy('sort_order')
            ->limit(5)
            ->get(),
        'beritaNasional' => $activeNewsQuery(NewsArticle::TYPE_NASIONAL)->limit(4)->get(),
        'networkLinks' => NetworkLink::query()
            ->where('is_active', true)
            ->when($networkSearch !== '', fn ($query) => $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($networkSearch) . '%']))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($networkPerPage, ['*'], 'network_page')
            ->withQueryString()
            ->fragment('jaringan'),
        'networkSearch' => $networkSearch,
        'networkPerPage' => $networkPerPage,
    ]);
});

Route::get('/berita', function (\Illuminate\Http\Request $request) {
    $selectedCategory = trim((string) $request->query('kategori', ''));
    $categories = ['Artikel', 'Siaran Pers', 'Foto'];

    if (! in_array($selectedCategory, $categories, true)) {
        $selectedCategory = '';
    }

    $news = NewsArticle::query()
        ->whereIn('type', [NewsArticle::TYPE_BGN, NewsArticle::TYPE_SOROTAN])
        ->where('is_active', true)
        ->when($selectedCategory !== '', fn ($query) => $query->where('category', $selectedCategory))
        ->orderByDesc('published_at')
        ->orderBy('sort_order')
        ->paginate(12)
        ->onEachSide(1)
        ->withQueryString();

    return view('public.news-index', [
        'settings' => SiteSetting::current(),
        'news' => $news,
        'categories' => $categories,
        'selectedCategory' => $selectedCategory,
    ]);
})->name('news.index');

Route::get('/berita/{article:slug}', function (NewsArticle $article) {
    abort_unless($article->is_active, 404);

    return view('public.news-show', [
        'settings' => SiteSetting::current(),
        'article' => $article,
    ]);
})->name('news.show');

Route::get('/data-mbg', MbgDataController::class)->name('data-mbg');

Route::get('/juknis', JuknisController::class)->name('juknis.index');

Route::get('/faq', FaqController::class)->name('faq.index');
