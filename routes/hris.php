<?php

use App\Modules\HRIS\Http\Controllers\AnnualLeaveScheduleController;
use App\Modules\HRIS\Http\Controllers\AttendanceController;
use App\Modules\HRIS\Http\Controllers\AttendancePolicyController;
use App\Modules\HRIS\Http\Controllers\AttendanceReportController;
use App\Modules\HRIS\Http\Controllers\CalendarController;
use App\Modules\HRIS\Http\Controllers\DashboardController;
use App\Modules\HRIS\Http\Controllers\EmployeeController;
use App\Modules\HRIS\Http\Controllers\HolidayController;
use App\Modules\HRIS\Http\Controllers\LeaveController;
use App\Modules\HRIS\Http\Controllers\OrganizationStructureController;
use App\Modules\HRIS\Http\Controllers\PayrollController;
use App\Modules\HRIS\Http\Controllers\PositionScheduleController;
use App\Modules\HRIS\Http\Controllers\WorkLocationController;
use Illuminate\Support\Facades\Route;

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

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/',
                [
                    DashboardController::class,
                    'index',
                ]
            )->name(
                'dashboard'
            );


            /*
            |--------------------------------------------------------------------------
            | Calendar
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/calendar',
                [
                    CalendarController::class,
                    'index',
                ]
            )->name(
                'calendar.index'
            );


            /*
            |--------------------------------------------------------------------------
            | Annual Leave Schedule
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/calendar/annual-leave',
                [
                    AnnualLeaveScheduleController::class,
                    'index',
                ]
            )->name(
                'calendar.annual-leave.index'
            );

            Route::post(
                '/calendar/annual-leave',
                [
                    AnnualLeaveScheduleController::class,
                    'store',
                ]
            )->name(
                'calendar.annual-leave.store'
            );

            Route::put(
                '/calendar/annual-leave/{leaveSchedule}',
                [
                    AnnualLeaveScheduleController::class,
                    'update',
                ]
            )->name(
                'calendar.annual-leave.update'
            );

            Route::put(
                '/calendar/annual-leave/{leaveSchedule}/cancel',
                [
                    AnnualLeaveScheduleController::class,
                    'cancel',
                ]
            )->name(
                'calendar.annual-leave.cancel'
            );


            /*
            |--------------------------------------------------------------------------
            | Holidays
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/calendar/holidays',
                [
                    HolidayController::class,
                    'index',
                ]
            )->name(
                'holidays.index'
            );

            Route::post(
                '/calendar/holidays',
                [
                    HolidayController::class,
                    'store',
                ]
            )->name(
                'holidays.store'
            );

            Route::put(
                '/calendar/holidays/{holiday}',
                [
                    HolidayController::class,
                    'update',
                ]
            )->name(
                'holidays.update'
            );

            Route::delete(
                '/calendar/holidays/{holiday}',
                [
                    HolidayController::class,
                    'destroy',
                ]
            )->name(
                'holidays.destroy'
            );


            /*
            |--------------------------------------------------------------------------
            | Employees
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/employees',
                [
                    EmployeeController::class,
                    'index',
                ]
            )->name(
                'employees.index'
            );

            Route::post(
                '/employees',
                [
                    EmployeeController::class,
                    'store',
                ]
            )->name(
                'employees.store'
            );

            Route::put(
                '/employees/{employee}',
                [
                    EmployeeController::class,
                    'update',
                ]
            )->name(
                'employees.update'
            );

            Route::put(
                '/employees/{employee}/hr-profile',
                [
                    EmployeeController::class,
                    'updateProfile',
                ]
            )->name(
                'employees.profile.update'
            );


            /*
            |--------------------------------------------------------------------------
            | Attendance
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/attendance',
                [
                    AttendanceController::class,
                    'index',
                ]
            )->name(
                'attendance.index'
            );

            Route::post(
                '/attendance',
                [
                    AttendanceController::class,
                    'store',
                ]
            )->name(
                'attendance.store'
            );

            Route::put(
                '/attendance/{attendance}',
                [
                    AttendanceController::class,
                    'update',
                ]
            )->name(
                'attendance.update'
            );


            /*
            |--------------------------------------------------------------------------
            | Leave
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/leave',
                [
                    LeaveController::class,
                    'index',
                ]
            )->name(
                'leave.index'
            );

            Route::post(
                '/leave',
                [
                    LeaveController::class,
                    'store',
                ]
            )->name(
                'leave.store'
            );

            Route::put(
                '/leave/{leave}',
                [
                    LeaveController::class,
                    'update',
                ]
            )->name(
                'leave.update'
            );

            Route::put(
                '/leave/{leave}/status',
                [
                    LeaveController::class,
                    'updateStatus',
                ]
            )->name(
                'leave.status'
            );

            Route::delete(
                '/leave/{leave}',
                [
                    LeaveController::class,
                    'destroy',
                ]
            )->name(
                'leave.destroy'
            );

            Route::get(
                '/leave/{leave}/attachment',
                [
                    LeaveController::class,
                    'attachment',
                ]
            )->name(
                'leave.attachment'
            );


            /*
            |--------------------------------------------------------------------------
            | Organization Structure
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/configuration/organization-structure',
                [
                    OrganizationStructureController::class,
                    'index',
                ]
            )->name(
                'configuration.organization-structure.index'
            );

            Route::post(
                '/configuration/branches',
                [
                    OrganizationStructureController::class,
                    'storeBranch',
                ]
            )->name(
                'configuration.branches.store'
            );

            Route::put(
                '/configuration/branches/{branch}',
                [
                    OrganizationStructureController::class,
                    'updateBranch',
                ]
            )->name(
                'configuration.branches.update'
            );

            Route::post(
                '/configuration/departments',
                [
                    OrganizationStructureController::class,
                    'storeDepartment',
                ]
            )->name(
                'configuration.departments.store'
            );

            Route::put(
                '/configuration/departments/{department}',
                [
                    OrganizationStructureController::class,
                    'updateDepartment',
                ]
            )->name(
                'configuration.departments.update'
            );

            Route::post(
                '/configuration/positions',
                [
                    OrganizationStructureController::class,
                    'storePosition',
                ]
            )->name(
                'configuration.positions.store'
            );

            Route::put(
                '/configuration/positions/{position}',
                [
                    OrganizationStructureController::class,
                    'updatePosition',
                ]
            )->name(
                'configuration.positions.update'
            );


            /*
            |--------------------------------------------------------------------------
            | Position Schedule
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/configuration/position-schedules',
                [
                    PositionScheduleController::class,
                    'index',
                ]
            )->name(
                'configuration.position-schedules.index'
            );

            Route::put(
                '/configuration/position-schedules/{position}',
                [
                    PositionScheduleController::class,
                    'update',
                ]
            )->name(
                'configuration.position-schedules.update'
            );

            Route::post(
                '/configuration/position-schedules/apply-default',
                [
                    PositionScheduleController::class,
                    'applyDefault',
                ]
            )->name(
                'configuration.position-schedules.apply-default'
            );

            /*
|--------------------------------------------------------------------------
| Payroll
|--------------------------------------------------------------------------
*/

            Route::get(
                '/payroll',
                [
                    PayrollController::class,
                    'index',
                ]
            )->name(
                'payroll.index'
            );


            Route::post(
                '/payroll/periods',
                [
                    PayrollController::class,
                    'storePeriod',
                ]
            )->name(
                'payroll.periods.store'
            );


            Route::post(
                '/payroll/periods/{payrollPeriod}/generate',
                [
                    PayrollController::class,
                    'generate',
                ]
            )->name(
                'payroll.generate'
            );


            Route::put(
                '/payroll/{payroll}/paid',
                [
                    PayrollController::class,
                    'markPaid',
                ]
            )->name(
                'payroll.mark-paid'
            );


            /*
|--------------------------------------------------------------------------
| Compensation
|--------------------------------------------------------------------------
*/

            Route::get(
                '/payroll/compensation',
                [
                    PayrollController::class,
                    'compensation',
                ]
            )->name(
                'payroll.compensation'
            );


            Route::put(
                '/payroll/compensation/{employee}',
                [
                    PayrollController::class,
                    'saveCompensation',
                ]
            )->name(
                'payroll.compensation.update'
            );


            /*
            |--------------------------------------------------------------------------
            | Work Locations
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/configuration/work-locations',
                [
                    WorkLocationController::class,
                    'index',
                ]
            )->name(
                'configuration.work-locations.index'
            );

            Route::post(
                '/configuration/work-locations',
                [
                    WorkLocationController::class,
                    'store',
                ]
            )->name(
                'configuration.work-locations.store'
            );

            Route::put(
                '/configuration/work-locations/{workLocation}',
                [
                    WorkLocationController::class,
                    'update',
                ]
            )->name(
                'configuration.work-locations.update'
            );

            Route::put(
                '/configuration/work-locations/{workLocation}/employees',
                [
                    WorkLocationController::class,
                    'assignEmployees',
                ]
            )->name(
                'configuration.work-locations.assign'
            );


            /*
            |--------------------------------------------------------------------------
            | Attendance Policy
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/configuration/attendance-policy',
                [
                    AttendancePolicyController::class,
                    'index',
                ]
            )->name(
                'configuration.attendance-policy.index'
            );

            Route::put(
                '/configuration/attendance-policy',
                [
                    AttendancePolicyController::class,
                    'update',
                ]
            )->name(
                'configuration.attendance-policy.update'
            );

            /*
|--------------------------------------------------------------------------
| Attendance Report
|--------------------------------------------------------------------------
*/

            Route::get(
                '/attendance/report',
                [
                    AttendanceReportController::class,
                    'index',
                ]
            )->name(
                'attendance.report.index'
            );


            Route::get(
                '/attendance/report/export/detail',
                [
                    AttendanceReportController::class,
                    'exportDetailed',
                ]
            )->name(
                'attendance.report.export-detailed'
            );


            Route::get(
                '/attendance/report/export/summary',
                [
                    AttendanceReportController::class,
                    'exportSummary',
                ]
            )->name(
                'attendance.report.export-summary'
            );
        }
    );
