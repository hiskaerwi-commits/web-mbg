<?php

namespace App\Filament\Pages;

use App\Models\MbgEducationRecap;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\WithPagination;

class MbgData extends Page
{
    use WithPagination;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedChartBar;

    protected string $view = 'filament.pages.mbg-data';

    public static function getNavigationLabel(): string { return 'Data MBG'; }
    public function getTitle(): string { return 'Data MBG'; }

    public function getProvinceCodeProperty(): ?string
    {
        return SiteSetting::current()->mbg_province_code;
    }

    public function getProvinceNameProperty(): ?string
    {
        $code = $this->provinceCode;

        return $code ? config('mbg_provinces.' . $code, $code) : null;
    }

    public function getRecapsProperty()
    {
        return MbgEducationRecap::query()
            ->when($this->provinceCode, fn ($query, $code) => $query->where('province_code', $code))
            ->orderBy('province_code')
            ->orderBy('regency_name')
            ->orderBy('level')
            ->paginate(50);
    }
}
