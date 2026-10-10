<?php

use App\Modules\HRIS\Http\Controllers\AttendanceMapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HRIS Daily Attendance Map
|--------------------------------------------------------------------------
|
| URL:
| /{organization}/hris/attendance/map?date=2026-10-10
|
*/

Route::middleware([
    'auth',
    'organization.access',
    'module:hris',
])
    ->prefix(
        '{organization:slug}/hris'
    )
    ->name('hris.')
    ->group(
        function () {
            Route::get(
                '/attendance/map',
                [
                    AttendanceMapController::class,
                    'index',
                ]
            )->name(
                'attendance.map'
            );
        }
    );
