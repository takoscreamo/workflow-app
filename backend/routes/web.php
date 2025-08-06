<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// SwaggerUI用のルート
Route::get('/swagger', function () {
    return view('swagger');
});
