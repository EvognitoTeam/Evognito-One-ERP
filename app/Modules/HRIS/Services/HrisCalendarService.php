<?php

namespace App\Modules\HRIS\Services;

use App\Models\Organization;
use App\Modules\HRIS\Models\HrisHoliday;
use App\Modules\HRIS\Models\HrisLeaveRequest;
use App\Modules\HRIS\Models\HrisLeaveSchedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HrisCalendarService
{
    public function events(
        Organization $organization,
        Carbon $start,
        Carbon $end,
        array $filters = []
    ): Collection {
        $events =
            collect();

        $type =
            $filters['type']
            ?? 'all';

        /*
        |--------------------------------------------------------------------------
        | Holidays
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $type,
                [
                    'all',
                    'holiday',
                ],
                true
            )
        ) {
            $holidays =
                HrisHoliday::query()
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->where(
                    'is_active',
                    true
                )
                ->where(
                    function (
                        Builder $query
                    ) use (
                        $start,
                        $end
                    ) {
                        $query
                            ->where(
                                'is_recurring',
                                true
                            )
                            ->orWhere(
                                function (
                                    Builder $range
                                ) use (
                                    $start,
                                    $end
                                ) {
                                    $range
                                        ->whereDate(
                                            'holiday_date',
                                            '<=',
                                            $end->toDateString()
                                        )
                                        ->where(
                                            function (
                                                Builder $inner
                                            ) use (
                                                $start
                                            ) {
                                                $inner
                                                    ->whereNull(
                                                        'end_date'
                                                    )
                                                    ->orWhereDate(
                                                        'end_date',
                                                        '>=',
                                                        $start
                                                            ->toDateString()
                                                    );
                                            }
                                        );
                                }
                            );
                    }
                )
                ->get();

            foreach (
                $holidays
                as $holiday
            ) {
                foreach (
                    $this->holidayPeriods(
                        $holiday,
                        $start,
                        $end
                    )
                    as $period
                ) {
                    $events->push([
                        'id' =>
                        'holiday-' . $holiday->id,

                        'source' =>
                        'holiday',

                        'type' =>
                        'holiday',

                        'title' =>
                        $holiday->name,

                        'start' =>
                        $period['start'],

                        'end' =>
                        $period['end'],

                        'color' =>
                        $holiday->color
                            ?? '#e8742a',

                        'employee_id' =>
                        null,

                        'employee_name' =>
                        null,

                        'status' =>
                        'holiday',

                        'notes' =>
                        $holiday->description,
                    ]);
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Annual Leave Schedule
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $type,
                [
                    'all',
                    'annual_leave',
                ],
                true
            )
        ) {
            $query =
                HrisLeaveSchedule::query()
                ->where(
                    'organization_id',
                    $organization->id
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
                ->with([
                    'employee.user',
                    'employee.branch',
                    'employee.department',
                    'employee.position',
                ]);

            $this->applyEmployeeFilters(
                $query,
                $filters
            );

            foreach (
                $query->get()
                as $schedule
            ) {
                $name =
                    $this->employeeName(
                        $schedule->employee
                    );

                $events->push([
                    'id' =>
                    'schedule-' . $schedule->id,

                    'source' =>
                    'annual_leave_schedule',

                    'type' =>
                    'annual_leave',

                    'title' =>
                    $name . ' · Annual Leave',

                    'start' =>
                    $schedule
                        ->start_date
                        ->toDateString(),

                    'end' =>
                    $schedule
                        ->end_date
                        ->toDateString(),

                    'color' =>
                    '#e8742a',

                    'employee_id' =>
                    $schedule->employee_id,

                    'employee_name' =>
                    $name,

                    'status' =>
                    $schedule->status,

                    'notes' =>
                    $schedule->notes,
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Approved Leave Requests
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $type,
                [
                    'all',
                    'cuti',
                    'izin',
                    'sakit',
                ],
                true
            )
        ) {
            $query =
                HrisLeaveRequest::query()
                ->where(
                    'organization_id',
                    $organization->id
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
                ->with([
                    'employee.user',
                    'employee.branch',
                    'employee.department',
                    'employee.position',
                ]);

            if (
                $type
                !== 'all'
            ) {
                $query->where(
                    'type',
                    $type
                );
            }

            $this->applyEmployeeFilters(
                $query,
                $filters
            );

            foreach (
                $query->get()
                as $leave
            ) {
                $name =
                    $this->employeeName(
                        $leave->employee
                    );

                $color =
                    match ($leave->type) {
                        'cuti' =>
                        '#7c3aed',

                        'izin' =>
                        '#2563eb',

                        'sakit' =>
                        '#dc2626',

                        default =>
                        '#64748b',
                    };

                $events->push([
                    'id' =>
                    'leave-' . $leave->id,

                    'source' =>
                    'leave_request',

                    'type' =>
                    $leave->type,

                    'title' =>
                    $name
                        . ' · '
                        . ucfirst(
                            $leave->type
                        ),

                    'start' =>
                    $leave
                        ->start_date
                        ->toDateString(),

                    'end' =>
                    $leave
                        ->end_date
                        ->toDateString(),

                    'color' =>
                    $color,

                    'employee_id' =>
                    $leave->employee_id,

                    'employee_name' =>
                    $name,

                    'status' =>
                    $leave->status,

                    'notes' =>
                    $leave->reason,
                ]);
            }
        }

        return $events
            ->sortBy('start')
            ->values();
    }


    private function applyEmployeeFilters(
        Builder $query,
        array $filters
    ): void {
        if (
            !empty($filters['employee_id'])
        ) {
            $query->where(
                'employee_id',
                $filters['employee_id']
            );
        }

        if (
            !empty($filters['branch_id'])
        ) {
            $query->whereHas(
                'employee',
                fn(Builder $employee) =>
                $employee->where(
                    'branch_id',
                    $filters['branch_id']
                )
            );
        }

        if (
            !empty($filters['department_id'])
        ) {
            $query->whereHas(
                'employee',
                fn(Builder $employee) =>
                $employee->where(
                    'department_id',
                    $filters['department_id']
                )
            );
        }
    }


    private function employeeName(
        $employee
    ): string {
        return
            $employee?->name
            ??
            $employee?->user?->name
            ??
            'Unknown Employee';
    }


    /*
    |--------------------------------------------------------------------------
    | Holiday Periods
    |--------------------------------------------------------------------------
    */

    private function holidayPeriods(
        HrisHoliday $holiday,
        Carbon $rangeStart,
        Carbon $rangeEnd
    ): array {
        if (
            !$holiday->is_recurring
        ) {
            $start =
                $holiday
                ->holiday_date
                ->copy()
                ->startOfDay();

            $end =
                (
                    $holiday->end_date
                    ??
                    $holiday->holiday_date
                )
                ->copy()
                ->startOfDay();

            if (
                $start->gt(
                    $rangeEnd
                )
                ||
                $end->lt(
                    $rangeStart
                )
            ) {
                return [];
            }

            return [[
                'start' =>
                $start->toDateString(),

                'end' =>
                $end->toDateString(),
            ]];
        }

        $periods = [];

        $originalStart =
            $holiday
            ->holiday_date;

        $originalEnd =
            $holiday->end_date
            ??
            $holiday->holiday_date;

        for (
            $year =
                $rangeStart->year - 1;

            $year
                <=
                $rangeEnd->year;

            $year++
        ) {
            if (
                !checkdate(
                    $originalStart->month,
                    $originalStart->day,
                    $year
                )
            ) {
                continue;
            }

            $start =
                Carbon::create(
                    $year,
                    $originalStart->month,
                    $originalStart->day,
                    0,
                    0,
                    0
                );

            $crossYear =
                $originalEnd
                ->format('md')
                <
                $originalStart
                ->format('md');

            $endYear =
                $crossYear
                ? $year + 1
                : $year;

            if (
                !checkdate(
                    $originalEnd->month,
                    $originalEnd->day,
                    $endYear
                )
            ) {
                continue;
            }

            $end =
                Carbon::create(
                    $endYear,
                    $originalEnd->month,
                    $originalEnd->day,
                    0,
                    0,
                    0
                );

            if (
                $start->gt(
                    $rangeEnd
                )
                ||
                $end->lt(
                    $rangeStart
                )
            ) {
                continue;
            }

            $periods[] = [
                'start' =>
                $start->toDateString(),

                'end' =>
                $end->toDateString(),
            ];
        }

        return $periods;
    }
}
