<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/swagger');
});

// SwaggerUI用のルート
Route::get('/swagger', function () {
    return view('swagger');
});
