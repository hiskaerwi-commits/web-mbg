<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MbgEducationRecap extends Model
{
    protected $fillable = [
        'year', 'level', 'province_code', 'regency_code', 'regency_name', 'district_code',
        'education_units', 'male_students', 'female_students', 'beneficiaries',
        'allergies', 'phobias', 'intolerances', 'special_conditions',
        'public_units', 'private_units', 'source_pulled_at', 'source_url',
    ];

    protected function casts(): array
    {
        return ['source_pulled_at' => 'datetime'];
    }

    public function getProvinceNameAttribute(): ?string
    {
        if (! $this->province_code) {
            return null;
        }

        return config('mbg_provinces.' . $this->province_code, $this->province_code);
    }

    /**
     * Import a decoded MBG recap `data` payload (provincial or regency granularity).
     * Shared by the `mbg:import-json` and `mbg:import-regencies` console commands.
     *
     * @param  array<mixed>  $data
     */
    public static function importPayload(array $data, ?string $sourceUrl = null): int
    {
        $count = 0;

        foreach ($data as $group) {
            foreach ($group['jenjang'] ?? [] as $recap) {
                static::query()->updateOrCreate(
                    [
                        'year' => $recap['tahun'],
                        'level' => $recap['jenjang'],
                        'province_code' => $recap['kode_prov'],
                        'regency_code' => $recap['kode_kabkota'] ?? null,
                        'district_code' => null,
                    ],
                    [
                        'regency_name' => $recap['kabupaten_kota'] ?? null,
                        'education_units' => $recap['jumlah_satuan_pendidikan'],
                        'male_students' => $recap['jumlah_laki'],
                        'female_students' => $recap['jumlah_perempuan'],
                        'beneficiaries' => $recap['jumlah_penerima_manfaat'],
                        'allergies' => $recap['jumlah_alergi'],
                        'phobias' => $recap['jumlah_fobia'],
                        'intolerances' => $recap['jumlah_intoleransi'],
                        'special_conditions' => $recap['jumlah_kondisi_khusus'],
                        'public_units' => $recap['jumlah_satpen_negeri'],
                        'private_units' => $recap['jumlah_satpen_swasta'],
                        'source_pulled_at' => $recap['date_pull'] ?? now(),
                        'source_url' => $sourceUrl,
                    ],
                );

                $count++;
            }
        }

        return $count;
    }
}
