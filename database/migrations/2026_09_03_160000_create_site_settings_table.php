<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('site_name')->default('Portal Institusi');
            $table->string('site_tagline')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('default_meta_title')->nullable();
            $table->text('default_meta_description')->nullable();
            $table->string('default_og_image_path')->nullable();
            $table->string('google_site_verification')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
