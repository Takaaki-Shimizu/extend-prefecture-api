<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrefectureController;

Route::get('/', function () {
    return view('prefecture.index');
});

Route::get('/prefecture', function () {
    return view('prefecture.index');
});

Route::post('/api/extract-prefecture', [PrefectureController::class, 'extract']);
