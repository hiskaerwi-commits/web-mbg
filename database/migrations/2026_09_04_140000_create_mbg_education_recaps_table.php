<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mbg_education_recaps', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->string('level', 20);
            $table->string('province_code', 10)->nullable();
            $table->string('regency_code', 10)->nullable();
            $table->string('district_code', 15)->nullable();
            $table->unsignedInteger('education_units')->default(0);
            $table->unsignedInteger('male_students')->default(0);
            $table->unsignedInteger('female_students')->default(0);
            $table->unsignedInteger('beneficiaries')->default(0);
            $table->unsignedInteger('allergies')->default(0);
            $table->unsignedInteger('phobias')->default(0);
            $table->unsignedInteger('intolerances')->default(0);
            $table->unsignedInteger('special_conditions')->default(0);
            $table->unsignedInteger('public_units')->default(0);
            $table->unsignedInteger('private_units')->default(0);
            $table->timestamp('source_pulled_at')->nullable();
            $table->string('source_url', 2048)->nullable();
            $table->timestamps();
            $table->index(['province_code', 'regency_code', 'district_code', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mbg_education_recaps');
    }
};
