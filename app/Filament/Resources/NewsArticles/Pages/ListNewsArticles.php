<?php

namespace App\Filament\Resources\NewsArticles\Pages;

use App\Filament\Resources\NewsArticles\NewsArticleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Size;

class ListNewsArticles extends ListRecords
{
    protected static string $resource = NewsArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Berita')
                ->color('primary')
                ->size(Size::Medium),
        ];
    }
}
