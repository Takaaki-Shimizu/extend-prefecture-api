<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrefectureController;

Route::post('/extract-prefecture', [PrefectureController::class, 'extract']);