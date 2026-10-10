<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

require __DIR__ . '/sales-api.php';
require __DIR__ . '/hris-api.php';
