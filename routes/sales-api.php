<?php

use App\Http\Middleware\SetApiLocale;
use App\Modules\Sales\Http\Controllers\Api\SalesMobileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', SetApiLocale::class])
    ->prefix('{organization:slug}/sales')
    ->name('api.sales.')
    ->group(function () {
        Route::get('/bootstrap', [SalesMobileController::class, 'bootstrap'])->name('bootstrap');
        Route::get('/visits', [SalesMobileController::class, 'visits'])->name('visits');
        Route::post('/visits/{visit}/clock-in', [SalesMobileController::class, 'clockIn'])->whereNumber('visit')->name('visits.clock-in');
        Route::post('/visits/{visit}/clock-out', [SalesMobileController::class, 'clockOut'])->whereNumber('visit')->name('visits.clock-out');
        Route::post('/visits/{visit}/receipts', [SalesMobileController::class, 'uploadReceipt'])->whereNumber('visit')->name('visits.receipts');
        Route::post('/tracking/location', [SalesMobileController::class, 'track'])->name('tracking.location');
    });
