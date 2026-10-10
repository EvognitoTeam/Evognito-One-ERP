<?php

namespace App\Modules\HRIS\Services;

use App\Models\Employee;
use App\Models\Organization;
use App\Modules\HRIS\Models\HrisAttendance;
use App\Modules\HRIS\Models\HrisAttendancePolicy;
use App\Modules\HRIS\Models\HrisLeaveRequest;
use App\Modules\HRIS\Models\HrisLeaveSchedule;
use App\Modules\HRIS\Models\HrisPositionSetting;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function __construct(
        private readonly GeofenceService $geofenceService,
        private readonly HolidayService $holidayService
    ) {
    }

    public function save(
        Organization $organization,
        Employee $employee,
        array $data,
        ?HrisAttendance $attendance = null,
        ?int $actorUserId = null,
        bool $enforcePolicy = false
    ): HrisAttendance {
        if (
            (int) $employee->organization_id
            !==
            (int) $organization->id
        ) {
            abort(404);
        }

        $policy = HrisAttendancePolicy::resolveFor(
            $organization
        );

        if (
            !$enforcePolicy
            &&
            !(
                $policy->allow_manual_adjustment
                ?? true
            )
        ) {
            throw ValidationException::withMessages([
                'attendance' => __(
                    'attendance.errors.manual_disabled'
                ),
            ]);
        }

        if (empty($data['clock_in'])) {
            throw ValidationException::withMessages([
                'clock_in' => __(
                    'attendance.errors.clock_in_required'
                ),
            ]);
        }

        $clockIn = Carbon::parse(
            $data['clock_in']
        );

        $clockOut = !empty($data['clock_out'])
            ? Carbon::parse(
                $data['clock_out']
            )
            : null;

        if (
            $clockOut
            &&
            $clockOut->lt($clockIn)
        ) {
            throw ValidationException::withMessages([
                'clock_out' => __(
                    'attendance.errors.clock_out_before_clock_in'
                ),
            ]);
        }

        $attendanceDate = $clockIn->toDateString();

        $duplicate = HrisAttendance::query()
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
                $attendanceDate
            )
            ->when(
                $attendance,
                fn ($query) => $query->where(
                    'id',
                    '!=',
                    $attendance->id
                )
            )
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'clock_in' => __(
                    'attendance.errors.duplicate'
                ),
            ]);
        }

        $positionSetting = null;

        if (!empty($employee->position_id)) {
            $positionSetting = HrisPositionSetting::query()
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->where(
                    'position_id',
                    $employee->position_id
                )
                ->first();
        }

        $scheduledClockIn =
            $positionSetting?->clock_in
            ?? '08:00:00';

        $scheduledClockOut =
            $positionSetting?->clock_out
            ?? '17:00:00';

        $lateTolerance =
            (int) (
                $positionSetting?->late_tolerance_minutes
                ?? 0
            );

        $isMobilePosition =
            (bool) (
                $positionSetting?->is_mobile
                ?? false
            );

        $scheduledStart = Carbon::parse(
            $attendanceDate
            .' '
            .$scheduledClockIn
        );

        $scheduledEnd = Carbon::parse(
            $attendanceDate
            .' '
            .$scheduledClockOut
        );

        if (
            $scheduledEnd->lte(
                $scheduledStart
            )
        ) {
            $scheduledEnd->addDay();
        }

        if ($enforcePolicy) {
            $this->assertEmployeeNotOnLeave(
                $organization,
                $employee,
                $clockIn
            );

            $this->assertWorkingDay(
                $policy,
                $clockIn
            );

            $this->assertHoliday(
                $organization,
                $policy,
                $clockIn
            );

            /*
            |--------------------------------------------------------------------------
            | Clock-in window only applies when creating attendance.
            |--------------------------------------------------------------------------
            |
            | Clock-out uses the original clock-in and must not be rejected merely
            | because current time is outside the clock-in window.
            |
            */

            if (!$attendance) {
                $this->assertClockInWindow(
                    $policy,
                    $clockIn,
                    $scheduledStart
                );
            }

            $this->assertPhotoPolicy(
                $policy,
                $data,
                $attendance,
                $clockOut
            );

            $this->assertGpsAccuracy(
                $policy,
                $data,
                $attendance,
                $clockOut
            );
        }

        $lateStart = $scheduledStart
            ->copy()
            ->addMinutes(
                $lateTolerance
            );

        $lateMinutes = 0;

        if ($clockIn->gt($lateStart)) {
            $lateMinutes =
                (int) floor(
                    $lateStart
                        ->diffInMinutes(
                            $clockIn
                        )
                );
        }

        if (
            $enforcePolicy
            &&
            $clockOut
            &&
            !(
                $policy->allow_early_clock_out
                ?? true
            )
            &&
            $clockOut->lt(
                $scheduledEnd
            )
        ) {
            throw ValidationException::withMessages([
                'clock_out' => __(
                    'attendance.errors.early_clock_out'
                ),
            ]);
        }

        $workMinutes = $clockOut
            ? (int) floor(
                $clockIn->diffInMinutes(
                    $clockOut
                )
            )
            : null;

        $clockInLatitude =
            $data['clock_in_latitude']
            ?? $attendance?->clock_in_latitude;

        $clockInLongitude =
            $data['clock_in_longitude']
            ?? $attendance?->clock_in_longitude;

        $clockOutLatitude =
            $data['clock_out_latitude']
            ?? $attendance?->clock_out_latitude;

        $clockOutLongitude =
            $data['clock_out_longitude']
            ?? $attendance?->clock_out_longitude;

        $hasClockInLocation =
            $this->hasCoordinates(
                $clockInLatitude,
                $clockInLongitude
            );

        $hasClockOutLocation =
            $this->hasCoordinates(
                $clockOutLatitude,
                $clockOutLongitude
            );

        /*
        |--------------------------------------------------------------------------
        | Do not re-enforce clock-in geofence during clock-out.
        |--------------------------------------------------------------------------
        */

        $clockInGeofence = null;

        if (!$attendance) {
            $clockInGeofence =
                $this->processLocation(
                    $organization,
                    $employee,
                    $clockInLatitude,
                    $clockInLongitude,
                    $hasClockInLocation,
                    $policy,
                    $isMobilePosition,
                    $enforcePolicy,
                    'clock_in'
                );
        }

        $clockOutGeofence = null;

        if (
            $clockOut
            ||
            $hasClockOutLocation
        ) {
            $clockOutGeofence =
                $this->processLocation(
                    $organization,
                    $employee,
                    $clockOutLatitude,
                    $clockOutLongitude,
                    $hasClockOutLocation,
                    $policy,
                    $isMobilePosition,
                    $enforcePolicy,
                    'clock_out'
                );
        }

        $resolvedLocation =
            $clockInGeofence['location']
            ??
            $clockOutGeofence['location']
            ??
            $attendance?->workLocation
            ??
            null;

        $attendance ??= new HrisAttendance();

        $attendance->organization_id =
            $organization->id;

        $attendance->employee_id =
            $employee->id;

        if ($resolvedLocation) {
            $attendance->work_location_id =
                $resolvedLocation->id;
        }

        $attendance->attendance_date =
            $attendanceDate;

        $attendance->clock_in =
            $clockIn;

        $attendance->clock_out =
            $clockOut;

        $attendance->scheduled_clock_in =
            $scheduledClockIn;

        $attendance->scheduled_clock_out =
            $scheduledClockOut;

        $attendance->status =
            $clockOut
                ? 'completed'
                : 'open';

        $attendance->is_late =
            $lateMinutes > 0;

        $attendance->late_minutes =
            $lateMinutes;

        $attendance->work_minutes =
            $workMinutes;

        $attendance->method =
            $data['method']
            ??
            $attendance->method
            ??
            'manual';

        if (
            array_key_exists(
                'clock_in_latitude',
                $data
            )
        ) {
            $attendance->clock_in_latitude =
                $data['clock_in_latitude'];
        }

        if (
            array_key_exists(
                'clock_in_longitude',
                $data
            )
        ) {
            $attendance->clock_in_longitude =
                $data['clock_in_longitude'];
        }

        if (
            array_key_exists(
                'clock_in_accuracy',
                $data
            )
        ) {
            $attendance->clock_in_accuracy_meters =
                $data['clock_in_accuracy'];
        }

        if ($clockInGeofence) {
            $attendance->clock_in_distance_meters =
                $clockInGeofence['distance_meters']
                ?? null;

            $attendance->clock_in_within_geofence =
                $clockInGeofence['within_geofence']
                ?? null;
        }

        if (
            array_key_exists(
                'clock_out_latitude',
                $data
            )
        ) {
            $attendance->clock_out_latitude =
                $data['clock_out_latitude'];
        }

        if (
            array_key_exists(
                'clock_out_longitude',
                $data
            )
        ) {
            $attendance->clock_out_longitude =
                $data['clock_out_longitude'];
        }

        if (
            array_key_exists(
                'clock_out_accuracy',
                $data
            )
        ) {
            $attendance->clock_out_accuracy_meters =
                $data['clock_out_accuracy'];
        }

        if ($clockOutGeofence) {
            $attendance->clock_out_distance_meters =
                $clockOutGeofence['distance_meters']
                ?? null;

            $attendance->clock_out_within_geofence =
                $clockOutGeofence['within_geofence']
                ?? null;
        }

        if (
            array_key_exists(
                'photo_in',
                $data
            )
        ) {
            $attendance->photo_in =
                $data['photo_in'];
        }

        if (
            array_key_exists(
                'photo_out',
                $data
            )
        ) {
            $attendance->photo_out =
                $data['photo_out'];
        }

        if (
            array_key_exists(
                'notes',
                $data
            )
        ) {
            $attendance->notes =
                $data['notes'];
        }

        if (
            array_key_exists(
                'metadata',
                $data
            )
        ) {
            $attendance->metadata =
                $data['metadata'];
        }

        if (!$attendance->exists) {
            $attendance->created_by =
                $actorUserId;
        }

        $attendance->updated_by =
            $actorUserId;

        $attendance->save();

        return $attendance->fresh([
            'workLocation',
        ]);
    }


    private function assertEmployeeNotOnLeave(
        Organization $organization,
        Employee $employee,
        Carbon $date
    ): void {
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
            throw ValidationException::withMessages([
                'clock_in' => __(
                    'attendance.errors.on_annual_leave',
                    [
                        'date' => $date
                            ->translatedFormat(
                                'd F Y'
                            ),
                    ]
                ),
            ]);
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
            throw ValidationException::withMessages([
                'clock_in' => __(
                    'attendance.errors.on_leave',
                    [
                        'type' => ucfirst(
                            $leave->type
                        ),
                        'date' => $date
                            ->translatedFormat(
                                'd F Y'
                            ),
                    ]
                ),
            ]);
        }
    }


    private function assertWorkingDay(
        HrisAttendancePolicy $policy,
        Carbon $clockIn
    ): void {
        $workDays = collect(
            $policy->work_days
            ?: [1, 2, 3, 4, 5]
        )
            ->map(
                fn ($day) =>
                    (int) $day
            );

        if (
            $workDays->contains(
                $clockIn->isoWeekday()
            )
        ) {
            return;
        }

        if (
            $policy->allow_attendance_on_non_workdays
            ?? false
        ) {
            return;
        }

        throw ValidationException::withMessages([
            'clock_in' => __(
                'attendance.errors.non_workday'
            ),
        ]);
    }


    private function assertHoliday(
        Organization $organization,
        HrisAttendancePolicy $policy,
        Carbon $clockIn
    ): void {
        $holiday =
            $this
                ->holidayService
                ->findForDate(
                    $organization,
                    $clockIn
                );

        if (!$holiday) {
            return;
        }

        if (
            $policy->allow_attendance_on_holidays
            ?? false
        ) {
            return;
        }

        throw ValidationException::withMessages([
            'clock_in' => __(
                'attendance.errors.holiday',
                [
                    'date' => $clockIn
                        ->translatedFormat(
                            'd F Y'
                        ),
                    'holiday' =>
                        $holiday->name,
                ]
            ),
        ]);
    }


    private function assertClockInWindow(
        HrisAttendancePolicy $policy,
        Carbon $clockIn,
        Carbon $scheduledStart
    ): void {
        $before =
            (int) (
                $policy->clock_in_before_minutes
                ?? 120
            );

        $after =
            (int) (
                $policy->clock_in_after_minutes
                ?? 240
            );

        $windowStart =
            $scheduledStart
                ->copy()
                ->subMinutes(
                    $before
                );

        $windowEnd =
            $scheduledStart
                ->copy()
                ->addMinutes(
                    $after
                );

        if (
            $clockIn->lt(
                $windowStart
            )
        ) {
            throw ValidationException::withMessages([
                'clock_in' => __(
                    'attendance.errors.too_early',
                    [
                        'time' => $windowStart
                            ->format('H:i'),
                    ]
                ),
            ]);
        }

        if (
            $clockIn->gt(
                $windowEnd
            )
        ) {
            throw ValidationException::withMessages([
                'clock_in' => __(
                    'attendance.errors.too_late',
                    [
                        'time' => $windowEnd
                            ->format('H:i'),
                    ]
                ),
            ]);
        }
    }


    private function assertPhotoPolicy(
        HrisAttendancePolicy $policy,
        array $data,
        ?HrisAttendance $attendance,
        ?Carbon $clockOut
    ): void {
        $photoIn =
            $data['photo_in']
            ??
            $attendance?->photo_in;

        if (
            (
                $policy->require_photo_clock_in
                ?? true
            )
            &&
            blank($photoIn)
        ) {
            throw ValidationException::withMessages([
                'photo' => __(
                    'attendance.errors.photo_in_required'
                ),
            ]);
        }

        if (!$clockOut) {
            return;
        }

        $photoOut =
            $data['photo_out']
            ??
            $attendance?->photo_out;

        if (
            (
                $policy->require_photo_clock_out
                ?? true
            )
            &&
            blank($photoOut)
        ) {
            throw ValidationException::withMessages([
                'photo' => __(
                    'attendance.errors.photo_out_required'
                ),
            ]);
        }
    }


    private function assertGpsAccuracy(
        HrisAttendancePolicy $policy,
        array $data,
        ?HrisAttendance $attendance,
        ?Carbon $clockOut
    ): void {
        $maximum =
            (float) (
                $policy->maximum_gps_accuracy_meters
                ?? 100
            );

        $clockInAccuracy =
            $data['clock_in_accuracy']
            ??
            $attendance?->clock_in_accuracy_meters;

        if (
            $clockInAccuracy !== null
            &&
            is_numeric(
                $clockInAccuracy
            )
            &&
            (float) $clockInAccuracy
            >
            $maximum
        ) {
            throw ValidationException::withMessages([
                'location' => __(
                    'attendance.errors.gps_accuracy',
                    [
                        'accuracy' =>
                            number_format(
                                (float) $clockInAccuracy,
                                0,
                                ',',
                                '.'
                            ),
                        'maximum' =>
                            number_format(
                                $maximum,
                                0,
                                ',',
                                '.'
                            ),
                    ]
                ),
            ]);
        }

        if (!$clockOut) {
            return;
        }

        $clockOutAccuracy =
            $data['clock_out_accuracy']
            ??
            $attendance?->clock_out_accuracy_meters;

        if (
            $clockOutAccuracy !== null
            &&
            is_numeric(
                $clockOutAccuracy
            )
            &&
            (float) $clockOutAccuracy
            >
            $maximum
        ) {
            throw ValidationException::withMessages([
                'location' => __(
                    'attendance.errors.gps_accuracy',
                    [
                        'accuracy' =>
                            number_format(
                                (float) $clockOutAccuracy,
                                0,
                                ',',
                                '.'
                            ),
                        'maximum' =>
                            number_format(
                                $maximum,
                                0,
                                ',',
                                '.'
                            ),
                    ]
                ),
            ]);
        }
    }


    private function processLocation(
        Organization $organization,
        Employee $employee,
        mixed $latitude,
        mixed $longitude,
        bool $hasLocation,
        HrisAttendancePolicy $policy,
        bool $isMobilePosition,
        bool $enforcePolicy,
        string $event
    ): ?array {
        if (!$enforcePolicy) {
            if (!$hasLocation) {
                return null;
            }

            return $this
                ->geofenceService
                ->resolve(
                    $organization,
                    $employee,
                    (float) $latitude,
                    (float) $longitude
                );
        }

        $requireLocation =
            (bool) (
                $policy->require_location
                ?? true
            );

        if (!$hasLocation) {
            if ($requireLocation) {
                throw ValidationException::withMessages([
                    'location' =>
                        $event === 'clock_out'
                            ? __(
                                'attendance.errors.location_out_required'
                            )
                            : __(
                                'attendance.errors.location_in_required'
                            ),
                ]);
            }

            return null;
        }

        $mode =
            $policy->geofence_mode
            ?? 'mobile_remote';

        if (
            $mode
            === 'record_only'
        ) {
            return $this
                ->geofenceService
                ->resolve(
                    $organization,
                    $employee,
                    (float) $latitude,
                    (float) $longitude
                );
        }

        if (
            $mode
            === 'mobile_remote'
            &&
            $isMobilePosition
        ) {
            return $this
                ->geofenceService
                ->resolve(
                    $organization,
                    $employee,
                    (float) $latitude,
                    (float) $longitude
                );
        }

        return $this
            ->geofenceService
            ->assertAllowed(
                $organization,
                $employee,
                (float) $latitude,
                (float) $longitude
            );
    }


    private function hasCoordinates(
        mixed $latitude,
        mixed $longitude
    ): bool {
        if (
            $latitude === null
            ||
            $longitude === null
            ||
            $latitude === ''
            ||
            $longitude === ''
        ) {
            return false;
        }

        return
            is_numeric($latitude)
            &&
            is_numeric($longitude);
    }
}
