<?php

namespace App\Filament\Resources\NationalNews\Pages;

use App\Filament\Resources\NationalNews\NationalNewsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Size;

class EditNationalNews extends EditRecord
{
    protected static string $resource = NationalNewsResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->label('Hapus')->size(Size::Medium)];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()->label('Simpan')->color('primary')->size(Size::Medium),
            $this->getCancelFormAction()->label('Batal')->size(Size::Medium),
        ];
    }
}
