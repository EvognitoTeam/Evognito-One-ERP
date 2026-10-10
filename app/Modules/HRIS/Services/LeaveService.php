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

class LeaveService
{
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
    | Overlap
    |--------------------------------------------------------------------------
    */

    public function assertNoOverlap(
        Organization $organization,
        Employee $employee,
        string $startDate,
        string $endDate,
        ?HrisLeaveRequest $ignore = null
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Leave Request
        |--------------------------------------------------------------------------
        */

        $exists =
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

        if ($exists) {
            throw ValidationException::withMessages([
                'start_date' =>
                'Employee sudah memiliki pengajuan leave yang bertabrakan dengan periode tersebut.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Admin Annual Leave Schedule
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
            ->exists();

        if ($scheduled) {
            throw ValidationException::withMessages([
                'start_date' =>
                'Periode pengajuan bertabrakan dengan Annual Leave Schedule yang telah ditentukan HR/Admin.',
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        HrisLeaveRequest $leave,
        int $approverUserId,
        ?string $notes = null
    ): HrisLeaveRequest {
        return DB::transaction(
            function () use (
                $leave,
                $approverUserId,
                $notes
            ) {
                $leave =
                    HrisLeaveRequest::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $leave->id
                    );

                if (
                    $leave->status
                    === 'approved'
                ) {
                    $leave->approval_notes =
                        $notes
                        ?? $leave->approval_notes;

                    $leave->save();

                    return $leave;
                }

                /*
                |--------------------------------------------------------------------------
                | Cuti Quota
                |--------------------------------------------------------------------------
                */

                if (
                    $leave->type
                    === 'cuti'
                    &&
                    !$leave->quota_applied
                ) {
                    $profile =
                        HrisEmployeeProfile::query()
                        ->where(
                            'organization_id',
                            $leave->organization_id
                        )
                        ->where(
                            'employee_id',
                            $leave->employee_id
                        )
                        ->lockForUpdate()
                        ->first();

                    if (!$profile) {
                        HrisEmployeeProfile::query()
                            ->create([
                                'organization_id' =>
                                $leave->organization_id,

                                'employee_id' =>
                                $leave->employee_id,

                                'leave_quota' =>
                                10,

                                'debt_minutes' =>
                                0,
                            ]);

                        $profile =
                            HrisEmployeeProfile::query()
                            ->where(
                                'organization_id',
                                $leave->organization_id
                            )
                            ->where(
                                'employee_id',
                                $leave->employee_id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();
                    }

                    if (
                        (int) $profile->leave_quota
                        <
                        (int) $leave->duration_days
                    ) {
                        throw ValidationException::withMessages([
                            'status' =>
                            'Leave quota tidak mencukupi. Tersisa '
                                . $profile->leave_quota
                                . ' hari, request membutuhkan '
                                . $leave->duration_days
                                . ' hari.',
                        ]);
                    }

                    $profile->decrement(
                        'leave_quota',
                        $leave->duration_days
                    );

                    $leave->quota_applied =
                        true;
                }

                $leave->status =
                    'approved';

                $leave->approved_by =
                    $approverUserId;

                $leave->approved_at =
                    now();

                $leave->rejected_at =
                    null;

                $leave->approval_notes =
                    $notes;

                $leave->save();

                return $leave;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        HrisLeaveRequest $leave,
        int $approverUserId,
        ?string $notes = null
    ): HrisLeaveRequest {
        return DB::transaction(
            function () use (
                $leave,
                $approverUserId,
                $notes
            ) {
                $leave =
                    HrisLeaveRequest::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $leave->id
                    );

                if (
                    $leave->type
                    === 'cuti'
                    &&
                    $leave->quota_applied
                ) {
                    $profile =
                        HrisEmployeeProfile::query()
                        ->where(
                            'organization_id',
                            $leave->organization_id
                        )
                        ->where(
                            'employee_id',
                            $leave->employee_id
                        )
                        ->lockForUpdate()
                        ->first();

                    if ($profile) {
                        $profile->increment(
                            'leave_quota',
                            $leave->duration_days
                        );
                    }

                    $leave->quota_applied =
                        false;
                }

                $leave->status =
                    'rejected';

                $leave->approved_by =
                    $approverUserId;

                $leave->approved_at =
                    null;

                $leave->rejected_at =
                    now();

                $leave->approval_notes =
                    $notes;

                $leave->save();

                return $leave;
            }
        );
    }
}
