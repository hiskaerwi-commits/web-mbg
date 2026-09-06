<?php

namespace Tests\Feature;

use App\Filament\Pages\WebsiteSettings;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FooterSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_contacts_and_social_links_can_be_saved_and_cleared(): void
    {
        $this->actingAs(User::factory()->create());
        $footer = [
            'show_contact' => true,
            'address' => "Jl. Contoh No. 10\nBandung",
            'email' => 'kontak@example.org',
            'phone' => '(022) 123-4567',
            'whatsapp' => '0812-3456-7890',
            'facebook' => 'https://www.facebook.com/contoh',
            'instagram' => 'https://www.instagram.com/contoh',
            'x' => 'https://x.com/contoh',
            'tiktok' => 'https://www.tiktok.com/@contoh',
        ];
        Livewire::test(WebsiteSettings::class)->set('footer', $footer)->call('save')->assertHasNoErrors();
        $this->assertSame($footer, SiteSetting::current()->footer_details);
        Livewire::test(WebsiteSettings::class)->assertSet('footer', $footer);

        $page = $this->get('/data-mbg')->assertOk()->assertSee('Jl. Contoh No. 10')
            ->assertSee('mailto:kontak@example.org', false)->assertSee('tel:0221234567', false)
            ->assertSee('https://wa.me/6281234567890', false);
        foreach (['facebook', 'instagram', 'x', 'tiktok'] as $platform) {
            $page->assertSee($footer[$platform], false);
        }

        Livewire::test(WebsiteSettings::class)->set('footer.show_contact', false)->call('save')->assertHasNoErrors();
        $this->assertSame($footer['address'], SiteSetting::current()->footer_details['address']);
        $this->get('/data-mbg')->assertOk()->assertDontSee($footer['address'])
            ->assertDontSee('mailto:')->assertDontSee('tel:')->assertDontSee('https://wa.me/')
            ->assertDontSee('Butuh Bantuan?')->assertSee($footer['instagram'], false);

        $empty = array_fill_keys(array_keys($footer), '');
        $empty['show_contact'] = false;
        Livewire::test(WebsiteSettings::class)->set('footer', $empty)->call('save')->assertHasNoErrors();
        $this->get('/data-mbg')->assertOk()->assertDontSee('Media Sosial')
            ->assertDontSee('mailto:')->assertDontSee('tel:')->assertDontSee('https://wa.me/')
            ->assertDontSee('Butuh Bantuan?');
    }

    public function test_footer_rejects_unsafe_social_urls_and_invalid_contacts(): void
    {
        $this->actingAs(User::factory()->create());
        Livewire::test(WebsiteSettings::class)
            ->set('footer.instagram', 'javascript:alert(1)')
            ->set('footer.email', 'not-an-email')
            ->set('footer.whatsapp', 'not-a-phone')
            ->call('save')->assertHasErrors(['footer.instagram', 'footer.email', 'footer.whatsapp']);
    }
}
