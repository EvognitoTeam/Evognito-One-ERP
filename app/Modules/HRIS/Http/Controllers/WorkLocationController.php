<?php

namespace App\Modules\HRIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Organization;
use App\Modules\HRIS\Models\HrisWorkLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkLocationController extends Controller
{
    public function index(
        Organization $organization
    ): View {
        $locations =
            HrisWorkLocation::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->with([
                'branch',
            ])
            ->withCount(
                'employees'
            )
            ->orderByDesc(
                'is_active'
            )
            ->orderBy('name')
            ->get();

        $branches =
            $organization
            ->branches()
            ->orderBy('name')
            ->get();

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
                'branch',
                'department',
                'position',
            ])
            ->orderBy('name')
            ->get();

        $assignments =
            DB::table(
                'hris_employee_work_locations'
            )
            ->where(
                'organization_id',
                $organization->id
            )
            ->get()
            ->groupBy(
                'work_location_id'
            );

        $totalLocations =
            $locations->count();

        $activeLocations =
            $locations
            ->where(
                'is_active',
                true
            )
            ->count();

        $geofenceLocations =
            $locations
            ->where(
                'geofence_enabled',
                true
            )
            ->count();

        $assignedEmployees =
            DB::table(
                'hris_employee_work_locations'
            )
            ->where(
                'organization_id',
                $organization->id
            )
            ->distinct(
                'employee_id'
            )
            ->count(
                'employee_id'
            );

        return view(
            'modules.hris.configuration.work-locations',
            compact(
                'organization',

                'locations',
                'branches',
                'employees',
                'assignments',

                'totalLocations',
                'activeLocations',
                'geofenceLocations',
                'assignedEmployees'
            )
        );
    }


    public function store(
        Request $request,
        Organization $organization
    ): RedirectResponse {
        $validated =
            $this->validateLocation(
                $request,
                $organization
            );

        HrisWorkLocation::query()
            ->create([
                'organization_id' =>
                $organization->id,

                'branch_id' =>
                $validated['branch_id']
                    ?? null,

                'name' =>
                $validated['name'],

                'type' =>
                $validated['type'],

                'address' =>
                $validated['address']
                    ?? null,

                'latitude' =>
                $validated['latitude'],

                'longitude' =>
                $validated['longitude'],

                'radius_meters' =>
                $validated['radius_meters'],

                'geofence_enabled' =>
                $request->boolean(
                    'geofence_enabled'
                ),

                'is_active' =>
                $request->boolean(
                    'is_active'
                ),

                'notes' =>
                $validated['notes']
                    ?? null,
            ]);

        return back()
            ->with(
                'success',
                'Work Location berhasil ditambahkan.'
            );
    }


    public function update(
        Request $request,
        Organization $organization,
        HrisWorkLocation $workLocation
    ): RedirectResponse {
        $this->guardOrganization(
            $organization,
            $workLocation
        );

        $validated =
            $this->validateLocation(
                $request,
                $organization,
                $workLocation
            );

        $workLocation->update([
            'branch_id' =>
            $validated['branch_id']
                ?? null,

            'name' =>
            $validated['name'],

            'type' =>
            $validated['type'],

            'address' =>
            $validated['address']
                ?? null,

            'latitude' =>
            $validated['latitude'],

            'longitude' =>
            $validated['longitude'],

            'radius_meters' =>
            $validated['radius_meters'],

            'geofence_enabled' =>
            $request->boolean(
                'geofence_enabled'
            ),

            'is_active' =>
            $request->boolean(
                'is_active'
            ),

            'notes' =>
            $validated['notes']
                ?? null,
        ]);

        return back()
            ->with(
                'success',
                'Work Location berhasil diperbarui.'
            );
    }


    public function assignEmployees(
        Request $request,
        Organization $organization,
        HrisWorkLocation $workLocation
    ): RedirectResponse {
        $this->guardOrganization(
            $organization,
            $workLocation
        );

        $validated =
            $request->validate([
                'employee_ids' => [
                    'nullable',
                    'array',
                ],

                'employee_ids.*' => [
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
            ]);

        $employeeIds =
            collect(
                $validated['employee_ids']
                    ?? []
            )
            ->map(
                fn($id) =>
                (int) $id
            )
            ->unique()
            ->values();

        DB::transaction(
            function () use (
                $organization,
                $workLocation,
                $employeeIds
            ) {
                DB::table(
                    'hris_employee_work_locations'
                )
                    ->where(
                        'organization_id',
                        $organization->id
                    )
                    ->where(
                        'work_location_id',
                        $workLocation->id
                    )
                    ->delete();

                $now =
                    now();

                $rows =
                    $employeeIds
                    ->map(
                        fn($employeeId) => [
                            'organization_id' =>
                            $organization->id,

                            'employee_id' =>
                            $employeeId,

                            'work_location_id' =>
                            $workLocation->id,

                            'is_primary' =>
                            false,

                            'created_at' =>
                            $now,

                            'updated_at' =>
                            $now,
                        ]
                    )
                    ->all();

                if (!empty($rows)) {
                    DB::table(
                        'hris_employee_work_locations'
                    )->insert($rows);
                }
            }
        );

        return back()
            ->with(
                'success',
                'Assignment employee untuk Work Location berhasil diperbarui.'
            );
    }


    private function validateLocation(
        Request $request,
        Organization $organization,
        ?HrisWorkLocation $workLocation = null
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'required',

                Rule::in([
                    'office',
                    'branch',
                    'warehouse',
                    'site',
                    'homebase',
                    'other',
                ]),
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

            'address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],

            'radius_meters' => [
                'required',
                'integer',
                'min:10',
                'max:10000',
            ],

            'geofence_enabled' => [
                'nullable',
                'boolean',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);
    }


    private function guardOrganization(
        Organization $organization,
        HrisWorkLocation $workLocation
    ): void {
        if (
            (int) $workLocation
                ->organization_id
            !==
            (int) $organization->id
        ) {
            abort(404);
        }
    }
}
