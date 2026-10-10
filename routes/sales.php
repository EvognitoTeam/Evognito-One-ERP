<?php

use App\Modules\Sales\Http\Controllers\SalesConfigurationController;
use App\Modules\Sales\Http\Controllers\SalesCustomerController;
use App\Modules\Sales\Http\Controllers\SalesDashboardController;
use App\Modules\Sales\Http\Controllers\SalesMonitoringController;
use App\Modules\Sales\Http\Controllers\SalesProductController;
use App\Modules\Sales\Http\Controllers\SalesRecordController;
use App\Modules\Sales\Http\Controllers\SalesReportController;
use App\Modules\Sales\Http\Controllers\SalesTeamController;
use App\Modules\Sales\Http\Controllers\SalesVisitController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'organization.access',
    'module:sales_force',
])
    ->prefix('{organization:slug}/sales')
    ->name('sales.')
    ->group(function () {
        Route::get('/', [SalesDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/team', [SalesTeamController::class, 'index'])
            ->name('team.index');
        Route::post('/team', [SalesTeamController::class, 'store'])
            ->name('team.store');
        Route::put('/team/{profile}', [SalesTeamController::class, 'update'])
            ->whereNumber('profile')
            ->name('team.update');
        Route::delete('/team/{profile}', [SalesTeamController::class, 'destroy'])
            ->whereNumber('profile')
            ->name('team.destroy');

        Route::get('/customers', [SalesCustomerController::class, 'index'])
            ->name('customers.index');
        Route::post('/customers', [SalesCustomerController::class, 'store'])
            ->name('customers.store');
        Route::put('/customers/{customer}', [SalesCustomerController::class, 'update'])
            ->whereNumber('customer')
            ->name('customers.update');
        Route::delete('/customers/{customer}', [SalesCustomerController::class, 'destroy'])
            ->whereNumber('customer')
            ->name('customers.destroy');

        Route::get('/products', [SalesProductController::class, 'index'])
            ->name('products.index');
        Route::post('/products', [SalesProductController::class, 'store'])
            ->name('products.store');
        Route::put('/products/{product}', [SalesProductController::class, 'update'])
            ->whereNumber('product')
            ->name('products.update');
        Route::delete('/products/{product}', [SalesProductController::class, 'destroy'])
            ->whereNumber('product')
            ->name('products.destroy');

        Route::get('/visits', [SalesVisitController::class, 'index'])
            ->name('visits.index');
        Route::post('/visits', [SalesVisitController::class, 'store'])
            ->name('visits.store');
        Route::get('/visits/{visit}', [SalesVisitController::class, 'show'])
            ->whereNumber('visit')
            ->name('visits.show');
        Route::put('/visits/{visit}', [SalesVisitController::class, 'update'])
            ->whereNumber('visit')
            ->name('visits.update');
        Route::delete('/visits/{visit}', [SalesVisitController::class, 'destroy'])
            ->whereNumber('visit')
            ->name('visits.destroy');
        Route::post('/visits/{visit}/receipts', [SalesVisitController::class, 'uploadReceipt'])
            ->whereNumber('visit')
            ->name('visits.receipts.store');
        Route::delete('/receipts/{receipt}', [SalesVisitController::class, 'destroyReceipt'])
            ->whereNumber('receipt')
            ->name('receipts.destroy');

        Route::get('/monitoring', [SalesMonitoringController::class, 'index'])
            ->name('monitoring.index');
        Route::get('/monitoring/data', [SalesMonitoringController::class, 'data'])
            ->name('monitoring.data');

        Route::get('/records', [SalesRecordController::class, 'index'])
            ->name('records.index');
        Route::post('/records/import', [SalesRecordController::class, 'import'])
            ->name('records.import');
        Route::get('/records/{record}/edit', [SalesRecordController::class, 'edit'])
            ->whereNumber('record')
            ->name('records.edit');
        Route::put('/records/{record}', [SalesRecordController::class, 'update'])
            ->whereNumber('record')
            ->name('records.update');
        Route::delete('/records/{record}', [SalesRecordController::class, 'destroy'])
            ->whereNumber('record')
            ->name('records.destroy');

        Route::get('/reports', [SalesReportController::class, 'index'])
            ->name('reports.index');
        Route::get('/reports/export', [SalesReportController::class, 'export'])
            ->name('reports.export');

        Route::get('/configuration', [SalesConfigurationController::class, 'index'])
            ->name('configuration.index');
        Route::post('/configuration/areas', [SalesConfigurationController::class, 'storeArea'])
            ->name('configuration.areas.store');
        Route::put('/configuration/areas/{area}', [SalesConfigurationController::class, 'updateArea'])
            ->whereNumber('area')
            ->name('configuration.areas.update');
        Route::delete('/configuration/areas/{area}', [SalesConfigurationController::class, 'destroyArea'])
            ->whereNumber('area')
            ->name('configuration.areas.destroy');
        Route::put('/configuration/policy', [SalesConfigurationController::class, 'updatePolicy'])
            ->name('configuration.policy.update');
    });
