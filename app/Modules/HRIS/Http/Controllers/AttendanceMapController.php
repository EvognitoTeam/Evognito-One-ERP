<?php

namespace App\Modules\HRIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Organization;
use App\Modules\HRIS\Models\HrisAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceMapController extends Controller
{
    public function index(
        Request $request,
        Organization $organization
    ): View {
        $validated = $request->validate([
            'date' => [
                'nullable',
                'date_format:Y-m-d',
            ],
        ]);

        $date = Carbon::createFromFormat(
            'Y-m-d',
            $validated['date']
                ?? now()->toDateString()
        )->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Active Employees
        |--------------------------------------------------------------------------
        |
        | Digunakan agar halaman tidak hanya menampilkan employee yang sudah
        | presensi. Employee yang belum presensi tetap muncul di side panel.
        |
        */

        $employees = Employee::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(
                'status',
                'active'
            )
            ->with([
                'user',
                'position',
                'branch',
            ])
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Attendance for Selected Date
        |--------------------------------------------------------------------------
        */

        $attendances = HrisAttendance::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->whereDate(
                'attendance_date',
                $date->toDateString()
            )
            ->with([
                'employee.user',
                'employee.position',
                'employee.branch',
                'workLocation',
            ])
            ->orderBy('clock_in')
            ->get();

        $attendanceByEmployee = $attendances
            ->keyBy('employee_id');

        /*
        |--------------------------------------------------------------------------
        | Employee Rows
        |--------------------------------------------------------------------------
        */

        $rows = $employees
            ->map(
                function (
                    Employee $employee
                ) use (
                    $attendanceByEmployee
                ) {
                    $attendance = $attendanceByEmployee
                        ->get(
                            $employee->id
                        );

                    return [
                        'employee_id' =>
                            $employee->id,

                        'employee_name' =>
                            $this->employeeName(
                                $employee
                            ),

                        'employee_number' =>
                            $employee->employee_number
                            ?? null,

                        'position' =>
                            $employee->position?->name
                            ?? null,

                        'branch' =>
                            $employee->branch?->name
                            ?? null,

                        'attendance' =>
                            $attendance
                                ? $this->attendancePayload(
                                    $attendance
                                )
                                : null,
                    ];
                }
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Include attendance records for employees not present in active list
        |--------------------------------------------------------------------------
        |
        | Berguna untuk historical date ketika employee sekarang sudah inactive
        | tetapi attendance lama tetap harus bisa dilihat.
        |
        */

        $knownEmployeeIds = $employees
            ->pluck('id')
            ->map(
                fn ($id) => (int) $id
            );

        foreach (
            $attendances
                ->whereNotIn(
                    'employee_id',
                    $knownEmployeeIds
                )
            as $attendance
        ) {
            $employee = $attendance->employee;

            $rows->push([
                'employee_id' =>
                    $attendance->employee_id,

                'employee_name' =>
                    $employee
                        ? $this->employeeName(
                            $employee
                        )
                        : '-',

                'employee_number' =>
                    $employee?->employee_number
                    ?? null,

                'position' =>
                    $employee?->position?->name
                    ?? null,

                'branch' =>
                    $employee?->branch?->name
                    ?? null,

                'attendance' =>
                    $this->attendancePayload(
                        $attendance
                    ),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Map Records
        |--------------------------------------------------------------------------
        */

        $mapAttendances = $attendances
            ->map(
                fn (
                    HrisAttendance $attendance
                ) => $this->attendancePayload(
                    $attendance
                )
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Unique Work Locations
        |--------------------------------------------------------------------------
        */

        $workLocations = $attendances
            ->pluck('workLocation')
            ->filter()
            ->unique('id')
            ->map(
                fn ($location) => [
                    'id' =>
                        $location->id,

                    'name' =>
                        $location->name,

                    'address' =>
                        $location->address,

                    'latitude' =>
                        $location->latitude !== null
                            ? (float) $location->latitude
                            : null,

                    'longitude' =>
                        $location->longitude !== null
                            ? (float) $location->longitude
                            : null,

                    'radius_meters' =>
                        (int) $location->radius_meters,

                    'geofence_enabled' =>
                        (bool) $location->geofence_enabled,
                ]
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $attendedCount =
            $attendances->count();

        $withGpsCount =
            $attendances
                ->filter(
                    fn (
                        HrisAttendance $attendance
                    ) =>
                        $attendance
                            ->clock_in_latitude
                        !== null
                        ||
                        $attendance
                            ->clock_out_latitude
                        !== null
                )
                ->count();

        $insideCount =
            $attendances
                ->filter(
                    function (
                        HrisAttendance $attendance
                    ) {
                        return
                            $attendance
                                ->clock_in_within_geofence
                            === true
                            ||
                            $attendance
                                ->clock_out_within_geofence
                            === true;
                    }
                )
                ->count();

        $outsideCount =
            $attendances
                ->filter(
                    function (
                        HrisAttendance $attendance
                    ) {
                        return
                            $attendance
                                ->clock_in_within_geofence
                            === false
                            ||
                            $attendance
                                ->clock_out_within_geofence
                            === false;
                    }
                )
                ->count();

        $lateCount =
            $attendances
                ->where(
                    'is_late',
                    true
                )
                ->count();

        $activeEmployeeCount =
            $employees->count();

        $notRecordedCount =
            max(
                0,
                $activeEmployeeCount
                -
                $attendances
                    ->whereIn(
                        'employee_id',
                        $employees->pluck('id')
                    )
                    ->count()
            );

        $previousDate =
            $date
                ->copy()
                ->subDay()
                ->toDateString();

        $nextDate =
            $date
                ->copy()
                ->addDay()
                ->toDateString();

        return view(
            'modules.hris.attendance.map',
            compact(
                'organization',
                'date',
                'rows',
                'mapAttendances',
                'workLocations',
                'activeEmployeeCount',
                'attendedCount',
                'withGpsCount',
                'insideCount',
                'outsideCount',
                'lateCount',
                'notRecordedCount',
                'previousDate',
                'nextDate'
            )
        );
    }


    private function attendancePayload(
        HrisAttendance $attendance
    ): array {
        $attendance->loadMissing([
            'employee.user',
            'employee.position',
            'employee.branch',
            'workLocation',
        ]);

        $employee =
            $attendance->employee;

        return [
            'id' =>
                $attendance->id,

            'employee_id' =>
                $attendance->employee_id,

            'employee_name' =>
                $employee
                    ? $this->employeeName(
                        $employee
                    )
                    : '-',

            'employee_number' =>
                $employee?->employee_number
                ?? null,

            'position' =>
                $employee?->position?->name
                ?? null,

            'branch' =>
                $employee?->branch?->name
                ?? null,

            'attendance_date' =>
                $attendance
                    ->attendance_date
                    ?->toDateString(),

            'clock_in' =>
                $attendance
                    ->clock_in
                    ?->format('H:i:s'),

            'clock_out' =>
                $attendance
                    ->clock_out
                    ?->format('H:i:s'),

            'status' =>
                $attendance->status,

            'method' =>
                $attendance->method,

            'is_late' =>
                (bool) $attendance->is_late,

            'late_minutes' =>
                (int) $attendance->late_minutes,

            'clock_in_location' => [
                'latitude' =>
                    $attendance
                        ->clock_in_latitude !== null
                        ? (float)
                        $attendance
                            ->clock_in_latitude
                        : null,

                'longitude' =>
                    $attendance
                        ->clock_in_longitude !== null
                        ? (float)
                        $attendance
                            ->clock_in_longitude
                        : null,

                'accuracy_meters' =>
                    $attendance
                        ->clock_in_accuracy_meters !== null
                        ? (float)
                        $attendance
                            ->clock_in_accuracy_meters
                        : null,

                'distance_meters' =>
                    $attendance
                        ->clock_in_distance_meters !== null
                        ? (float)
                        $attendance
                            ->clock_in_distance_meters
                        : null,

                'within_geofence' =>
                    $attendance
                        ->clock_in_within_geofence,
            ],

            'clock_out_location' => [
                'latitude' =>
                    $attendance
                        ->clock_out_latitude !== null
                        ? (float)
                        $attendance
                            ->clock_out_latitude
                        : null,

                'longitude' =>
                    $attendance
                        ->clock_out_longitude !== null
                        ? (float)
                        $attendance
                            ->clock_out_longitude
                        : null,

                'accuracy_meters' =>
                    $attendance
                        ->clock_out_accuracy_meters !== null
                        ? (float)
                        $attendance
                            ->clock_out_accuracy_meters
                        : null,

                'distance_meters' =>
                    $attendance
                        ->clock_out_distance_meters !== null
                        ? (float)
                        $attendance
                            ->clock_out_distance_meters
                        : null,

                'within_geofence' =>
                    $attendance
                        ->clock_out_within_geofence,
            ],

            'work_location' =>
                $attendance->workLocation
                    ? [
                        'id' =>
                            $attendance
                                ->workLocation
                                ->id,

                        'name' =>
                            $attendance
                                ->workLocation
                                ->name,

                        'address' =>
                            $attendance
                                ->workLocation
                                ->address,

                        'latitude' =>
                            $attendance
                                ->workLocation
                                ->latitude !== null
                                ? (float)
                                $attendance
                                    ->workLocation
                                    ->latitude
                                : null,

                        'longitude' =>
                            $attendance
                                ->workLocation
                                ->longitude !== null
                                ? (float)
                                $attendance
                                    ->workLocation
                                    ->longitude
                                : null,

                        'radius_meters' =>
                            (int)
                            $attendance
                                ->workLocation
                                ->radius_meters,
                    ]
                    : null,
        ];
    }


    private function employeeName(
        Employee $employee
    ): string {
        return
            $employee->name
            ??
            $employee->user?->name
            ??
            'Employee';
    }
}
