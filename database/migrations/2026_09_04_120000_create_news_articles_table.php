<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_articles', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 20);
            $table->string('title', 255);
            $table->string('image_path')->nullable();
            $table->string('source')->nullable();
            $table->string('url', 2048)->nullable();
            $table->date('published_at');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active', 'sort_order']);
        });

        $now = now();

        DB::table('news_articles')->insert([
            ['type' => 'sorotan', 'title' => 'Rapat Koordinasi Percepatan Program Gizi Nasional', 'image_path' => null, 'source' => null, 'url' => null, 'published_at' => '2026-09-02', 'sort_order' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'sorotan', 'title' => 'Kunjungan Kerja ke Wilayah Prioritas Penanganan Gizi', 'image_path' => null, 'source' => null, 'url' => null, 'published_at' => '2026-09-02', 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'sorotan', 'title' => 'Audiensi Bersama Mitra Program Gizi Daerah', 'image_path' => null, 'source' => null, 'url' => null, 'published_at' => '2026-08-31', 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['type' => 'bgn', 'title' => 'Audiensi Percepatan Program Gizi di Wilayah Prioritas', 'image_path' => null, 'source' => 'BGN', 'url' => null, 'published_at' => '2026-09-02', 'sort_order' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'bgn', 'title' => 'BGN Perkuat Pengawasan Mutu Layanan Gizi di Lapangan', 'image_path' => null, 'source' => 'BGN', 'url' => null, 'published_at' => '2026-09-02', 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'bgn', 'title' => 'Evaluasi Standar Keamanan Dapur Penyedia Layanan Gizi', 'image_path' => null, 'source' => 'BGN', 'url' => null, 'published_at' => '2026-09-02', 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'bgn', 'title' => 'Koordinasi Lintas Kementerian untuk Program Gizi Nasional', 'image_path' => null, 'source' => 'BGN', 'url' => null, 'published_at' => '2026-08-31', 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['type' => 'nasional', 'title' => 'Testimoni Masyarakat atas Manfaat Program Gizi Nasional', 'image_path' => null, 'source' => 'Media Nasional', 'url' => null, 'published_at' => '2026-06-22', 'sort_order' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'nasional', 'title' => 'Pemerintah Dorong Perluasan Cakupan Program Gizi', 'image_path' => null, 'source' => 'Media Nasional', 'url' => null, 'published_at' => '2026-06-12', 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'nasional', 'title' => 'Kajian Dampak Program Gizi terhadap Ekonomi Lokal', 'image_path' => null, 'source' => 'Media Nasional', 'url' => null, 'published_at' => '2026-06-09', 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'nasional', 'title' => 'Program Gizi Dinilai Berdampak Langsung bagi Keluarga', 'image_path' => null, 'source' => 'Media Nasional', 'url' => null, 'published_at' => '2026-06-06', 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('news_articles');
    }
};
