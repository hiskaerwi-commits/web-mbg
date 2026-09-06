<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_faq_displays_all_questions_and_preserves_answer_lists(): void
    {
        $catalog = json_decode(file_get_contents(resource_path('data/faq.json')), true, flags: JSON_THROW_ON_ERROR);
        $response = $this->get('/faq')->assertOk()->assertViewHas('questionCount', 62);
        foreach ($catalog['categories'] as $category) {
            $response->assertSee($category['title']);
            foreach ($category['items'] as $item) {
                $response->assertSee($item['question']);
                foreach ($item['blocks'] as $block) {
                    foreach ($block['items'] ?? [$block['text']] as $text) {
                        $response->assertSee($text);
                    }
                }
            }
        }
    }

    public function test_faq_search_and_empty_results(): void
    {
        $this->get('/faq?search=ramadan')->assertOk()
            ->assertViewHas('questionCount', 1)
            ->assertSee('Bagaimana pelaksanaan Program Makan Bergizi ketika bulan puasa?')
            ->assertSee('Tampilkan semua FAQ');
        $this->get('/faq?search=tidak-ada-hasil-xyz')->assertOk()
            ->assertViewHas('questionCount', 0)->assertSee('Pertanyaan tidak ditemukan');
        $this->getJson('/faq?search[]=x')->assertUnprocessable();
    }
}
