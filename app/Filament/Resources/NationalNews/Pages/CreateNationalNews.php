<?php

namespace App\Filament\Resources\NationalNews\Pages;

use App\Filament\Resources\NationalNews\NationalNewsResource;
use App\Models\NewsArticle;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Size;

class CreateNationalNews extends CreateRecord
{
    protected static string $resource = NationalNewsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = NewsArticle::TYPE_NASIONAL;
        $data['category'] = 'Berita';

        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->label('Simpan')->color('primary')->size(Size::Medium),
            $this->getCancelFormAction()->label('Batal')->size(Size::Medium),
        ];
    }
}
