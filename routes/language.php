<?php

use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::post('/language', [LanguageController::class, 'update'])
    ->name('language.update');
