<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JuknisTest extends TestCase
{
    use RefreshDatabase;

    public function test_official_catalog_is_displayed_with_download_links(): void
    {
        $catalog = json_decode(file_get_contents(resource_path('data/juknis.json')), true, flags: JSON_THROW_ON_ERROR);
        $response = $this->get('/juknis')->assertOk();
        $response->assertViewHas('documents', fn ($documents) => $documents->total() === count($catalog['documents']));
        foreach ($catalog['documents'] as $document) {
            $response->assertSee($document['title'])->assertSee($document['download_url'], false);
        }
    }

    public function test_search_matches_year_number_and_title_and_combines_with_type(): void
    {
        $this->get('/juknis?search=2026')->assertOk()
            ->assertViewHas('documents', fn ($documents) => $documents->total() === 1);
        $this->get('/juknis?search=401.1&jenis=JUKNIS')->assertOk()
            ->assertSee('Petunjuk Teknis Tata Kelola Penyelenggaraan Program Makan Bergizi Gratis')
            ->assertViewHas('documents', fn ($documents) => $documents->total() === 1);
        $this->get('/juknis?search=susu')->assertOk()
            ->assertViewHas('documents', fn ($documents) => $documents->total() === 1);
        $this->get('/juknis?search=susu&jenis=Pedoman')->assertOk()->assertSee('Dokumen tidak ditemukan');
        $this->getJson('/juknis?search[]=x')->assertUnprocessable();
    }

    public function test_unbuilt_navigation_links_go_home_and_juknis_is_local(): void
    {
        $response = $this->get('/')->assertOk();
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $labels = ['Infografis', 'SPPG Operasional', 'Pejabat BGN', 'Visi Misi', 'Arti Logo', 'Tugas & Fungsi', 'Gabung Menjadi Mitra', 'Ajukan Pengaduan', 'Radar MBG', 'Bantuan', 'Alamat'];
        foreach ($labels as $label) {
            $links = $xpath->query('//a[normalize-space(.)="'.$label.'"]');
            $this->assertGreaterThan(0, $links->length, $label);
            foreach ($links as $link) {
                $this->assertSame(url('/'), $link->getAttribute('href'), $label);
            }
        }
        foreach ($xpath->query('//a[normalize-space(.)="Juknis" or normalize-space(.)="Buka Dokumen"]') as $link) {
            $this->assertSame(route('juknis.index'), $link->getAttribute('href'));
        }
        $faqLinks = $xpath->query('//footer//a[normalize-space(.)="FAQ"]');
        $this->assertSame(1, $faqLinks->length);
        $this->assertSame(route('faq.index'), $faqLinks->item(0)->getAttribute('href'));
        $this->assertSame(0, $xpath->query('//header//a[normalize-space(.)="FAQ"]')->length);
    }
}
