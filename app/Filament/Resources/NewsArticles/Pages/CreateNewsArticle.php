<?php

namespace App\Filament\Resources\NewsArticles\Pages;

use App\Filament\Resources\NewsArticles\NewsArticleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Size;

class CreateNewsArticle extends CreateRecord
{
    protected static string $resource = NewsArticleResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan')
                ->color('primary')
                ->size(Size::Medium),
            $this->getCancelFormAction()
                ->label('Batal')
                ->size(Size::Medium),
        ];
    }
}
