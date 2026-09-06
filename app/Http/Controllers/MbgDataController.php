<?php

namespace App\Http\Controllers;

use App\Models\MbgEducationRecap;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MbgDataController extends Controller
{
    public function __invoke(Request $request): View
    {
        $settings = SiteSetting::current();
        $filters = $request->validate([
            'mbg_province' => ['nullable', 'string', Rule::in(array_keys(config('mbg_provinces')))],
            'mbg_level' => ['nullable', 'string', Rule::in(config('mbg.levels'))],
        ]);

        // Regional websites stay within their configured province. National sites allow drill-down.
        $provinceCode = $settings->mbg_province_code ?: ($filters['mbg_province'] ?? null);
        $level = $filters['mbg_level'] ?? null;
        $year = MbgEducationRecap::query()
            ->whereNull('district_code')
            ->whereNotNull('province_code')
            ->when($settings->mbg_province_code, fn ($query, $code) => $query->where('province_code', $code))
            ->max('year');
        $base = MbgEducationRecap::query()
            ->where('year', $year)
            ->whereNull('district_code')
            ->whereNotNull('province_code')
            ->when($provinceCode, fn ($query) => $query->where('province_code', $provinceCode));

        $availableLevels = (clone $base)->distinct()->pluck('level');
        $levels = collect(config('mbg.levels'))->filter(fn ($item) => $availableLevels->contains($item))->values();
        $base->when($level, fn ($query) => $query->where('level', $level));

        // Only province-level records contribute to the summary, never their child or national recaps.
        $provinceRecaps = (clone $base)->whereNull('regency_code');
        $summary = (clone $provinceRecaps)
            ->selectRaw('COUNT(*) as recap_count, SUM(beneficiaries) as beneficiaries, SUM(education_units) as education_units, SUM(public_units) as public_units, SUM(private_units) as private_units')
            ->first();
        $regions = $provinceCode
            ? (clone $base)->whereNotNull('regency_code')
                ->selectRaw('regency_code, regency_name, SUM(education_units) as education_units, SUM(beneficiaries) as beneficiaries, SUM(special_conditions) as special_conditions')
                ->groupBy('regency_code', 'regency_name')
            : (clone $provinceRecaps)
                ->selectRaw('province_code, SUM(education_units) as education_units, SUM(beneficiaries) as beneficiaries, SUM(special_conditions) as special_conditions')
                ->groupBy('province_code');

        return view('public.data-mbg', [
            'settings' => $settings,
            'mbgProvinceCode' => $provinceCode,
            'mbgProvinceName' => $provinceCode ? config('mbg_provinces.'.$provinceCode, $provinceCode) : 'Nasional',
            'mbgLevel' => $level,
            'mbgLevels' => $levels,
            'mbgYear' => $year,
            'mbgProvinceRecap' => $summary->recap_count ? $summary : null,
            'mbgRegionRecaps' => $regions->orderByDesc('beneficiaries')->get(),
            'mbgLastUpdated' => (clone $base)->max('source_pulled_at'),
        ]);
    }
}
