<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JuknisController extends Controller
{
    public function __invoke(Request $request): View
    {
        $types = ['JUKNIS', 'Peraturan Badan Gizi Nasional', 'Pedoman'];
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'jenis' => ['nullable', 'string', Rule::in($types)],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $search = trim($filters['search'] ?? '');
        $type = $filters['jenis'] ?? '';
        $catalog = json_decode(file_get_contents(resource_path('data/juknis.json')), true, flags: JSON_THROW_ON_ERROR);
        $filtered = collect($catalog['documents'])->filter(function (array $document) use ($search, $type): bool {
            $text = implode(' ', [$document['title'], $document['year'], $document['number']]);

            return (! $type || $document['type'] === $type)
                && ($search === '' || mb_stripos($text, $search) !== false);
        })->values();
        $page = (int) ($filters['page'] ?? 1);
        $documents = new LengthAwarePaginator($filtered->forPage($page, 10)->values(), $filtered->count(), 10, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('public.juknis', [
            'settings' => SiteSetting::current(),
            'documents' => $documents,
            'types' => $types,
            'search' => $search,
            'selectedType' => $type,
            'sourceUrl' => $catalog['source_url'],
            'fetchedAt' => $catalog['fetched_at'],
        ]);
    }
}
