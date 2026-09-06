<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MbgEducationRecap;
use Illuminate\Http\Request;

class MbgEducationRecapController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'year' => ['nullable', 'integer'],
            'level' => ['nullable', 'string', 'max:20'],
            'province_code' => ['nullable', 'string', 'max:10'],
            'regency_code' => ['nullable', 'string', 'max:10'],
            'district_code' => ['nullable', 'string', 'max:15'],
        ]);

        $recaps = MbgEducationRecap::query()
            ->when($data['year'] ?? null, fn ($query, $year) => $query->where('year', $year))
            ->when($data['level'] ?? null, fn ($query, $level) => $query->where('level', strtoupper($level)))
            ->when($data['province_code'] ?? null, fn ($query, $code) => $query->where('province_code', $code))
            ->when($data['regency_code'] ?? null, fn ($query, $code) => $query->where('regency_code', $code))
            ->when($data['district_code'] ?? null, fn ($query, $code) => $query->where('district_code', $code))
            ->orderBy('year', 'desc')->orderBy('level')->get();

        return response()->json(['data' => $recaps]);
    }
}
