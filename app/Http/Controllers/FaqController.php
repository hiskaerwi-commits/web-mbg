<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:200']]);
        $search = trim($filters['search'] ?? '');
        $catalog = json_decode(file_get_contents(resource_path('data/faq.json')), true, flags: JSON_THROW_ON_ERROR);
        $categories = collect($catalog['categories'])->map(function (array $category) use ($search): array {
            $category['items'] = array_values(array_filter($category['items'], function (array $item) use ($search, $category): bool {
                $answer = collect($item['blocks'])->map(fn (array $block) => $block['text'] ?? implode(' ', $block['items']))->implode(' ');

                return $search === '' || mb_stripos($category['title'].' '.$item['question'].' '.$answer, $search) !== false;
            }));

            return $category;
        })->filter(fn (array $category) => count($category['items']) > 0)->values();

        return view('public.faq', [
            'settings' => SiteSetting::current(),
            'categories' => $categories,
            'questionCount' => $categories->sum(fn (array $category) => count($category['items'])),
            'search' => $search,
            'referenceUrl' => 'https://www.bgn.go.id/faq',
            'fetchedAt' => $catalog['fetched_at'],
        ]);
    }
}
