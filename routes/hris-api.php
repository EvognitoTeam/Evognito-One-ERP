<?php

use App\Http\Middleware\SetApiLocale;
use App\Modules\HRIS\Http\Controllers\Api\AttendanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HRIS Mobile Attendance API
|--------------------------------------------------------------------------
|
| routes/api.php already receives the /api prefix from Laravel.
|
| Final endpoints:
|
| GET  /api/{organization}/hris/attendance/status
| POST /api/{organization}/hris/attendance/clock-in
| POST /api/{organization}/hris/attendance/clock-out
| GET  /api/{organization}/hris/attendance/history
| GET  /api/{organization}/hris/attendance/work-locations
|
*/

Route::middleware([
    'auth:api',
    SetApiLocale::class,
])
    ->prefix(
        '{organization:slug}/hris/attendance'
    )
    ->name(
        'api.hris.attendance.'
    )
    ->group(
        function () {
            Route::get(
                '/status',
                [
                    AttendanceController::class,
                    'status',
                ]
            )->name(
                'status'
            );

            Route::post(
                '/clock-in',
                [
                    AttendanceController::class,
                    'clockIn',
                ]
            )->name(
                'clock-in'
            );

            Route::post(
                '/clock-out',
                [
                    AttendanceController::class,
                    'clockOut',
                ]
            )->name(
                'clock-out'
            );

            Route::get(
                '/history',
                [
                    AttendanceController::class,
                    'history',
                ]
            )->name(
                'history'
            );

            Route::get(
                '/work-locations',
                [
                    AttendanceController::class,
                    'workLocations',
                ]
            )->name(
                'work-locations'
            );
        }
    );
