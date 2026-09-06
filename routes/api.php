<?php

use App\Http\Controllers\Api\MbgEducationRecapController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/mbg/education-recaps', [MbgEducationRecapController::class, 'index']);
