<?php

namespace App\Modules\HRIS\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Organization;
use App\Modules\HRIS\Models\HrisAttendance;
use App\Modules\HRIS\Models\HrisAttendancePolicy;
use App\Modules\HRIS\Models\HrisLeaveRequest;
use App\Modules\HRIS\Models\HrisLeaveSchedule;
use App\Modules\HRIS\Models\HrisPositionSetting;
use App\Modules\HRIS\Services\AttendanceService;
use App\Modules\HRIS\Services\GeofenceService;
use App\Modules\HRIS\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly GeofenceService $geofenceService,
        private readonly HolidayService $holidayService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    public function status(
        Request $request,
        Organization $organization
    ): JsonResponse {
        $employee =
            $this->resolveEmployee(
                $request,
                $organization
            );

        $policy =
            HrisAttendancePolicy::resolveFor(
                $organization
            );

        $positionSetting =
            $employee->position_id
                ? HrisPositionSetting::query()
                    ->where(
                        'organization_id',
                        $organization->id
                    )
                    ->where(
                        'position_id',
                        $employee->position_id
                    )
                    ->first()
                : null;

        $openAttendance =
            HrisAttendance::query()
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->where(
                    'employee_id',
                    $employee->id
                )
                ->where(
                    'status',
                    'open'
                )
                ->latest(
                    'clock_in'
                )
                ->first();

        $todayAttendance =
            HrisAttendance::query()
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->where(
                    'employee_id',
                    $employee->id
                )
                ->whereDate(
                    'attendance_date',
                    now()->toDateString()
                )
                ->latest('id')
                ->first();

        $leaveState =
            $this->leaveState(
                $organization,
                $employee,
                now()
            );

        $holiday =
            $this
                ->holidayService
                ->findForDate(
                    $organization,
                    now()
                );

        $workDays =
            collect(
                $policy->work_days
                ?: [1, 2, 3, 4, 5]
            )
                ->map(
                    fn ($day) =>
                        (int) $day
                );

        $isWorkingDay =
            $workDays->contains(
                now()->isoWeekday()
            );

        $structurallyAllowed =
            !$leaveState
            &&
            (
                $isWorkingDay
                ||
                (
                    $policy->allow_attendance_on_non_workdays
                    ?? false
                )
            )
            &&
            (
                !$holiday
                ||
                (
                    $policy->allow_attendance_on_holidays
                    ?? false
                )
            );

        $locations =
            $this
                ->geofenceService
                ->eligibleLocations(
                    $organization,
                    $employee
                )
                ->map(
                    fn ($location) => [
                        'id' =>
                            $location->id,

                        'ulid' =>
                            $location->ulid,

                        'name' =>
                            $location->name,

                        'type' =>
                            $location->type,

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

        return response()->json([
            'success' => true,

            'data' => [
                'server_time' =>
                    now()->toIso8601String(),

                'employee' => [
                    'id' =>
                        $employee->id,

                    'name' =>
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
                ],

                'state' => [
                    'has_open_attendance' =>
                        (bool) $openAttendance,

                    'has_today_attendance' =>
                        (bool) $todayAttendance,

                    'can_clock_in' =>
                        !$openAttendance
                        &&
                        !$todayAttendance
                        &&
                        $structurallyAllowed,

                    'can_clock_out' =>
                        (bool) $openAttendance,

                    'leave' =>
                        $leaveState,

                    'holiday' =>
                        $holiday
                            ? [
                                'name' =>
                                    $holiday->name,

                                'date' =>
                                    now()
                                        ->toDateString(),
                            ]
                            : null,

                    'working_day' =>
                        $isWorkingDay,
                ],

                'schedule' => [
                    'clock_in' =>
                        $positionSetting?->clock_in
                        ?? '08:00:00',

                    'clock_out' =>
                        $positionSetting?->clock_out
                        ?? '17:00:00',

                    'late_tolerance_minutes' =>
                        (int) (
                            $positionSetting
                                ?->late_tolerance_minutes
                            ?? 0
                        ),

                    'mobile_position' =>
                        (bool) (
                            $positionSetting
                                ?->is_mobile
                            ?? false
                        ),
                ],

                'policy' => [
                    'require_location' =>
                        (bool) (
                            $policy->require_location
                            ?? true
                        ),

                    'require_photo_clock_in' =>
                        (bool) (
                            $policy->require_photo_clock_in
                            ?? true
                        ),

                    'require_photo_clock_out' =>
                        (bool) (
                            $policy->require_photo_clock_out
                            ?? true
                        ),

                    'maximum_gps_accuracy_meters' =>
                        (int) (
                            $policy->maximum_gps_accuracy_meters
                            ?? 100
                        ),

                    'geofence_mode' =>
                        $policy->geofence_mode
                        ?? 'mobile_remote',

                    'allow_early_clock_out' =>
                        (bool) (
                            $policy->allow_early_clock_out
                            ?? true
                        ),
                ],

                'open_attendance' =>
                    $openAttendance
                        ? $this->attendancePayload(
                            $openAttendance
                        )
                        : null,

                'today_attendance' =>
                    $todayAttendance
                        ? $this->attendancePayload(
                            $todayAttendance
                        )
                        : null,

                'work_locations' =>
                    $locations,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Clock In
    |--------------------------------------------------------------------------
    */

    public function clockIn(
        Request $request,
        Organization $organization
    ): JsonResponse {
        $employee =
            $this->resolveEmployee(
                $request,
                $organization
            );

        $policy =
            HrisAttendancePolicy::resolveFor(
                $organization
            );

        $validated =
            $request->validate([
                'latitude' => [
                    (
                        $policy->require_location
                        ?? true
                    )
                        ? 'required'
                        : 'nullable',
                    'numeric',
                    'between:-90,90',
                ],

                'longitude' => [
                    (
                        $policy->require_location
                        ?? true
                    )
                        ? 'required'
                        : 'nullable',
                    'numeric',
                    'between:-180,180',
                ],

                'accuracy' => [
                    (
                        $policy->require_location
                        ?? true
                    )
                        ? 'required'
                        : 'nullable',
                    'numeric',
                    'min:0',
                    'max:10000',
                ],

                'photo' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120',
                ],

                'device_id' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'device_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'device_os' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'app_version' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'notes' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],
            ]);

        if (
            (
                $policy->require_photo_clock_in
                ?? true
            )
            &&
            !$request->hasFile('photo')
        ) {
            throw ValidationException::withMessages([
                'photo' => __(
                    'attendance.errors.photo_in_required'
                ),
            ]);
        }

        $existing =
            HrisAttendance::query()
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->where(
                    'employee_id',
                    $employee->id
                )
                ->whereDate(
                    'attendance_date',
                    now()->toDateString()
                )
                ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'attendance' => __(
                    'attendance.errors.duplicate'
                ),
            ]);
        }

        $photoPath = null;

        try {
            if (
                $request->hasFile(
                    'photo'
                )
            ) {
                $photoPath =
                    $request
                        ->file('photo')
                        ->store(
                            'hris/attendance/'
                            .$organization->id
                            .'/'
                            .$employee->id,
                            'public'
                        );
            }

            $attendance =
                $this
                    ->attendanceService
                    ->save(
                        $organization,
                        $employee,
                        [
                            'clock_in' =>
                                now(),

                            'method' =>
                                'mobile',

                            'photo_in' =>
                                $photoPath,

                            'clock_in_latitude' =>
                                $validated['latitude']
                                ?? null,

                            'clock_in_longitude' =>
                                $validated['longitude']
                                ?? null,

                            'clock_in_accuracy' =>
                                $validated['accuracy']
                                ?? null,

                            'notes' =>
                                $validated['notes']
                                ?? null,

                            'metadata' =>
                                $this->metadata(
                                    $request,
                                    $validated
                                ),
                        ],
                        null,
                        $request->user()?->id,
                        true
                    );

            return response()->json([
                'success' => true,

                'message' =>
                    __('attendance.clock_in_success'),

                'data' =>
                    $this->attendancePayload(
                        $attendance
                    ),
            ], 201);
        } catch (Throwable $e) {
            if ($photoPath) {
                Storage::disk('public')
                    ->delete(
                        $photoPath
                    );
            }

            throw $e;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Clock Out
    |--------------------------------------------------------------------------
    */

    public function clockOut(
        Request $request,
        Organization $organization
    ): JsonResponse {
        $employee =
            $this->resolveEmployee(
                $request,
                $organization
            );

        $policy =
            HrisAttendancePolicy::resolveFor(
                $organization
            );

        $attendance =
            HrisAttendance::query()
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->where(
                    'employee_id',
                    $employee->id
                )
                ->where(
                    'status',
                    'open'
                )
                ->latest(
                    'clock_in'
                )
                ->first();

        if (!$attendance) {
            throw ValidationException::withMessages([
                'attendance' => __(
                    'attendance.errors.no_open_attendance'
                ),
            ]);
        }

        $validated =
            $request->validate([
                'latitude' => [
                    (
                        $policy->require_location
                        ?? true
                    )
                        ? 'required'
                        : 'nullable',
                    'numeric',
                    'between:-90,90',
                ],

                'longitude' => [
                    (
                        $policy->require_location
                        ?? true
                    )
                        ? 'required'
                        : 'nullable',
                    'numeric',
                    'between:-180,180',
                ],

                'accuracy' => [
                    (
                        $policy->require_location
                        ?? true
                    )
                        ? 'required'
                        : 'nullable',
                    'numeric',
                    'min:0',
                    'max:10000',
                ],

                'photo' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120',
                ],

                'device_id' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'device_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'device_os' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'app_version' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'notes' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],
            ]);

        if (
            (
                $policy->require_photo_clock_out
                ?? true
            )
            &&
            !$request->hasFile('photo')
        ) {
            throw ValidationException::withMessages([
                'photo' => __(
                    'attendance.errors.photo_out_required'
                ),
            ]);
        }

        $photoPath = null;

        try {
            if (
                $request->hasFile(
                    'photo'
                )
            ) {
                $photoPath =
                    $request
                        ->file('photo')
                        ->store(
                            'hris/attendance/'
                            .$organization->id
                            .'/'
                            .$employee->id,
                            'public'
                        );
            }

            $metadata =
                is_array(
                    $attendance->metadata
                )
                    ? $attendance->metadata
                    : [];

            $metadata['clock_out'] =
                $this->metadata(
                    $request,
                    $validated
                );

            $updated =
                $this
                    ->attendanceService
                    ->save(
                        $organization,
                        $employee,
                        [
                            'clock_in' =>
                                $attendance->clock_in,

                            'clock_out' =>
                                now(),

                            'method' =>
                                'mobile',

                            'photo_in' =>
                                $attendance->photo_in,

                            'photo_out' =>
                                $photoPath,

                            'clock_in_latitude' =>
                                $attendance->clock_in_latitude,

                            'clock_in_longitude' =>
                                $attendance->clock_in_longitude,

                            'clock_in_accuracy' =>
                                $attendance
                                    ->clock_in_accuracy_meters,

                            'clock_out_latitude' =>
                                $validated['latitude']
                                ?? null,

                            'clock_out_longitude' =>
                                $validated['longitude']
                                ?? null,

                            'clock_out_accuracy' =>
                                $validated['accuracy']
                                ?? null,

                            'notes' =>
                                $validated['notes']
                                ??
                                $attendance->notes,

                            'metadata' =>
                                $metadata,
                        ],
                        $attendance,
                        $request->user()?->id,
                        true
                    );

            return response()->json([
                'success' => true,

                'message' =>
                    __('attendance.clock_out_success'),

                'data' =>
                    $this->attendancePayload(
                        $updated
                    ),
            ]);
        } catch (Throwable $e) {
            if ($photoPath) {
                Storage::disk('public')
                    ->delete(
                        $photoPath
                    );
            }

            throw $e;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | History
    |--------------------------------------------------------------------------
    */

    public function history(
        Request $request,
        Organization $organization
    ): JsonResponse {
        $employee =
            $this->resolveEmployee(
                $request,
                $organization
            );

        $validated =
            $request->validate([
                'from' => [
                    'nullable',
                    'date',
                ],

                'to' => [
                    'nullable',
                    'date',
                    'after_or_equal:from',
                ],

                'per_page' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:100',
                ],
            ]);

        $query =
            HrisAttendance::query()
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->where(
                    'employee_id',
                    $employee->id
                )
                ->with(
                    'workLocation'
                )
                ->orderByDesc(
                    'attendance_date'
                )
                ->orderByDesc(
                    'clock_in'
                );

        if (
            !empty(
                $validated['from']
            )
        ) {
            $query->whereDate(
                'attendance_date',
                '>=',
                $validated['from']
            );
        }

        if (
            !empty(
                $validated['to']
            )
        ) {
            $query->whereDate(
                'attendance_date',
                '<=',
                $validated['to']
            );
        }

        $paginator =
            $query->paginate(
                $validated['per_page']
                ?? 20
            );

        return response()->json([
            'success' => true,

            'data' =>
                collect(
                    $paginator->items()
                )
                    ->map(
                        fn ($attendance) =>
                            $this->attendancePayload(
                                $attendance
                            )
                    )
                    ->values(),

            'meta' => [
                'current_page' =>
                    $paginator->currentPage(),

                'last_page' =>
                    $paginator->lastPage(),

                'per_page' =>
                    $paginator->perPage(),

                'total' =>
                    $paginator->total(),
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Work Locations
    |--------------------------------------------------------------------------
    */

    public function workLocations(
        Request $request,
        Organization $organization
    ): JsonResponse {
        $employee =
            $this->resolveEmployee(
                $request,
                $organization
            );

        $validated =
            $request->validate([
                'latitude' => [
                    'nullable',
                    'numeric',
                    'between:-90,90',
                ],

                'longitude' => [
                    'nullable',
                    'numeric',
                    'between:-180,180',
                ],
            ]);

        $locations =
            $this
                ->geofenceService
                ->eligibleLocations(
                    $organization,
                    $employee
                );

        $payload =
            $locations
                ->map(
                    function (
                        $location
                    ) use (
                        $validated
                    ) {
                        $distance = null;

                        $within = null;

                        if (
                            isset(
                                $validated['latitude'],
                                $validated['longitude']
                            )
                            &&
                            $location->latitude !== null
                            &&
                            $location->longitude !== null
                        ) {
                            $distance =
                                $this
                                    ->geofenceService
                                    ->distanceMeters(
                                        (float)
                                        $validated['latitude'],

                                        (float)
                                        $validated['longitude'],

                                        (float)
                                        $location->latitude,

                                        (float)
                                        $location->longitude
                                    );

                            $within =
                                !$location->geofence_enabled
                                ||
                                $distance
                                <=
                                (float)
                                $location->radius_meters;
                        }

                        return [
                            'id' =>
                                $location->id,

                            'ulid' =>
                                $location->ulid,

                            'name' =>
                                $location->name,

                            'type' =>
                                $location->type,

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
                                (int)
                                $location->radius_meters,

                            'geofence_enabled' =>
                                (bool)
                                $location->geofence_enabled,

                            'distance_meters' =>
                                $distance !== null
                                    ? round(
                                        $distance,
                                        2
                                    )
                                    : null,

                            'within_geofence' =>
                                $within,
                        ];
                    }
                )
                ->values();

        return response()->json([
            'success' => true,
            'data' => $payload,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Employee
    |--------------------------------------------------------------------------
    */

    private function resolveEmployee(
        Request $request,
        Organization $organization
    ): Employee {
        $user =
            $request->user();

        if (!$user) {
            abort(401);
        }

        $employee =
            Employee::query()
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->where(
                    'user_id',
                    $user->id
                )
                ->with([
                    'user',
                    'position',
                    'department',
                    'branch',
                ])
                ->first();

        if (!$employee) {
            throw ValidationException::withMessages([
                'employee' => __(
                    'attendance.errors.employee_not_linked'
                ),
            ]);
        }

        if (
            isset($employee->status)
            &&
            $employee->status !== 'active'
        ) {
            throw ValidationException::withMessages([
                'employee' => __(
                    'attendance.errors.employee_inactive'
                ),
            ]);
        }

        return $employee;
    }


    /*
    |--------------------------------------------------------------------------
    | Leave State
    |--------------------------------------------------------------------------
    */

    private function leaveState(
        Organization $organization,
        Employee $employee,
        Carbon $date
    ): ?array {
        $scheduled =
            HrisLeaveSchedule::query()
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->where(
                    'employee_id',
                    $employee->id
                )
                ->where(
                    'status',
                    'scheduled'
                )
                ->whereDate(
                    'start_date',
                    '<=',
                    $date->toDateString()
                )
                ->whereDate(
                    'end_date',
                    '>=',
                    $date->toDateString()
                )
                ->first();

        if ($scheduled) {
            return [
                'type' =>
                    'annual_leave',

                'title' =>
                    $scheduled->title,

                'start_date' =>
                    $scheduled
                        ->start_date
                        ->toDateString(),

                'end_date' =>
                    $scheduled
                        ->end_date
                        ->toDateString(),
            ];
        }

        $leave =
            HrisLeaveRequest::query()
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->where(
                    'employee_id',
                    $employee->id
                )
                ->where(
                    'status',
                    'approved'
                )
                ->whereDate(
                    'start_date',
                    '<=',
                    $date->toDateString()
                )
                ->whereDate(
                    'end_date',
                    '>=',
                    $date->toDateString()
                )
                ->first();

        if ($leave) {
            return [
                'type' =>
                    $leave->type,

                'title' =>
                    ucfirst(
                        $leave->type
                    ),

                'start_date' =>
                    $leave
                        ->start_date
                        ->toDateString(),

                'end_date' =>
                    $leave
                        ->end_date
                        ->toDateString(),
            ];
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | API Payload
    |--------------------------------------------------------------------------
    */

    private function attendancePayload(
        HrisAttendance $attendance
    ): array {
        $attendance->loadMissing(
            'workLocation'
        );

        return [
            'id' =>
                $attendance->id,

            'ulid' =>
                $attendance->ulid,

            'attendance_date' =>
                $attendance
                    ->attendance_date
                    ?->toDateString(),

            'clock_in' =>
                $attendance
                    ->clock_in
                    ?->toIso8601String(),

            'clock_out' =>
                $attendance
                    ->clock_out
                    ?->toIso8601String(),

            'scheduled_clock_in' =>
                $attendance
                    ->scheduled_clock_in,

            'scheduled_clock_out' =>
                $attendance
                    ->scheduled_clock_out,

            'status' =>
                $attendance->status,

            'is_late' =>
                (bool)
                $attendance->is_late,

            'late_minutes' =>
                (int)
                $attendance->late_minutes,

            'work_minutes' =>
                $attendance->work_minutes !== null
                    ? (int)
                    $attendance->work_minutes
                    : null,

            'method' =>
                $attendance->method,

            'photo_in_url' =>
                $attendance->photo_in
                    ? Storage::disk(
                        'public'
                    )->url(
                        $attendance->photo_in
                    )
                    : null,

            'photo_out_url' =>
                $attendance->photo_out
                    ? Storage::disk(
                        'public'
                    )->url(
                        $attendance->photo_out
                    )
                    : null,

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

                        'radius_meters' =>
                            (int)
                            $attendance
                                ->workLocation
                                ->radius_meters,
                    ]
                    : null,

            'notes' =>
                $attendance->notes,
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


    private function metadata(
        Request $request,
        array $validated
    ): array {
        return [
            'device_id' =>
                $validated['device_id']
                ?? null,

            'device_name' =>
                $validated['device_name']
                ?? null,

            'device_os' =>
                $validated['device_os']
                ?? null,

            'app_version' =>
                $validated['app_version']
                ?? null,

            'ip' =>
                $request->ip(),

            'user_agent' =>
                $request->userAgent(),

            'recorded_at' =>
                now()->toIso8601String(),
        ];
    }
}
