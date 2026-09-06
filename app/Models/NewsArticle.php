<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsArticle extends Model
{
    public const TYPE_SOROTAN = 'sorotan';

    public const TYPE_BGN = 'bgn';

    public const TYPE_NASIONAL = 'nasional';

    protected $fillable = [
        'type',
        'category',
        'title',
        'slug',
        'document_number',
        'content',
        'image_path',
        'source',
        'url',
        'published_at',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'published_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $article): void {
            if (filled($article->slug)) {
                return;
            }

            $base = Str::slug($article->title) ?: 'berita';
            $slug = $base;
            $suffix = 1;

            while (static::query()->where('slug', $slug)->exists()) {
                $slug = $base . '-' . ++$suffix;
            }

            $article->slug = $slug;
        });
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_SOROTAN => 'Sorotan (Carousel)',
            self::TYPE_BGN => 'Berita BGN',
            self::TYPE_NASIONAL => 'Berita Nasional',
        ];
    }

    public static function categoryOptions(): array
    {
        return [
            'Berita' => 'Berita',
            'Foto' => 'Foto',
            'Video' => 'Video',
            'Siaran Pers' => 'Siaran Pers',
            'Pengumuman' => 'Pengumuman',
        ];
    }
}
