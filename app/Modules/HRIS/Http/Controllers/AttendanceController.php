<?php

namespace App\Modules\HRIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Organization;
use App\Modules\HRIS\Models\HrisAttendance;
use App\Modules\HRIS\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService
    ) {}

    public function index(
        Request $request,
        Organization $organization
    ): View {
        /*
        |--------------------------------------------------------------------------
        | Default Period
        |--------------------------------------------------------------------------
        */

        $from =
            $request->input(
                'from',
                now()->startOfMonth()->toDateString()
            );

        $to =
            $request->input(
                'to',
                now()->toDateString()
            );

        /*
        |--------------------------------------------------------------------------
        | Employee Relations
        |--------------------------------------------------------------------------
        */

        $employeeModel =
            new Employee();

        $employeeRelations = [];

        foreach (
            [
                'user',
                'branch',
                'department',
                'position',
            ]
            as $relation
        ) {
            if (
                method_exists(
                    $employeeModel,
                    $relation
                )
            ) {
                $employeeRelations[] =
                    $relation;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Attendance Query
        |--------------------------------------------------------------------------
        */

        $query =
            HrisAttendance::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->whereBetween(
                'attendance_date',
                [
                    $from,
                    $to,
                ]
            )
            ->with('employee');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('search')
        ) {
            $search =
                trim(
                    $request
                        ->string('search')
                        ->toString()
                );

            $query->whereHas(
                'employee',
                function (
                    Builder $employeeQuery
                ) use (
                    $search,
                    $employeeModel
                ) {
                    $employeeQuery->where(
                        function (
                            Builder $inner
                        ) use (
                            $search,
                            $employeeModel
                        ) {
                            $used =
                                false;

                            if (
                                Schema::hasColumn(
                                    'employees',
                                    'employee_number'
                                )
                            ) {
                                $inner->where(
                                    'employee_number',
                                    'like',
                                    '%' . $search . '%'
                                );

                                $used = true;
                            }

                            if (
                                Schema::hasColumn(
                                    'employees',
                                    'name'
                                )
                            ) {
                                $used
                                    ? $inner->orWhere(
                                        'name',
                                        'like',
                                        '%' . $search . '%'
                                    )
                                    : $inner->where(
                                        'name',
                                        'like',
                                        '%' . $search . '%'
                                    );

                                $used = true;
                            }

                            if (
                                method_exists(
                                    $employeeModel,
                                    'user'
                                )
                            ) {
                                $callback =
                                    fn(
                                        Builder $user
                                    ) =>
                                    $user
                                        ->where(
                                            'name',
                                            'like',
                                            '%' . $search . '%'
                                        )
                                        ->orWhere(
                                            'email',
                                            'like',
                                            '%' . $search . '%'
                                        );

                                $used
                                    ? $inner->orWhereHas(
                                        'user',
                                        $callback
                                    )
                                    : $inner->whereHas(
                                        'user',
                                        $callback
                                    );
                            }
                        }
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'employee_id'
            )
        ) {
            $query->where(
                'employee_id',
                $request->integer(
                    'employee_id'
                )
            );
        }

        if (
            $request->input(
                'late'
            ) === 'yes'
        ) {
            $query->where(
                'is_late',
                true
            );
        }

        if (
            $request->input(
                'late'
            ) === 'no'
        ) {
            $query->where(
                'is_late',
                false
            );
        }

        if (
            $request->filled(
                'status'
            )
        ) {
            $query->where(
                'status',
                $request->string(
                    'status'
                )->toString()
            );
        }

        $attendances =
            $query
            ->latest(
                'attendance_date'
            )
            ->latest(
                'clock_in'
            )
            ->paginate(25)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Load Employee Nested Relations
        |--------------------------------------------------------------------------
        */

        $attendances
            ->getCollection()
            ->each(
                function (
                    HrisAttendance $attendance
                ) use (
                    $employeeRelations
                ) {
                    if (
                        $attendance->employee
                        &&
                        !empty($employeeRelations)
                    ) {
                        $attendance
                            ->employee
                            ->loadMissing(
                                $employeeRelations
                            );
                    }
                }
            );

        /*
        |--------------------------------------------------------------------------
        | Employees
        |--------------------------------------------------------------------------
        */

        $employees =
            Employee::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->when(
                Schema::hasColumn(
                    'employees',
                    'status'
                ),
                fn($query) =>
                $query->where(
                    'status',
                    'active'
                )
            )
            ->with(
                $employeeRelations
            )
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Today Statistics
        |--------------------------------------------------------------------------
        */

        $today =
            now()->toDateString();

        $todayQuery =
            HrisAttendance::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->whereDate(
                'attendance_date',
                $today
            );

        $presentToday =
            (clone $todayQuery)
            ->count();

        $lateToday =
            (clone $todayQuery)
            ->where(
                'is_late',
                true
            )
            ->count();

        $openToday =
            (clone $todayQuery)
            ->where(
                'status',
                'open'
            )
            ->count();

        $activeEmployees =
            Schema::hasColumn(
                'employees',
                'status'
            )
            ? Employee::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(
                'status',
                'active'
            )
            ->count()
            : Employee::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->count();

        return view(
            'modules.hris.attendance.index',
            compact(
                'organization',

                'attendances',
                'employees',

                'from',
                'to',

                'presentToday',
                'lateToday',
                'openToday',
                'activeEmployees'
            )
        );
    }

    public function store(
        Request $request,
        Organization $organization
    ): RedirectResponse {
        $validated =
            $this->validateAttendance(
                $request,
                $organization
            );

        $employee =
            Employee::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->findOrFail(
                $validated['employee_id']
            );

        $this
            ->attendanceService
            ->save(
                $organization,
                $employee,
                $validated,
                null,
                $request->user()?->id
            );

        return back()
            ->with(
                'success',
                'Attendance berhasil ditambahkan.'
            );
    }

    public function update(
        Request $request,
        Organization $organization,
        HrisAttendance $attendance
    ): RedirectResponse {
        if (
            (int) $attendance
                ->organization_id
            !==
            (int) $organization->id
        ) {
            abort(404);
        }

        $validated =
            $this->validateAttendance(
                $request,
                $organization
            );

        $employee =
            Employee::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->findOrFail(
                $validated['employee_id']
            );

        $this
            ->attendanceService
            ->save(
                $organization,
                $employee,
                $validated,
                $attendance,
                $request->user()?->id
            );

        return back()
            ->with(
                'success',
                'Attendance berhasil diperbarui.'
            );
    }

    private function validateAttendance(
        Request $request,
        Organization $organization
    ): array {
        return $request->validate([
            'employee_id' => [
                'required',
                'integer',

                Rule::exists(
                    'employees',
                    'id'
                )->where(
                    fn($query) =>
                    $query->where(
                        'organization_id',
                        $organization->id
                    )
                ),
            ],

            'clock_in' => [
                'required',
                'date',
            ],

            'clock_out' => [
                'nullable',
                'date',
                'after_or_equal:clock_in',
            ],

            'method' => [
                'required',

                Rule::in([
                    'manual',
                    'web',
                    'mobile',
                    'device',
                ]),
            ],

            'clock_in_latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'clock_in_longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'clock_out_latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'clock_out_longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);
    }
}
