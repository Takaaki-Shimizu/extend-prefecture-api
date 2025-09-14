<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('prefecture.index');
});

Route::get('/prefecture', function () {
    return view('prefecture.index');
});
