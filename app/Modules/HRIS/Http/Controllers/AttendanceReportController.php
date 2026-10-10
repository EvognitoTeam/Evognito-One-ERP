<?php

namespace App\Modules\HRIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Position;
use App\Modules\HRIS\Models\HrisAttendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceReportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Report
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request,
        Organization $organization
    ): View {
        $filters =
            $this->validatedFilters(
                $request
            );

        $baseQuery =
            $this->buildQuery(
                $organization,
                $filters
            );


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $totalRecords =
            (clone $baseQuery)
            ->count();

        $completedCount =
            (clone $baseQuery)
            ->where(
                'status',
                'completed'
            )
            ->count();

        $openCount =
            (clone $baseQuery)
            ->where(
                'status',
                'open'
            )
            ->count();

        $lateCount =
            (clone $baseQuery)
            ->where(
                'is_late',
                true
            )
            ->count();

        $onTimeCount =
            (clone $baseQuery)
            ->where(
                'is_late',
                false
            )
            ->count();

        $insideCount =
            (clone $baseQuery)
            ->where(
                'clock_in_within_geofence',
                true
            )
            ->where(
                function (
                    Builder $query
                ) {
                    $query
                        ->whereNull(
                            'clock_out_within_geofence'
                        )
                        ->orWhere(
                            'clock_out_within_geofence',
                            true
                        );
                }
            )
            ->count();

        $outsideCount =
            (clone $baseQuery)
            ->where(
                function (
                    Builder $query
                ) {
                    $query
                        ->where(
                            'clock_in_within_geofence',
                            false
                        )
                        ->orWhere(
                            'clock_out_within_geofence',
                            false
                        );
                }
            )
            ->count();

        $totalWorkMinutes =
            (int) (
                (clone $baseQuery)
                ->sum(
                    'work_minutes'
                )
            );

        $totalLateMinutes =
            (int) (
                (clone $baseQuery)
                ->sum(
                    'late_minutes'
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Employee Count
        |--------------------------------------------------------------------------
        */

        $employeeCount =
            (clone $baseQuery)
            ->distinct(
                'employee_id'
            )
            ->count(
                'employee_id'
            );


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $attendances =
            $baseQuery
            ->orderByDesc(
                'attendance_date'
            )
            ->orderByDesc(
                'clock_in'
            )
            ->paginate(50)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Filter Options
        |--------------------------------------------------------------------------
        */

        $employees =
            Employee::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->with([
                'user',
                'position',
                'branch',
            ])
            ->orderBy(
                'employee_number'
            )
            ->get();

        $branches =
            Branch::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->orderBy(
                'name'
            )
            ->get();

        $positions =
            Position::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->orderBy(
                'name'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Active Filter Count
        |--------------------------------------------------------------------------
        */

        $activeFilterCount =
            collect([
                $filters['employee_id'],
                $filters['branch_id'],
                $filters['position_id'],
                $filters['status'],
                $filters['late'],
                $filters['geofence'],
                $filters['method'],
                $filters['search'],
            ])
            ->filter(
                fn($value) =>
                $value !== null
                    &&
                    $value !== ''
            )
            ->count();


        return view(
            'modules.hris.attendance.report.index',
            compact(
                'organization',
                'filters',

                'attendances',
                'employees',
                'branches',
                'positions',

                'totalRecords',
                'employeeCount',
                'completedCount',
                'openCount',

                'lateCount',
                'onTimeCount',
                'totalLateMinutes',

                'insideCount',
                'outsideCount',

                'totalWorkMinutes',

                'activeFilterCount'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Detailed Export
    |--------------------------------------------------------------------------
    */

    public function exportDetailed(
        Request $request,
        Organization $organization
    ): StreamedResponse {
        $filters =
            $this->validatedFilters(
                $request
            );

        $query =
            $this->buildQuery(
                $organization,
                $filters
            )
            ->orderBy(
                'attendance_date'
            )
            ->orderBy(
                'employee_id'
            )
            ->orderBy(
                'clock_in'
            );


        $filename =
            $this->buildFilename(
                $organization,
                $filters,
                'attendance-detail'
            );


        return Response::streamDownload(
            function () use (
                $query
            ) {
                $handle =
                    fopen(
                        'php://output',
                        'w'
                    );


                /*
                |--------------------------------------------------------------------------
                | UTF-8 BOM
                |--------------------------------------------------------------------------
                */

                fwrite(
                    $handle,
                    "\xEF\xBB\xBF"
                );


                /*
                |--------------------------------------------------------------------------
                | Header
                |--------------------------------------------------------------------------
                */

                fputcsv(
                    $handle,
                    [
                        'Date',

                        'Employee Number',
                        'Employee Name',

                        'Branch',
                        'Position',

                        'Scheduled Clock In',
                        'Scheduled Clock Out',

                        'Clock In',
                        'Clock Out',

                        'Status',

                        'Late',
                        'Late Minutes',

                        'Work Minutes',
                        'Work Hours',

                        'Method',

                        'Work Location',

                        'Clock In Latitude',
                        'Clock In Longitude',
                        'Clock In Accuracy (m)',
                        'Clock In Distance (m)',
                        'Clock In Geofence',

                        'Clock Out Latitude',
                        'Clock Out Longitude',
                        'Clock Out Accuracy (m)',
                        'Clock Out Distance (m)',
                        'Clock Out Geofence',

                        'Notes',
                    ],
                    ';'
                );


                /*
                |--------------------------------------------------------------------------
                | Data
                |--------------------------------------------------------------------------
                */

                $query->chunkById(
                    500,
                    function (
                        $rows
                    ) use (
                        $handle
                    ) {
                        foreach (
                            $rows
                            as $attendance
                        ) {
                            $employee =
                                $attendance->employee;

                            $workMinutes =
                                (int) (
                                    $attendance->work_minutes
                                    ?? 0
                                );


                            fputcsv(
                                $handle,
                                [
                                    $attendance
                                        ->attendance_date
                                        ?->format(
                                            'Y-m-d'
                                        ),

                                    $employee
                                        ?->employee_number,

                                    $this->employeeName(
                                        $employee
                                    ),

                                    $employee
                                        ?->branch
                                        ?->name,

                                    $employee
                                        ?->position
                                        ?->name,

                                    $attendance
                                        ->scheduled_clock_in,

                                    $attendance
                                        ->scheduled_clock_out,

                                    $attendance
                                        ->clock_in
                                        ?->format(
                                            'Y-m-d H:i:s'
                                        ),

                                    $attendance
                                        ->clock_out
                                        ?->format(
                                            'Y-m-d H:i:s'
                                        ),

                                    $attendance
                                        ->status,

                                    $attendance
                                        ->is_late
                                        ? 'Yes'
                                        : 'No',

                                    (int) (
                                        $attendance
                                        ->late_minutes
                                        ?? 0
                                    ),

                                    $workMinutes,

                                    round(
                                        $workMinutes
                                            / 60,
                                        2
                                    ),

                                    $attendance
                                        ->method,

                                    $attendance
                                        ->workLocation
                                        ?->name,

                                    $attendance
                                        ->clock_in_latitude,

                                    $attendance
                                        ->clock_in_longitude,

                                    $attendance
                                        ->clock_in_accuracy_meters,

                                    $attendance
                                        ->clock_in_distance_meters,

                                    $this->geofenceText(
                                        $attendance
                                            ->clock_in_within_geofence
                                    ),

                                    $attendance
                                        ->clock_out_latitude,

                                    $attendance
                                        ->clock_out_longitude,

                                    $attendance
                                        ->clock_out_accuracy_meters,

                                    $attendance
                                        ->clock_out_distance_meters,

                                    $this->geofenceText(
                                        $attendance
                                            ->clock_out_within_geofence
                                    ),

                                    $attendance
                                        ->notes,
                                ],
                                ';'
                            );
                        }
                    }
                );


                fclose(
                    $handle
                );
            },
            $filename,
            [
                'Content-Type' =>
                'text/csv; charset=UTF-8',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Summary Export
    |--------------------------------------------------------------------------
    |
    | Satu row per employee berdasarkan filter aktif.
    |
    */

    public function exportSummary(
        Request $request,
        Organization $organization
    ): StreamedResponse {
        $filters =
            $this->validatedFilters(
                $request
            );


        $records =
            $this->buildQuery(
                $organization,
                $filters
            )
            ->orderBy(
                'employee_id'
            )
            ->orderBy(
                'attendance_date'
            )
            ->get();


        $summaries =
            $this->buildEmployeeSummaries(
                $records
            );


        $filename =
            $this->buildFilename(
                $organization,
                $filters,
                'attendance-summary'
            );


        return Response::streamDownload(
            function () use (
                $summaries,
                $filters
            ) {
                $handle =
                    fopen(
                        'php://output',
                        'w'
                    );


                fwrite(
                    $handle,
                    "\xEF\xBB\xBF"
                );


                /*
                |--------------------------------------------------------------------------
                | Report information
                |--------------------------------------------------------------------------
                */

                fputcsv(
                    $handle,
                    [
                        'Attendance Summary',
                    ],
                    ';'
                );

                fputcsv(
                    $handle,
                    [
                        'From',
                        $filters['from'],
                    ],
                    ';'
                );

                fputcsv(
                    $handle,
                    [
                        'To',
                        $filters['to'],
                    ],
                    ';'
                );

                fputcsv(
                    $handle,
                    [],
                    ';'
                );


                /*
                |--------------------------------------------------------------------------
                | Header
                |--------------------------------------------------------------------------
                */

                fputcsv(
                    $handle,
                    [
                        'Employee Number',
                        'Employee Name',

                        'Branch',
                        'Position',

                        'Attendance Records',

                        'Completed',
                        'Open',

                        'On Time',
                        'Late',

                        'Total Late Minutes',

                        'Inside Geofence',
                        'Outside Geofence',
                        'Unknown Geofence',

                        'Total Work Minutes',
                        'Total Work Hours',

                        'Average Work Hours / Attendance',
                    ],
                    ';'
                );


                /*
                |--------------------------------------------------------------------------
                | Rows
                |--------------------------------------------------------------------------
                */

                foreach (
                    $summaries
                    as $summary
                ) {
                    fputcsv(
                        $handle,
                        [
                            $summary['employee_number'],

                            $summary['employee_name'],

                            $summary['branch'],

                            $summary['position'],

                            $summary['records'],

                            $summary['completed'],

                            $summary['open'],

                            $summary['on_time'],

                            $summary['late'],

                            $summary['late_minutes'],

                            $summary['inside'],

                            $summary['outside'],

                            $summary['unknown'],

                            $summary['work_minutes'],

                            round(
                                $summary['work_minutes']
                                    / 60,
                                2
                            ),

                            $summary['records'] > 0
                                ? round(
                                    (
                                        $summary['work_minutes']
                                        / 60
                                    )
                                        /
                                        $summary['records'],
                                    2
                                )
                                : 0,
                        ],
                        ';'
                    );
                }


                fclose(
                    $handle
                );
            },
            $filename,
            [
                'Content-Type' =>
                'text/csv; charset=UTF-8',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Build Query
    |--------------------------------------------------------------------------
    */

    private function buildQuery(
        Organization $organization,
        array $filters
    ): Builder {
        $query =
            HrisAttendance::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->with([
                'employee.user',
                'employee.branch',
                'employee.position',
                'employee.department',
                'workLocation',
            ])
            ->whereBetween(
                'attendance_date',
                [
                    $filters['from'],
                    $filters['to'],
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Employee
        |--------------------------------------------------------------------------
        */

        if (
            !empty($filters['employee_id'])
        ) {
            $query->where(
                'employee_id',
                $filters['employee_id']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Branch
        |--------------------------------------------------------------------------
        */

        if (
            !empty($filters['branch_id'])
        ) {
            $query->whereHas(
                'employee',
                function (
                    Builder $query
                ) use (
                    $filters
                ) {
                    $query->where(
                        'branch_id',
                        $filters['branch_id']
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Position
        |--------------------------------------------------------------------------
        */

        if (
            !empty($filters['position_id'])
        ) {
            $query->whereHas(
                'employee',
                function (
                    Builder $query
                ) use (
                    $filters
                ) {
                    $query->where(
                        'position_id',
                        $filters['position_id']
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Attendance Status
        |--------------------------------------------------------------------------
        */

        if (
            !empty($filters['status'])
        ) {
            $query->where(
                'status',
                $filters['status']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Method
        |--------------------------------------------------------------------------
        */

        if (
            !empty($filters['method'])
        ) {
            $query->where(
                'method',
                $filters['method']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Late
        |--------------------------------------------------------------------------
        */

        if (
            $filters['late']
            === 'yes'
        ) {
            $query->where(
                'is_late',
                true
            );
        }


        if (
            $filters['late']
            === 'no'
        ) {
            $query->where(
                'is_late',
                false
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Geofence
        |--------------------------------------------------------------------------
        */

        if (
            $filters['geofence']
            === 'inside'
        ) {
            $query
                ->where(
                    'clock_in_within_geofence',
                    true
                )
                ->where(
                    function (
                        Builder $query
                    ) {
                        $query
                            ->whereNull(
                                'clock_out_within_geofence'
                            )
                            ->orWhere(
                                'clock_out_within_geofence',
                                true
                            );
                    }
                );
        }


        if (
            $filters['geofence']
            === 'outside'
        ) {
            $query->where(
                function (
                    Builder $query
                ) {
                    $query
                        ->where(
                            'clock_in_within_geofence',
                            false
                        )
                        ->orWhere(
                            'clock_out_within_geofence',
                            false
                        );
                }
            );
        }


        if (
            $filters['geofence']
            === 'unknown'
        ) {
            $query
                ->whereNull(
                    'clock_in_within_geofence'
                )
                ->whereNull(
                    'clock_out_within_geofence'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Search Employee
        |--------------------------------------------------------------------------
        */

        if (
            !empty($filters['search'])
        ) {
            $keyword =
                '%'
                . $filters['search']
                . '%';


            $query->whereHas(
                'employee',
                function (
                    Builder $query
                ) use (
                    $keyword
                ) {
                    $query->where(
                        function (
                            Builder $query
                        ) use (
                            $keyword
                        ) {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    $keyword
                                )
                                ->orWhere(
                                    'employee_number',
                                    'like',
                                    $keyword
                                )
                                ->orWhereHas(
                                    'user',
                                    function (
                                        Builder $query
                                    ) use (
                                        $keyword
                                    ) {
                                        $query
                                            ->where(
                                                'name',
                                                'like',
                                                $keyword
                                            )
                                            ->orWhere(
                                                'email',
                                                'like',
                                                $keyword
                                            );
                                    }
                                );
                        }
                    );
                }
            );
        }


        return $query;
    }


    /*
    |--------------------------------------------------------------------------
    | Validation + Defaults
    |--------------------------------------------------------------------------
    */

    private function validatedFilters(
        Request $request
    ): array {
        $validated =
            $request->validate([
                'from' => [
                    'nullable',
                    'date_format:Y-m-d',
                ],

                'to' => [
                    'nullable',
                    'date_format:Y-m-d',
                ],

                'employee_id' => [
                    'nullable',
                    'integer',
                ],

                'branch_id' => [
                    'nullable',
                    'integer',
                ],

                'position_id' => [
                    'nullable',
                    'integer',
                ],

                'status' => [
                    'nullable',
                    'in:open,completed',
                ],

                'late' => [
                    'nullable',
                    'in:yes,no',
                ],

                'geofence' => [
                    'nullable',
                    'in:inside,outside,unknown',
                ],

                'method' => [
                    'nullable',
                    'in:manual,web,mobile,device',
                ],

                'search' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
            ]);


        $from =
            $validated['from']
            ??
            now()
            ->startOfMonth()
            ->toDateString();


        $to =
            $validated['to']
            ??
            now()
            ->endOfMonth()
            ->toDateString();


        /*
        |--------------------------------------------------------------------------
        | Prevent inverted date
        |--------------------------------------------------------------------------
        */

        if (
            Carbon::parse(
                $to
            )->lt(
                Carbon::parse(
                    $from
                )
            )
        ) {
            $to =
                $from;
        }


        return [
            'from' =>
            $from,

            'to' =>
            $to,

            'employee_id' =>
            $validated['employee_id']
                ?? null,

            'branch_id' =>
            $validated['branch_id']
                ?? null,

            'position_id' =>
            $validated['position_id']
                ?? null,

            'status' =>
            $validated['status']
                ?? null,

            'late' =>
            $validated['late']
                ?? null,

            'geofence' =>
            $validated['geofence']
                ?? null,

            'method' =>
            $validated['method']
                ?? null,

            'search' =>
            trim(
                $validated['search']
                    ?? ''
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Employee Summary
    |--------------------------------------------------------------------------
    */

    private function buildEmployeeSummaries(
        Collection $records
    ): Collection {
        return $records
            ->groupBy(
                'employee_id'
            )
            ->map(
                function (
                    Collection $attendances
                ) {
                    /** @var HrisAttendance $first */
                    $first =
                        $attendances->first();

                    $employee =
                        $first?->employee;


                    $inside = 0;
                    $outside = 0;
                    $unknown = 0;


                    foreach (
                        $attendances
                        as $attendance
                    ) {
                        $status =
                            $this->attendanceGeofenceStatus(
                                $attendance
                            );


                        if (
                            $status
                            === 'inside'
                        ) {
                            $inside++;
                        } elseif (
                            $status
                            === 'outside'
                        ) {
                            $outside++;
                        } else {
                            $unknown++;
                        }
                    }


                    return [
                        'employee_number' =>
                        $employee
                            ?->employee_number,

                        'employee_name' =>
                        $this->employeeName(
                            $employee
                        ),

                        'branch' =>
                        $employee
                            ?->branch
                            ?->name,

                        'position' =>
                        $employee
                            ?->position
                            ?->name,

                        'records' =>
                        $attendances->count(),

                        'completed' =>
                        $attendances
                            ->where(
                                'status',
                                'completed'
                            )
                            ->count(),

                        'open' =>
                        $attendances
                            ->where(
                                'status',
                                'open'
                            )
                            ->count(),

                        'on_time' =>
                        $attendances
                            ->where(
                                'is_late',
                                false
                            )
                            ->count(),

                        'late' =>
                        $attendances
                            ->where(
                                'is_late',
                                true
                            )
                            ->count(),

                        'late_minutes' =>
                        (int) (
                            $attendances
                            ->sum(
                                'late_minutes'
                            )
                        ),

                        'inside' =>
                        $inside,

                        'outside' =>
                        $outside,

                        'unknown' =>
                        $unknown,

                        'work_minutes' =>
                        (int) (
                            $attendances
                            ->sum(
                                'work_minutes'
                            )
                        ),
                    ];
                }
            )
            ->sortBy(
                'employee_name'
            )
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | Filename
    |--------------------------------------------------------------------------
    */

    private function buildFilename(
        Organization $organization,
        array $filters,
        string $prefix
    ): string {
        $parts = [
            $prefix,
        ];


        /*
        |--------------------------------------------------------------------------
        | Employee
        |--------------------------------------------------------------------------
        */

        if (
            !empty($filters['employee_id'])
        ) {
            $employee =
                Employee::query()
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->where(
                    'id',
                    $filters['employee_id']
                )
                ->with('user')
                ->first();


            if ($employee) {
                $parts[] =
                    Str::slug(
                        $this->employeeName(
                            $employee
                        )
                    );
            }
        } else {
            $parts[] =
                'all-employees';
        }


        /*
        |--------------------------------------------------------------------------
        | Period
        |--------------------------------------------------------------------------
        */

        if (
            $filters['from']
            ===
            $filters['to']
        ) {
            $parts[] =
                $filters['from'];
        } else {
            $parts[] =
                $filters['from'];

            $parts[] =
                'to';

            $parts[] =
                $filters['to'];
        }


        /*
        |--------------------------------------------------------------------------
        | Optional Filter Naming
        |--------------------------------------------------------------------------
        */

        if (
            $filters['late']
            === 'yes'
        ) {
            $parts[] =
                'late-only';
        }


        if (
            $filters['late']
            === 'no'
        ) {
            $parts[] =
                'on-time';
        }


        if (
            !empty($filters['status'])
        ) {
            $parts[] =
                $filters['status'];
        }


        if (
            !empty($filters['geofence'])
        ) {
            $parts[] =
                'geofence-'
                . $filters['geofence'];
        }


        if (
            !empty($filters['method'])
        ) {
            $parts[] =
                $filters['method'];
        }


        return implode(
            '-',
            array_filter(
                $parts
            )
        ) . '.csv';
    }


    /*
    |--------------------------------------------------------------------------
    | Employee Name
    |--------------------------------------------------------------------------
    */

    private function employeeName(
        ?Employee $employee
    ): string {
        if (!$employee) {
            return '-';
        }


        return
            $employee->name
            ??
            $employee->user?->name
            ??
            'Employee';
    }


    /*
    |--------------------------------------------------------------------------
    | Attendance Geofence Status
    |--------------------------------------------------------------------------
    */

    private function attendanceGeofenceStatus(
        HrisAttendance $attendance
    ): string {
        if (
            $attendance
            ->clock_in_within_geofence
            === false
            ||
            $attendance
            ->clock_out_within_geofence
            === false
        ) {
            return 'outside';
        }


        if (
            $attendance
            ->clock_in_within_geofence
            === true
        ) {
            return 'inside';
        }


        return 'unknown';
    }


    /*
    |--------------------------------------------------------------------------
    | Geofence Text
    |--------------------------------------------------------------------------
    */

    private function geofenceText(
        mixed $value
    ): string {
        if (
            $value === true
            ||
            $value === 1
            ||
            $value === '1'
        ) {
            return 'Inside';
        }


        if (
            $value === false
            ||
            $value === 0
            ||
            $value === '0'
        ) {
            return 'Outside';
        }


        return 'Unknown';
    }
}
