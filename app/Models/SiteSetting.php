<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'site_tagline',
        'logo_path',
        'favicon_path',
        'footer_text',
        'footer_settings',
        'mbg_province_code',
        'default_meta_title',
        'default_meta_description',
        'default_og_image_path',
        'google_site_verification',
    ];

    protected function casts(): array
    {
        return ['footer_settings' => 'array'];
    }

    public function getFooterDetailsAttribute(): array
    {
        return array_replace(config('footer.defaults'), $this->footer_settings ?? []);
    }

    public function getProvinceNameAttribute(): ?string
    {
        return $this->mbg_province_code ? config('mbg_provinces.'.$this->mbg_province_code) : null;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->province_name ? "{$this->site_name} - {$this->province_name}" : $this->site_name;
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'site_name' => 'Portal Institusi',
            'site_tagline' => 'Informasi resmi institusi',
            'footer_text' => 'Portal Informasi Resmi',
            'mbg_province_code' => '020000',
            'default_meta_title' => 'Portal Institusi',
            'default_meta_description' => 'Portal informasi resmi institusi.',
        ]);
    }
}
