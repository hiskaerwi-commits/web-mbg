<?php

namespace App\Filament\Resources\NationalNews\Pages;

use App\Filament\Resources\NationalNews\NationalNewsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Size;

class ListNationalNews extends ListRecords
{
    protected static string $resource = NationalNewsResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tambah Berita Nasional')->color('primary')->size(Size::Medium)];
    }
}
