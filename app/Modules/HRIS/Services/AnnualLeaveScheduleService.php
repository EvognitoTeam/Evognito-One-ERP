<?php

namespace App\Modules\HRIS\Services;

use App\Models\Employee;
use App\Models\Organization;
use App\Modules\HRIS\Models\HrisEmployeeProfile;
use App\Modules\HRIS\Models\HrisLeaveRequest;
use App\Modules\HRIS\Models\HrisLeaveSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnnualLeaveScheduleService
{
    /*
    |--------------------------------------------------------------------------
    | Duration
    |--------------------------------------------------------------------------
    */

    public function durationDays(
        string $startDate,
        string $endDate
    ): int {
        $start =
            Carbon::parse(
                $startDate
            )->startOfDay();

        $end =
            Carbon::parse(
                $endDate
            )->startOfDay();

        return
            (int) $start->diffInDays(
                $end
            )
            + 1;
    }


    /*
    |--------------------------------------------------------------------------
    | Create Schedule
    |--------------------------------------------------------------------------
    */

    public function create(
        Organization $organization,
        Employee $employee,
        array $data,
        ?int $actorUserId = null
    ): HrisLeaveSchedule {
        $this->guardEmployee(
            $organization,
            $employee
        );

        $endDate =
            $data['end_date']
            ??
            $data['start_date'];

        $duration =
            $this->durationDays(
                $data['start_date'],
                $endDate
            );

        $this->assertNoOverlap(
            $organization,
            $employee,
            $data['start_date'],
            $endDate
        );

        return DB::transaction(
            function () use (
                $organization,
                $employee,
                $data,
                $endDate,
                $duration,
                $actorUserId
            ) {
                $profile =
                    $this->profileForUpdate(
                        $organization,
                        $employee
                    );

                if (
                    (int) $profile->leave_quota
                    <
                    $duration
                ) {
                    throw ValidationException::withMessages([
                        'employee_id' =>
                        'Kuota cuti employee tidak mencukupi. Tersisa '
                            . $profile->leave_quota
                            . ' hari, schedule membutuhkan '
                            . $duration
                            . ' hari.',
                    ]);
                }

                $profile->decrement(
                    'leave_quota',
                    $duration
                );

                return HrisLeaveSchedule::query()
                    ->create([
                        'organization_id' =>
                        $organization->id,

                        'employee_id' =>
                        $employee->id,

                        'start_date' =>
                        $data['start_date'],

                        'end_date' =>
                        $endDate,

                        'duration_days' =>
                        $duration,

                        'title' =>
                        $data['title']
                            ?? 'Annual Leave',

                        'notes' =>
                        $data['notes']
                            ?? null,

                        'status' =>
                        'scheduled',

                        'quota_applied' =>
                        true,

                        'source' =>
                        'admin',

                        'created_by' =>
                        $actorUserId,

                        'updated_by' =>
                        $actorUserId,
                    ]);
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Schedule
    |--------------------------------------------------------------------------
    */

    public function update(
        Organization $organization,
        HrisLeaveSchedule $schedule,
        Employee $employee,
        array $data,
        ?int $actorUserId = null
    ): HrisLeaveSchedule {
        $this->guardSchedule(
            $organization,
            $schedule
        );

        $this->guardEmployee(
            $organization,
            $employee
        );

        if (
            $schedule->status
            !== 'scheduled'
        ) {
            throw ValidationException::withMessages([
                'schedule' =>
                'Schedule yang sudah dibatalkan tidak dapat diedit.',
            ]);
        }

        $endDate =
            $data['end_date']
            ??
            $data['start_date'];

        $newDuration =
            $this->durationDays(
                $data['start_date'],
                $endDate
            );

        $this->assertNoOverlap(
            $organization,
            $employee,
            $data['start_date'],
            $endDate,
            $schedule
        );

        return DB::transaction(
            function () use (
                $organization,
                $schedule,
                $employee,
                $data,
                $endDate,
                $newDuration,
                $actorUserId
            ) {
                $locked =
                    HrisLeaveSchedule::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $schedule->id
                    );

                $oldEmployee =
                    Employee::query()
                    ->where(
                        'organization_id',
                        $organization->id
                    )
                    ->findOrFail(
                        $locked->employee_id
                    );

                /*
                |--------------------------------------------------------------------------
                | Restore Old Quota
                |--------------------------------------------------------------------------
                */

                if (
                    $locked->quota_applied
                ) {
                    $oldProfile =
                        $this->profileForUpdate(
                            $organization,
                            $oldEmployee
                        );

                    $oldProfile->increment(
                        'leave_quota',
                        $locked->duration_days
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Apply New Quota
                |--------------------------------------------------------------------------
                */

                $newProfile =
                    $this->profileForUpdate(
                        $organization,
                        $employee
                    );

                if (
                    (int) $newProfile->leave_quota
                    <
                    $newDuration
                ) {
                    throw ValidationException::withMessages([
                        'employee_id' =>
                        'Kuota cuti employee tidak mencukupi. Tersisa '
                            . $newProfile->leave_quota
                            . ' hari, schedule membutuhkan '
                            . $newDuration
                            . ' hari.',
                    ]);
                }

                $newProfile->decrement(
                    'leave_quota',
                    $newDuration
                );

                $locked->update([
                    'employee_id' =>
                    $employee->id,

                    'start_date' =>
                    $data['start_date'],

                    'end_date' =>
                    $endDate,

                    'duration_days' =>
                    $newDuration,

                    'title' =>
                    $data['title']
                        ?? 'Annual Leave',

                    'notes' =>
                    $data['notes']
                        ?? null,

                    'quota_applied' =>
                    true,

                    'updated_by' =>
                    $actorUserId,
                ]);

                return $locked->fresh([
                    'employee',
                ]);
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Cancel Schedule
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Organization $organization,
        HrisLeaveSchedule $schedule,
        ?int $actorUserId = null,
        ?string $reason = null
    ): HrisLeaveSchedule {
        $this->guardSchedule(
            $organization,
            $schedule
        );

        return DB::transaction(
            function () use (
                $organization,
                $schedule,
                $actorUserId,
                $reason
            ) {
                $locked =
                    HrisLeaveSchedule::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $schedule->id
                    );

                if (
                    $locked->status
                    === 'cancelled'
                ) {
                    return $locked;
                }

                if (
                    $locked->quota_applied
                ) {
                    $employee =
                        Employee::query()
                        ->where(
                            'organization_id',
                            $organization->id
                        )
                        ->findOrFail(
                            $locked->employee_id
                        );

                    $profile =
                        $this->profileForUpdate(
                            $organization,
                            $employee
                        );

                    $profile->increment(
                        'leave_quota',
                        $locked->duration_days
                    );

                    $locked->quota_applied =
                        false;
                }

                $locked->status =
                    'cancelled';

                $locked->cancelled_by =
                    $actorUserId;

                $locked->cancelled_at =
                    now();

                $locked->cancellation_reason =
                    $reason;

                $locked->updated_by =
                    $actorUserId;

                $locked->save();

                return $locked;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Overlap
    |--------------------------------------------------------------------------
    */

    public function assertNoOverlap(
        Organization $organization,
        Employee $employee,
        string $startDate,
        string $endDate,
        ?HrisLeaveSchedule $ignore = null
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Existing Annual Leave Schedule
        |--------------------------------------------------------------------------
        */

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
                $endDate
            )
            ->whereDate(
                'end_date',
                '>=',
                $startDate
            )
            ->when(
                $ignore,
                fn($query) =>
                $query->where(
                    'id',
                    '!=',
                    $ignore->id
                )
            )
            ->exists();

        if ($scheduled) {
            throw ValidationException::withMessages([
                'start_date' =>
                'Periode bertabrakan dengan Annual Leave Schedule lain.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Existing Leave Request
        |--------------------------------------------------------------------------
        */

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
            ->whereIn(
                'status',
                [
                    'pending',
                    'approved',
                ]
            )
            ->whereDate(
                'start_date',
                '<=',
                $endDate
            )
            ->whereDate(
                'end_date',
                '>=',
                $startDate
            )
            ->exists();

        if ($leave) {
            throw ValidationException::withMessages([
                'start_date' =>
                'Periode bertabrakan dengan Leave Request employee.',
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Employee Profile
    |--------------------------------------------------------------------------
    */

    private function profileForUpdate(
        Organization $organization,
        Employee $employee
    ): HrisEmployeeProfile {
        $profile =
            HrisEmployeeProfile::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(
                'employee_id',
                $employee->id
            )
            ->lockForUpdate()
            ->first();

        if (!$profile) {
            HrisEmployeeProfile::query()
                ->create([
                    'organization_id' =>
                    $organization->id,

                    'employee_id' =>
                    $employee->id,

                    'leave_quota' =>
                    10,

                    'debt_minutes' =>
                    0,
                ]);

            $profile =
                HrisEmployeeProfile::query()
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->where(
                    'employee_id',
                    $employee->id
                )
                ->lockForUpdate()
                ->firstOrFail();
        }

        return $profile;
    }


    private function guardEmployee(
        Organization $organization,
        Employee $employee
    ): void {
        if (
            (int) $employee->organization_id
            !==
            (int) $organization->id
        ) {
            abort(404);
        }
    }


    private function guardSchedule(
        Organization $organization,
        HrisLeaveSchedule $schedule
    ): void {
        if (
            (int) $schedule->organization_id
            !==
            (int) $organization->id
        ) {
            abort(404);
        }
    }
}
