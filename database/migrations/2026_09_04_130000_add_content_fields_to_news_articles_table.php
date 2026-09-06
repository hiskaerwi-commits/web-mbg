<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table): void {
            $table->string('slug')->nullable()->after('title');
            $table->string('category')->default('Berita')->after('type');
            $table->string('document_number')->nullable()->after('title');
            $table->longText('content')->nullable()->after('document_number');
        });

        foreach (DB::table('news_articles')->select('id', 'title')->get() as $row) {
            $slug = Str::slug($row->title) . '-' . $row->id;

            DB::table('news_articles')->where('id', $row->id)->update(['slug' => $slug]);
        }

        Schema::table('news_articles', function (Blueprint $table): void {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('news_articles', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'category', 'document_number', 'content']);
        });
    }
};
