<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mbg_education_recaps', function (Blueprint $table): void {
            $table->string('regency_name')->nullable()->after('regency_code');
        });
    }

    public function down(): void
    {
        Schema::table('mbg_education_recaps', function (Blueprint $table): void {
            $table->dropColumn('regency_name');
        });
    }
};
