<?php

namespace Tests\Feature;

use App\Filament\Pages\MbgData;
use App\Filament\Pages\WebsiteSettings;
use App\Models\MbgEducationRecap;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MbgScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SiteSetting::current()->update(['mbg_province_code' => null]);
    }

    private function recap(?string $province, int $beneficiaries, array $attributes = []): MbgEducationRecap
    {
        return MbgEducationRecap::create(array_merge([
            'year' => 2026,
            'level' => 'SD',
            'province_code' => $province,
            'beneficiaries' => $beneficiaries,
            'education_units' => 10,
            'public_units' => 6,
            'private_units' => 4,
            'source_pulled_at' => '2026-09-05 08:00:00',
        ], $attributes));
    }

    public function test_national_totals_include_only_current_province_recaps(): void
    {
        $this->recap('020000', 100);
        $this->recap('030000', 200);
        $this->recap('020000', 50, ['level' => 'SMP']);
        $this->recap(null, 9999);
        $this->recap(null, 9999, ['year' => 2027]);
        $this->recap('020000', 999, ['year' => 2025]);
        $this->recap('020000', 100, ['regency_code' => '020100', 'regency_name' => 'Kab. Bogor']);
        $this->recap('020000', 100, ['district_code' => '020101']);

        $this->get('/data-mbg')->assertOk()
            ->assertSee('Rekap Makan Bergizi Gratis Nasional')
            ->assertSee('Tahun data 2026')
            ->assertViewHas('mbgProvinceRecap', fn ($recap) => (int) $recap->beneficiaries === 350 && (int) $recap->education_units === 30)
            ->assertViewHas('mbgRegionRecaps', fn ($rows) => $rows->count() === 2 && (int) $rows->sum('beneficiaries') === 350)
            ->assertDontSee('Kab. Bogor');

        $this->get('/data-mbg?mbg_level=SD')->assertOk()
            ->assertViewHas('mbgProvinceRecap', fn ($recap) => (int) $recap->beneficiaries === 300)
            ->assertSee('mbg_province=020000&amp;mbg_level=SD', false);
    }

    public function test_national_drill_down_preserves_level_and_links_back_to_national(): void
    {
        $this->recap('020000', 100);
        $this->recap('020000', 100, ['regency_code' => '020100', 'regency_name' => 'Kab. Bogor']);
        $this->recap('020000', 70, ['regency_code' => '020100', 'regency_name' => 'Kab. Bogor', 'level' => 'SMP']);
        $this->recap('030000', 200, ['regency_code' => '030100', 'regency_name' => 'Kab. Cilacap']);

        $this->get('/data-mbg?mbg_province=020000&mbg_level=SD')->assertOk()
            ->assertSee('Rekap Makan Bergizi Gratis Prov. Jawa Barat')
            ->assertSee('Kab. Bogor')->assertDontSee('Kab. Cilacap')
            ->assertSee('Kembali ke rekap nasional')
            ->assertViewHas('mbgRegionRecaps', fn ($rows) => $rows->count() === 1 && (int) $rows->first()->beneficiaries === 100);
    }

    public function test_regional_site_keeps_its_configured_scope(): void
    {
        SiteSetting::current()->update(['mbg_province_code' => '020000']);
        $this->recap('020000', 100);
        $this->recap('030000', 200);

        foreach (['/data-mbg', '/data-mbg?mbg_province=030000', '/data-mbg?mbg_province='] as $url) {
            $this->get($url)->assertOk()
                ->assertViewHas('mbgProvinceCode', '020000')
                ->assertViewHas('mbgProvinceRecap', fn ($recap) => (int) $recap->beneficiaries === 100)
                ->assertDontSee('Kembali ke rekap nasional');
        }
    }

    public function test_empty_national_data_and_invalid_filters_are_handled(): void
    {
        $this->get('/data-mbg')->assertOk()->assertSee('Data provinsi belum diimpor.')
            ->assertViewHas('mbgProvinceRecap', null);
        $this->getJson('/data-mbg?mbg_province=invalid')->assertUnprocessable();
        $this->getJson('/data-mbg?mbg_level[]=SD')->assertUnprocessable();
    }

    public function test_settings_can_switch_between_national_and_province_and_admin_follows(): void
    {
        $this->actingAs(User::factory()->create());
        $this->recap('020000', 100);
        $this->recap('030000', 200);

        Livewire::test(WebsiteSettings::class)
            ->assertSee('Pusat / Nasional')
            ->set('mbgProvinceCode', '020000')->call('save')->assertHasNoErrors();
        $this->assertSame('020000', SiteSetting::current()->mbg_province_code);
        Livewire::test(MbgData::class)->assertSee('Prov. Jawa Barat')->assertDontSee('Prov. Jawa Tengah');

        Livewire::test(WebsiteSettings::class)
            ->set('mbgProvinceCode', '')->call('save')->assertHasNoErrors();
        $this->assertNull(SiteSetting::current()->mbg_province_code);
        Livewire::test(MbgData::class)->assertSee('Pusat / Nasional')
            ->assertSee('Prov. Jawa Barat')->assertSee('Prov. Jawa Tengah');
        $this->get('/data-mbg')->assertOk()->assertViewHas('mbgProvinceCode', null);
    }
}
