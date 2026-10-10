<?php

namespace App\Modules\HRIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Organization;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationStructureController extends Controller
{
    public function index(
        Organization $organization
    ): View {
        $branches =
            Branch::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->orderBy('name')
            ->get();

        $departments =
            Department::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->orderBy('name')
            ->get();

        $positions =
            Position::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->orderBy('name')
            ->get();

        return view(
            'modules.hris.configuration.organization-structure',
            compact(
                'organization',
                'branches',
                'departments',
                'positions'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Branch
    |--------------------------------------------------------------------------
    */

    public function storeBranch(
        Request $request,
        Organization $organization
    ): RedirectResponse {
        $validated =
            $this->validateBranch(
                $request,
                $organization
            );

        $branch =
            new Branch();

        $branch->organization_id =
            $organization->id;

        if (
            Schema::hasColumn(
                'branches',
                'ulid'
            )
        ) {
            $branch->ulid =
                (string) Str::ulid();
        }

        $branch->name =
            $validated['name'];

        if (
            Schema::hasColumn(
                'branches',
                'code'
            )
        ) {
            $branch->code =
                $validated['code']
                ?? null;
        }

        if (
            Schema::hasColumn(
                'branches',
                'address'
            )
        ) {
            $branch->address =
                $validated['address']
                ?? null;
        }

        $branch->save();

        return back()
            ->with(
                'success',
                'Branch berhasil ditambahkan.'
            );
    }


    public function updateBranch(
        Request $request,
        Organization $organization,
        Branch $branch
    ): RedirectResponse {
        $this->guardOrganization(
            $organization,
            $branch
        );

        $validated =
            $this->validateBranch(
                $request,
                $organization,
                $branch
            );

        $branch->name =
            $validated['name'];

        if (
            Schema::hasColumn(
                'branches',
                'code'
            )
        ) {
            $branch->code =
                $validated['code']
                ?? null;
        }

        if (
            Schema::hasColumn(
                'branches',
                'address'
            )
        ) {
            $branch->address =
                $validated['address']
                ?? null;
        }

        $branch->save();

        return back()
            ->with(
                'success',
                'Branch berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Department
    |--------------------------------------------------------------------------
    */

    public function storeDepartment(
        Request $request,
        Organization $organization
    ): RedirectResponse {
        $validated =
            $this->validateDepartment(
                $request,
                $organization
            );

        $department =
            new Department();

        $department->organization_id =
            $organization->id;

        if (
            Schema::hasColumn(
                'departments',
                'ulid'
            )
        ) {
            $department->ulid =
                (string) Str::ulid();
        }

        $department->name =
            $validated['name'];

        if (
            Schema::hasColumn(
                'departments',
                'code'
            )
        ) {
            $department->code =
                $validated['code']
                ?? null;
        }

        if (
            Schema::hasColumn(
                'departments',
                'parent_id'
            )
        ) {
            $department->parent_id =
                $validated['parent_id']
                ?? null;
        }

        $department->save();

        return back()
            ->with(
                'success',
                'Department berhasil ditambahkan.'
            );
    }


    public function updateDepartment(
        Request $request,
        Organization $organization,
        Department $department
    ): RedirectResponse {
        $this->guardOrganization(
            $organization,
            $department
        );

        $validated =
            $this->validateDepartment(
                $request,
                $organization,
                $department
            );

        if (
            !empty($validated['parent_id'])
            &&
            (int) $validated['parent_id']
            ===
            (int) $department->id
        ) {
            return back()
                ->withErrors([
                    'parent_id' =>
                    'Department tidak dapat menjadi parent untuk dirinya sendiri.',
                ]);
        }

        $department->name =
            $validated['name'];

        if (
            Schema::hasColumn(
                'departments',
                'code'
            )
        ) {
            $department->code =
                $validated['code']
                ?? null;
        }

        if (
            Schema::hasColumn(
                'departments',
                'parent_id'
            )
        ) {
            $department->parent_id =
                $validated['parent_id']
                ?? null;
        }

        $department->save();

        return back()
            ->with(
                'success',
                'Department berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Position
    |--------------------------------------------------------------------------
    */

    public function storePosition(
        Request $request,
        Organization $organization
    ): RedirectResponse {
        $validated =
            $this->validatePosition(
                $request,
                $organization
            );

        $position =
            new Position();

        $position->organization_id =
            $organization->id;

        if (
            Schema::hasColumn(
                'positions',
                'ulid'
            )
        ) {
            $position->ulid =
                (string) Str::ulid();
        }

        $position->name =
            $validated['name'];

        if (
            Schema::hasColumn(
                'positions',
                'code'
            )
        ) {
            $position->code =
                $validated['code']
                ?? null;
        }

        $position->save();

        return back()
            ->with(
                'success',
                'Position berhasil ditambahkan.'
            );
    }


    public function updatePosition(
        Request $request,
        Organization $organization,
        Position $position
    ): RedirectResponse {
        $this->guardOrganization(
            $organization,
            $position
        );

        $validated =
            $this->validatePosition(
                $request,
                $organization,
                $position
            );

        $position->name =
            $validated['name'];

        if (
            Schema::hasColumn(
                'positions',
                'code'
            )
        ) {
            $position->code =
                $validated['code']
                ?? null;
        }

        $position->save();

        return back()
            ->with(
                'success',
                'Position berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validateBranch(
        Request $request,
        Organization $organization,
        ?Branch $branch = null
    ): array {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ];

        if (
            Schema::hasColumn(
                'branches',
                'code'
            )
        ) {
            $unique =
                Rule::unique(
                    'branches',
                    'code'
                )
                ->where(
                    fn($query) =>
                    $query->where(
                        'organization_id',
                        $organization->id
                    )
                );

            if ($branch) {
                $unique->ignore(
                    $branch->id
                );
            }

            $rules['code'] = [
                'nullable',
                'string',
                'max:100',
                $unique,
            ];
        }

        if (
            Schema::hasColumn(
                'branches',
                'address'
            )
        ) {
            $rules['address'] = [
                'nullable',
                'string',
                'max:500',
            ];
        }

        return $request->validate(
            $rules
        );
    }


    private function validateDepartment(
        Request $request,
        Organization $organization,
        ?Department $department = null
    ): array {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ];

        if (
            Schema::hasColumn(
                'departments',
                'code'
            )
        ) {
            $unique =
                Rule::unique(
                    'departments',
                    'code'
                )
                ->where(
                    fn($query) =>
                    $query->where(
                        'organization_id',
                        $organization->id
                    )
                );

            if ($department) {
                $unique->ignore(
                    $department->id
                );
            }

            $rules['code'] = [
                'nullable',
                'string',
                'max:100',
                $unique,
            ];
        }

        if (
            Schema::hasColumn(
                'departments',
                'parent_id'
            )
        ) {
            $rules['parent_id'] = [
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
            ];
        }

        return $request->validate(
            $rules
        );
    }


    private function validatePosition(
        Request $request,
        Organization $organization,
        ?Position $position = null
    ): array {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ];

        if (
            Schema::hasColumn(
                'positions',
                'code'
            )
        ) {
            $unique =
                Rule::unique(
                    'positions',
                    'code'
                )
                ->where(
                    fn($query) =>
                    $query->where(
                        'organization_id',
                        $organization->id
                    )
                );

            if ($position) {
                $unique->ignore(
                    $position->id
                );
            }

            $rules['code'] = [
                'nullable',
                'string',
                'max:100',
                $unique,
            ];
        }

        return $request->validate(
            $rules
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Guard
    |--------------------------------------------------------------------------
    */

    private function guardOrganization(
        Organization $organization,
        $model
    ): void {
        if (
            (int) $model->organization_id
            !==
            (int) $organization->id
        ) {
            abort(404);
        }
    }
}
