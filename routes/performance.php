<?php

use App\Modules\Performance\Http\Controllers\PerformanceConfigurationController;
use App\Modules\Performance\Http\Controllers\PerformanceDashboardController;
use App\Modules\Performance\Http\Controllers\PerformanceKpiController;
use App\Modules\Performance\Http\Controllers\PerformanceMasterDataImportController;
use App\Modules\Performance\Http\Controllers\PerformanceProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'organization.access', 'module:performance'])
    ->prefix('{organization:slug}/performance')
    ->name('performance.')
    ->group(function () {
        Route::get('/', [PerformanceDashboardController::class, 'index'])->name('dashboard');

        Route::get('/master-data', [PerformanceMasterDataImportController::class, 'index'])->name('master-data.index');
        Route::post('/master-data/import', [PerformanceMasterDataImportController::class, 'store'])->name('master-data.store');
        Route::get('/master-data/imports/{import}', [PerformanceMasterDataImportController::class, 'show'])->name('master-data.show');

        Route::get('/products', [PerformanceProductController::class, 'index'])->name('products.index');
        Route::post('/products', [PerformanceProductController::class, 'store'])->name('products.store');
        Route::post('/products/{product}/employees', [PerformanceProductController::class, 'assignEmployee'])->name('products.employees.store');
        Route::delete('/products/{product}/employees/{employee}', [PerformanceProductController::class, 'removeEmployee'])->name('products.employees.destroy');

        Route::get('/kpi', [PerformanceKpiController::class, 'index'])->name('kpi.index');
        Route::post('/kpi', [PerformanceKpiController::class, 'store'])->name('kpi.store');
        Route::post('/kpi/{kpi}/products', [PerformanceKpiController::class, 'assignProduct'])->name('kpi.products.store');
        Route::delete('/kpi/{kpi}/products/{assignment}', [PerformanceKpiController::class, 'removeProductAssignment'])->name('kpi.products.destroy');

        Route::get('/configuration', [PerformanceConfigurationController::class, 'index'])->name('configuration.index');
        Route::put('/configuration', [PerformanceConfigurationController::class, 'update'])->name('configuration.update');
    });
