<?php

use Illuminate\Support\Facades\Route;

// Route Vue.js
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$'); // Điều kiện để không khớp với các route bắt đầu bằng "api"