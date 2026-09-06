<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class WebsiteSettings extends Page
{
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected string $view = 'filament.pages.website-settings';

    public string $siteName = '';

    public string $siteTagline = '';

    public string $footerText = '';

    public array $footer = [];

    public string $mbgProvinceCode = '';

    public string $defaultMetaTitle = '';

    public string $defaultMetaDescription = '';

    public string $googleSiteVerification = '';

    public ?string $logoPath = null;

    public ?string $faviconPath = null;

    public ?string $defaultOgImagePath = null;

    public mixed $logo = null;

    public mixed $favicon = null;

    public mixed $defaultOgImage = null;

    public static function getNavigationLabel(): string
    {
        return 'Website Settings';
    }

    public function getTitle(): string
    {
        return 'Website Settings';
    }

    public function provinceOptions(): array
    {
        $options = config('mbg_provinces');
        asort($options);

        return $options;
    }

    public function mount(): void
    {
        $settings = SiteSetting::current();

        $this->siteName = $settings->site_name;
        $this->siteTagline = $settings->site_tagline ?? '';
        $this->footerText = $settings->footer_text ?? '';
        $this->footer = $settings->footer_details;
        $this->mbgProvinceCode = $settings->mbg_province_code ?? '';
        $this->defaultMetaTitle = $settings->default_meta_title ?? '';
        $this->defaultMetaDescription = $settings->default_meta_description ?? '';
        $this->googleSiteVerification = $settings->google_site_verification ?? '';
        $this->logoPath = $settings->logo_path;
        $this->faviconPath = $settings->favicon_path;
        $this->defaultOgImagePath = $settings->default_og_image_path;
    }

    public function save(): void
    {
        $this->validate([
            'siteName' => ['required', 'string', 'max:120'],
            'siteTagline' => ['nullable', 'string', 'max:160'],
            'footerText' => ['nullable', 'string', 'max:500'],
            'footer' => ['array:show_contact,address,email,phone,whatsapp,facebook,instagram,x,tiktok'],
            'footer.show_contact' => ['required', 'boolean'],
            'footer.address' => ['nullable', 'string', 'max:1000'],
            'footer.email' => ['nullable', 'email', 'max:255'],
            'footer.phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?\(?[0-9][0-9\s().-]*$/'],
            'footer.whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s().-]*$/'],
            'footer.facebook' => ['nullable', 'url:http,https', 'max:2048'],
            'footer.instagram' => ['nullable', 'url:http,https', 'max:2048'],
            'footer.x' => ['nullable', 'url:http,https', 'max:2048'],
            'footer.tiktok' => ['nullable', 'url:http,https', 'max:2048'],
            'mbgProvinceCode' => ['nullable', 'string', 'in:'.implode(',', ['', ...array_keys(config('mbg_provinces'))])],
            'defaultMetaTitle' => ['nullable', 'string', 'max:60'],
            'defaultMetaDescription' => ['nullable', 'string', 'max:160'],
            'googleSiteVerification' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'mimes:png,ico,svg', 'max:512'],
            'defaultOgImage' => ['nullable', 'image', 'max:2048'],
        ]);

        $settings = SiteSetting::current();

        $settings->fill([
            'site_name' => $this->siteName,
            'site_tagline' => $this->siteTagline ?: null,
            'footer_text' => $this->footerText ?: null,
            'footer_settings' => array_replace(
                array_map(fn ($value) => trim((string) $value), $this->footer),
                ['show_contact' => (bool) $this->footer['show_contact']],
            ),
            'mbg_province_code' => $this->mbgProvinceCode ?: null,
            'default_meta_title' => $this->defaultMetaTitle ?: null,
            'default_meta_description' => $this->defaultMetaDescription ?: null,
            'google_site_verification' => $this->googleSiteVerification ?: null,
        ]);

        $this->storeUpload($settings, 'logo', 'logo_path', 'branding');
        $this->storeUpload($settings, 'favicon', 'favicon_path', 'branding');
        $this->storeUpload($settings, 'defaultOgImage', 'default_og_image_path', 'seo');

        $settings->save();

        $this->logoPath = $settings->logo_path;
        $this->faviconPath = $settings->favicon_path;
        $this->defaultOgImagePath = $settings->default_og_image_path;
        $this->logo = null;
        $this->favicon = null;
        $this->defaultOgImage = null;

        Notification::make()
            ->title('Website settings tersimpan')
            ->success()
            ->send();
    }

    private function storeUpload(SiteSetting $settings, string $property, string $column, string $directory): void
    {
        if (! ($this->{$property} instanceof TemporaryUploadedFile)) {
            return;
        }

        if ($settings->{$column}) {
            Storage::disk('public')->delete($settings->{$column});
        }

        $settings->{$column} = $this->{$property}->store($directory, 'public');
    }
}
