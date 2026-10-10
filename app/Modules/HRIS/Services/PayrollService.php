<?php

namespace App\Modules\HRIS\Services;

use App\Models\Employee;
use App\Models\Organization;
use App\Modules\HRIS\Models\HrisAttendance;
use App\Modules\HRIS\Models\HrisAttendancePolicy;
use App\Modules\HRIS\Models\HrisEmployeeCompensation;
use App\Modules\HRIS\Models\HrisLeaveRequest;
use App\Modules\HRIS\Models\HrisLeaveSchedule;
use App\Modules\HRIS\Models\HrisPayroll;
use App\Modules\HRIS\Models\HrisPayrollPeriod;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollService
{
    public function __construct(
        private readonly HolidayService $holidayService
    ) {}


    /*
    |--------------------------------------------------------------------------
    | Generate Period
    |--------------------------------------------------------------------------
    */

    public function generate(
        Organization $organization,
        HrisPayrollPeriod $period,
        ?int $actorUserId = null
    ): void {
        if (
            (int) $period->organization_id
            !==
            (int) $organization->id
        ) {
            abort(404);
        }

        if (
            $period->status
            === 'paid'
        ) {
            throw ValidationException::withMessages([
                'period' =>
                'Payroll period yang sudah Paid tidak dapat di-generate ulang.',
            ]);
        }

        $employees =
            Employee::query()
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
                'department',
                'branch',
            ])
            ->get();

        DB::transaction(
            function () use (
                $organization,
                $period,
                $employees,
                $actorUserId
            ) {
                foreach (
                    $employees
                    as $employee
                ) {
                    $this->generateEmployee(
                        $organization,
                        $period,
                        $employee
                    );
                }

                $period->status =
                    'processed';

                $period->generated_at =
                    now();

                $period->generated_by =
                    $actorUserId;

                $period->save();
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate One Employee
    |--------------------------------------------------------------------------
    */

    private function generateEmployee(
        Organization $organization,
        HrisPayrollPeriod $period,
        Employee $employee
    ): HrisPayroll {
        $start =
            $period
            ->period_start
            ->copy()
            ->startOfDay();

        $end =
            $period
            ->period_end
            ->copy()
            ->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Compensation
        |--------------------------------------------------------------------------
        */

        $compensation =
            HrisEmployeeCompensation::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(
                'employee_id',
                $employee->id
            )
            ->where(
                'is_active',
                true
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Attendance Summary
        |--------------------------------------------------------------------------
        */

        $summary =
            $this->attendanceSummary(
                $organization,
                $employee,
                $start,
                $end
            );


        /*
        |--------------------------------------------------------------------------
        | Compensation Values
        |--------------------------------------------------------------------------
        */

        $basicSalary =
            (float) (
                $compensation
                ?->basic_salary
                ?? 0
            );

        $fixedAllowance =
            (float) (
                $compensation
                ?->fixed_allowance
                ?? 0
            );

        $dailyBonusRate =
            (float) (
                $compensation
                ?->daily_bonus
                ?? 0
            );

        $annualBonus =
            (float) (
                $compensation
                ?->annual_bonus
                ?? 0
            );

        $lateDeductionRate =
            (float) (
                $compensation
                ?->late_deduction_per_minute
                ?? 0
            );

        $absenceDeductionRate =
            (float) (
                $compensation
                ?->absence_deduction_per_day
                ?? 0
            );

        $otherFixedDeduction =
            (float) (
                $compensation
                ?->other_fixed_deduction
                ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | Calculate
        |--------------------------------------------------------------------------
        */

        $dailyBonusTotal =
            $summary['present_days']
            *
            $dailyBonusRate;

        $lateDeductionTotal =
            $summary['late_minutes']
            *
            $lateDeductionRate;

        $absenceDeductionTotal =
            $summary['absent_days']
            *
            $absenceDeductionRate;

        $grossSalary =
            $basicSalary
            +
            $fixedAllowance
            +
            $dailyBonusTotal
            +
            $annualBonus;

        $totalDeductions =
            $lateDeductionTotal
            +
            $absenceDeductionTotal
            +
            $otherFixedDeduction;

        $netSalary =
            max(
                0,
                $grossSalary
                    -
                    $totalDeductions
            );


        /*
        |--------------------------------------------------------------------------
        | Snapshot
        |--------------------------------------------------------------------------
        */

        $employeeName =
            $employee->name
            ??
            $employee->user?->name
            ??
            'Unknown Employee';

        return HrisPayroll::query()
            ->updateOrCreate(
                [
                    'payroll_period_id' =>
                    $period->id,

                    'employee_id' =>
                    $employee->id,
                ],
                [
                    'organization_id' =>
                    $organization->id,

                    'employee_number' =>
                    $employee
                        ->employee_number
                        ?? null,

                    'employee_name' =>
                    $employeeName,

                    'position_name' =>
                    $employee
                        ->position
                        ?->name,

                    'department_name' =>
                    $employee
                        ->department
                        ?->name,

                    'branch_name' =>
                    $employee
                        ->branch
                        ?->name,

                    'scheduled_days' =>
                    $summary['scheduled_days'],

                    'present_days' =>
                    $summary['present_days'],

                    'leave_days' =>
                    $summary['leave_days'],

                    'sick_days' =>
                    $summary['sick_days'],

                    'permission_days' =>
                    $summary['permission_days'],

                    'absent_days' =>
                    $summary['absent_days'],

                    'late_minutes' =>
                    $summary['late_minutes'],

                    'basic_salary' =>
                    $basicSalary,

                    'fixed_allowance' =>
                    $fixedAllowance,

                    'daily_bonus_rate' =>
                    $dailyBonusRate,

                    'daily_bonus_total' =>
                    $dailyBonusTotal,

                    'annual_bonus' =>
                    $annualBonus,

                    'late_deduction_rate' =>
                    $lateDeductionRate,

                    'late_deduction_total' =>
                    $lateDeductionTotal,

                    'absence_deduction_rate' =>
                    $absenceDeductionRate,

                    'absence_deduction_total' =>
                    $absenceDeductionTotal,

                    'other_fixed_deduction' =>
                    $otherFixedDeduction,

                    'gross_salary' =>
                    $grossSalary,

                    'total_deductions' =>
                    $totalDeductions,

                    'net_salary' =>
                    $netSalary,

                    'currency' =>
                    $compensation
                        ?->currency
                        ?? 'IDR',

                    'status' =>
                    'draft',
                ]
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Attendance Summary
    |--------------------------------------------------------------------------
    */

    public function attendanceSummary(
        Organization $organization,
        Employee $employee,
        Carbon $start,
        Carbon $end
    ): array {
        $policy =
            HrisAttendancePolicy::resolveFor(
                $organization
            );

        $workDays =
            collect(
                $policy->work_days
                    ?: [
                        1,
                        2,
                        3,
                        4,
                        5,
                    ]
            )
            ->map(
                fn($day) =>
                (int) $day
            );


        /*
        |--------------------------------------------------------------------------
        | Expected Work Dates
        |--------------------------------------------------------------------------
        */

        $expectedDates =
            collect();

        foreach (
            CarbonPeriod::create(
                $start,
                $end
            )
            as $date
        ) {
            $date =
                Carbon::instance(
                    $date
                );

            if (
                !$workDays->contains(
                    $date->isoWeekday()
                )
            ) {
                continue;
            }

            if (
                $this
                ->holidayService
                ->isHoliday(
                    $organization,
                    $date
                )
            ) {
                continue;
            }

            $expectedDates->push(
                $date->toDateString()
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Attendance
        |--------------------------------------------------------------------------
        */

        $attendances =
            HrisAttendance::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(
                'employee_id',
                $employee->id
            )
            ->whereBetween(
                'attendance_date',
                [
                    $start->toDateString(),
                    $end->toDateString(),
                ]
            )
            ->get();

        $attendanceDates =
            $attendances
            ->pluck(
                'attendance_date'
            )
            ->map(
                fn($date) =>
                Carbon::parse(
                    $date
                )->toDateString()
            )
            ->unique();


        /*
        |--------------------------------------------------------------------------
        | Leave Map
        |--------------------------------------------------------------------------
        */

        $leaveMap =
            $this->leaveDateMap(
                $organization,
                $employee,
                $start,
                $end
            );


        /*
        |--------------------------------------------------------------------------
        | Count
        |--------------------------------------------------------------------------
        */

        $present =
            0;

        $leave =
            0;

        $sick =
            0;

        $permission =
            0;

        $absent =
            0;

        foreach (
            $expectedDates
            as $date
        ) {
            /*
            |--------------------------------------------------------------------------
            | Attendance has highest priority
            |--------------------------------------------------------------------------
            */

            if (
                $attendanceDates->contains(
                    $date
                )
            ) {
                $present++;

                continue;
            }

            $leaveType =
                $leaveMap->get(
                    $date
                );

            if (
                $leaveType
                === 'cuti'
            ) {
                $leave++;

                continue;
            }

            if (
                $leaveType
                === 'sakit'
            ) {
                $sick++;

                continue;
            }

            if (
                $leaveType
                === 'izin'
            ) {
                $permission++;

                continue;
            }

            $absent++;
        }


        /*
        |--------------------------------------------------------------------------
        | Late Minutes
        |--------------------------------------------------------------------------
        */

        $lateMinutes =
            (int) $attendances
                ->sum(
                    'late_minutes'
                );


        return [
            'scheduled_days' =>
            $expectedDates->count(),

            'present_days' =>
            $present,

            'leave_days' =>
            $leave,

            'sick_days' =>
            $sick,

            'permission_days' =>
            $permission,

            'absent_days' =>
            $absent,

            'late_minutes' =>
            $lateMinutes,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Leave Date Map
    |--------------------------------------------------------------------------
    */

    private function leaveDateMap(
        Organization $organization,
        Employee $employee,
        Carbon $start,
        Carbon $end
    ): Collection {
        $map =
            collect();


        /*
        |--------------------------------------------------------------------------
        | Admin Annual Leave Schedule
        |--------------------------------------------------------------------------
        */

        $schedules =
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
                $end->toDateString()
            )
            ->whereDate(
                'end_date',
                '>=',
                $start->toDateString()
            )
            ->get();

        foreach (
            $schedules
            as $schedule
        ) {
            foreach (
                CarbonPeriod::create(
                    $schedule->start_date,
                    $schedule->end_date
                )
                as $date
            ) {
                $map->put(
                    Carbon::instance(
                        $date
                    )->toDateString(),
                    'cuti'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Approved Leave Requests
        |--------------------------------------------------------------------------
        */

        $leaves =
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
                $end->toDateString()
            )
            ->whereDate(
                'end_date',
                '>=',
                $start->toDateString()
            )
            ->get();

        foreach (
            $leaves
            as $leave
        ) {
            foreach (
                CarbonPeriod::create(
                    $leave->start_date,
                    $leave->end_date
                )
                as $date
            ) {
                $map->put(
                    Carbon::instance(
                        $date
                    )->toDateString(),
                    $leave->type
                );
            }
        }

        return $map;
    }


    /*
    |--------------------------------------------------------------------------
    | Mark Paid
    |--------------------------------------------------------------------------
    */

    public function markPaid(
        Organization $organization,
        HrisPayroll $payroll,
        ?int $actorUserId = null
    ): void {
        if (
            (int) $payroll->organization_id
            !==
            (int) $organization->id
        ) {
            abort(404);
        }

        $payroll->status =
            'paid';

        $payroll->paid_at =
            now();

        $payroll->paid_by =
            $actorUserId;

        $payroll->save();


        /*
        |--------------------------------------------------------------------------
        | Update Period If All Payroll Paid
        |--------------------------------------------------------------------------
        */

        $remaining =
            HrisPayroll::query()
            ->where(
                'payroll_period_id',
                $payroll->payroll_period_id
            )
            ->where(
                'status',
                '!=',
                'paid'
            )
            ->exists();

        if (!$remaining) {
            HrisPayrollPeriod::query()
                ->where(
                    'id',
                    $payroll
                        ->payroll_period_id
                )
                ->update([
                    'status' =>
                    'paid',
                ]);
        }
    }
}
