<?php

namespace App\Modules\HRIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Organization;
use App\Modules\HRIS\Models\HrisEmployeeProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request,
        Organization $organization
    ): View {
        $query =
            Employee::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->with([
                'user',
                'branch',
                'department',
                'position',
            ]);


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

            $query->where(
                function (
                    Builder $employeeQuery
                ) use (
                    $search
                ) {
                    $employeeQuery
                        ->where(
                            'employee_number',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'name',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'email',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhereHas(
                            'user',
                            function (
                                Builder $userQuery
                            ) use (
                                $search
                            ) {
                                $userQuery
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
                            }
                        );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Branch
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'branch_id'
            )
        ) {
            $query->where(
                'branch_id',
                $request->integer(
                    'branch_id'
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Department
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'department_id'
            )
        ) {
            $query->where(
                'department_id',
                $request->integer(
                    'department_id'
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Position
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'position_id'
            )
        ) {
            $query->where(
                'position_id',
                $request->integer(
                    'position_id'
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage =
            (int) $request->integer(
                'per_page',
                20
            );

        if (
            !in_array(
                $perPage,
                [
                    10,
                    20,
                    50,
                    100,
                ],
                true
            )
        ) {
            $perPage = 20;
        }

        $employees =
            $query
            ->orderBy(
                'employee_number'
            )
            ->paginate(
                $perPage
            )
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | HRIS Profiles
        |--------------------------------------------------------------------------
        */

        $profiles =
            HrisEmployeeProfile::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->whereIn(
                'employee_id',
                $employees
                    ->getCollection()
                    ->pluck('id')
            )
            ->get()
            ->keyBy(
                'employee_id'
            );


        /*
        |--------------------------------------------------------------------------
        | Supervisor Candidates
        |--------------------------------------------------------------------------
        */

        $supervisors =
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
            ])
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Master Data
        |--------------------------------------------------------------------------
        */

        $branches =
            $organization
            ->branches()
            ->orderBy('name')
            ->get();

        $departments =
            $organization
            ->departments()
            ->orderBy('name')
            ->get();

        $positions =
            $organization
            ->positions()
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $totalEmployees =
            Employee::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->count();

        $activeEmployees =
            Employee::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(
                'status',
                'active'
            )
            ->count();

        $inactiveEmployees =
            Employee::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(
                'status',
                'inactive'
            )
            ->count();

        $linkedAccounts =
            Schema::hasColumn(
                'employees',
                'user_id'
            )
            ? Employee::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->whereNotNull(
                'user_id'
            )
            ->count()
            : 0;


        return view(
            'modules.hris.employees.index',
            compact(
                'organization',

                'employees',
                'profiles',
                'supervisors',

                'branches',
                'departments',
                'positions',

                'perPage',

                'totalEmployees',
                'activeEmployees',
                'inactiveEmployees',
                'linkedAccounts'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        Organization $organization
    ): RedirectResponse {
        $validated =
            $this->validateEmployee(
                $request,
                $organization
            );

        DB::transaction(
            function () use (
                $validated,
                $organization
            ) {
                /*
                |--------------------------------------------------------------------------
                | Core Employee
                |--------------------------------------------------------------------------
                */

                $employee =
                    new Employee();

                $this->fillCoreEmployee(
                    $employee,
                    $validated,
                    $organization
                );

                $employee->save();


                /*
                |--------------------------------------------------------------------------
                | HRIS Profile
                |--------------------------------------------------------------------------
                */

                $this->saveHrisProfile(
                    $employee,
                    $validated,
                    $organization
                );
            }
        );

        return redirect()
            ->route(
                'hris.employees.index',
                [
                    'organization' =>
                    $organization->slug,
                ]
            )
            ->with(
                'success',
                'Employee berhasil ditambahkan.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Organization $organization,
        Employee $employee
    ): RedirectResponse {
        $this->ensureOrganization(
            $organization,
            $employee
        );

        $validated =
            $this->validateEmployee(
                $request,
                $organization,
                $employee
            );

        if (
            !empty($validated['supervisor_employee_id'])
            &&
            (int) $validated['supervisor_employee_id']
            ===
            (int) $employee->id
        ) {
            throw ValidationException::withMessages([
                'supervisor_employee_id'
                =>
                'Employee tidak dapat menjadi supervisor dirinya sendiri.',
            ]);
        }

        DB::transaction(
            function () use (
                $validated,
                $organization,
                $employee
            ) {
                /*
                |--------------------------------------------------------------------------
                | Core
                |--------------------------------------------------------------------------
                */

                $this->fillCoreEmployee(
                    $employee,
                    $validated,
                    $organization
                );

                $employee->save();


                /*
                |--------------------------------------------------------------------------
                | HRIS
                |--------------------------------------------------------------------------
                */

                $this->saveHrisProfile(
                    $employee,
                    $validated,
                    $organization
                );
            }
        );

        return redirect()
            ->route(
                'hris.employees.index',
                [
                    'organization' =>
                    $organization->slug,
                ]
            )
            ->with(
                'success',
                'Employee berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Update HR Profile Only
    |--------------------------------------------------------------------------
    |
    | Tetap dipertahankan supaya route versi sebelumnya
    | tidak langsung rusak.
    |
    */

    public function updateProfile(
        Request $request,
        Organization $organization,
        Employee $employee
    ): RedirectResponse {
        $this->ensureOrganization(
            $organization,
            $employee
        );

        $validated =
            $request->validate([
                'employment_type' => [
                    'nullable',

                    Rule::in([
                        'permanent',
                        'contract',
                        'training',
                        'part_time',
                        'internship',
                        'freelance',
                    ]),
                ],

                'join_date' => [
                    'nullable',
                    'date',
                ],

                'end_date' => [
                    'nullable',
                    'date',
                    'after_or_equal:join_date',
                ],

                'supervisor_employee_id' => [
                    'nullable',
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

                'leave_quota' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:365',
                ],

                'debt_minutes' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:525600',
                ],

                'notes' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],
            ]);

        if (
            !empty($validated['supervisor_employee_id'])
            &&
            (int) $validated['supervisor_employee_id']
            ===
            (int) $employee->id
        ) {
            throw ValidationException::withMessages([
                'supervisor_employee_id'
                =>
                'Employee tidak dapat menjadi supervisor dirinya sendiri.',
            ]);
        }

        $this->saveHrisProfile(
            $employee,
            $validated,
            $organization
        );

        return back()
            ->with(
                'success',
                'HR Profile berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Employee
    |--------------------------------------------------------------------------
    */

    private function validateEmployee(
        Request $request,
        Organization $organization,
        ?Employee $employee = null
    ): array {
        $employeeNumberRule =
            Rule::unique(
                'employees',
                'employee_number'
            )
            ->where(
                fn($query) =>
                $query->where(
                    'organization_id',
                    $organization->id
                )
            );

        if ($employee) {
            $employeeNumberRule
                ->ignore(
                    $employee->id
                );
        }

        return $request->validate([
            /*
            |--------------------------------------------------------------------------
            | Core Employee
            |--------------------------------------------------------------------------
            */

            'employee_number' => [
                'required',
                'string',
                'max:100',

                $employeeNumberRule,
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'branch_id' => [
                'nullable',
                'integer',

                Rule::exists(
                    'branches',
                    'id'
                )->where(
                    fn($query) =>
                    $query->where(
                        'organization_id',
                        $organization->id
                    )
                ),
            ],

            'department_id' => [
                'nullable',
                'integer',

                Rule::exists(
                    'departments',
                    'id'
                )->where(
                    fn($query) =>
                    $query->where(
                        'organization_id',
                        $organization->id
                    )
                ),
            ],

            'position_id' => [
                'nullable',
                'integer',

                Rule::exists(
                    'positions',
                    'id'
                )->where(
                    fn($query) =>
                    $query->where(
                        'organization_id',
                        $organization->id
                    )
                ),
            ],

            'status' => [
                'required',

                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],


            /*
            |--------------------------------------------------------------------------
            | HRIS Profile
            |--------------------------------------------------------------------------
            */

            'employment_type' => [
                'nullable',

                Rule::in([
                    'permanent',
                    'contract',
                    'training',
                    'part_time',
                    'internship',
                    'freelance',
                ]),
            ],

            'join_date' => [
                'nullable',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:join_date',
            ],

            'supervisor_employee_id' => [
                'nullable',
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

            'leave_quota' => [
                'required',
                'integer',
                'min:0',
                'max:365',
            ],

            'debt_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:525600',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Fill Core Employee
    |--------------------------------------------------------------------------
    */

    private function fillCoreEmployee(
        Employee $employee,
        array $validated,
        Organization $organization
    ): void {
        $employee->organization_id =
            $organization->id;

        if (
            !$employee->exists
            &&
            Schema::hasColumn(
                'employees',
                'ulid'
            )
            &&
            blank(
                $employee->ulid
            )
        ) {
            $employee->ulid =
                (string) Str::ulid();
        }

        $employee->employee_number =
            $validated['employee_number'];

        $employee->name =
            $validated['name'];

        $employee->email =
            $validated['email']
            ?? null;

        $employee->phone =
            $validated['phone']
            ?? null;

        $employee->branch_id =
            $validated['branch_id']
            ?? null;

        $employee->department_id =
            $validated['department_id']
            ?? null;

        $employee->position_id =
            $validated['position_id']
            ?? null;

        $employee->status =
            $validated['status'];

        /*
        |--------------------------------------------------------------------------
        | Important
        |--------------------------------------------------------------------------
        |
        | Tidak membuat User secara otomatis.
        |
        */

        if (
            !$employee->exists
            &&
            Schema::hasColumn(
                'employees',
                'user_id'
            )
        ) {
            $employee->user_id =
                null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | HRIS Profile
    |--------------------------------------------------------------------------
    */

    private function saveHrisProfile(
        Employee $employee,
        array $validated,
        Organization $organization
    ): HrisEmployeeProfile {
        return HrisEmployeeProfile::query()
            ->updateOrCreate(
                [
                    'organization_id' =>
                    $organization->id,

                    'employee_id' =>
                    $employee->id,
                ],
                [
                    'supervisor_employee_id' =>
                    $validated['supervisor_employee_id']
                        ?? null,

                    'employment_type' =>
                    $validated['employment_type']
                        ?? null,

                    'join_date' =>
                    $validated['join_date']
                        ?? null,

                    'end_date' =>
                    $validated['end_date']
                        ?? null,

                    'leave_quota' =>
                    $validated['leave_quota']
                        ?? 10,

                    'debt_minutes' =>
                    $validated['debt_minutes']
                        ?? 0,

                    'notes' =>
                    $validated['notes']
                        ?? null,
                ]
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Organization Guard
    |--------------------------------------------------------------------------
    */

    private function ensureOrganization(
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
}
