<?php

namespace App\Modules\HRIS\Services;

use App\Models\Employee;
use App\Models\Organization;
use App\Modules\HRIS\Models\HrisWorkLocation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GeofenceService
{
    /*
    |--------------------------------------------------------------------------
    | Eligible Locations
    |--------------------------------------------------------------------------
    |
    | Priority:
    |
    | 1. Explicit employee assignment.
    | 2. Active work location linked to employee branch.
    |
    */

    public function eligibleLocations(
        Organization $organization,
        Employee $employee
    ): Collection {
        $assignedIds = DB::table(
            'hris_employee_work_locations'
        )
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(
                'employee_id',
                $employee->id
            )
            ->pluck(
                'work_location_id'
            );

        if ($assignedIds->isNotEmpty()) {
            return HrisWorkLocation::query()
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->whereIn(
                    'id',
                    $assignedIds
                )
                ->where(
                    'is_active',
                    true
                )
                ->get();
        }

        if (!empty($employee->branch_id)) {
            return HrisWorkLocation::query()
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->where(
                    'branch_id',
                    $employee->branch_id
                )
                ->where(
                    'is_active',
                    true
                )
                ->get();
        }

        return collect();
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve
    |--------------------------------------------------------------------------
    |
    | Tidak memblokir. Hanya mencari location terdekat dan hasil radius.
    |
    */

    public function resolve(
        Organization $organization,
        Employee $employee,
        float $latitude,
        float $longitude
    ): array {
        $locations = $this->eligibleLocations(
            $organization,
            $employee
        );

        if ($locations->isEmpty()) {
            return [
                'location' => null,
                'distance_meters' => null,
                'within_geofence' => null,
            ];
        }

        $nearest = null;

        foreach ($locations as $location) {
            if (
                $location->latitude === null
                ||
                $location->longitude === null
            ) {
                continue;
            }

            $distance = $this->distanceMeters(
                $latitude,
                $longitude,
                (float) $location->latitude,
                (float) $location->longitude
            );

            $within = !$location->geofence_enabled
                || $distance <= (float) $location->radius_meters;

            $candidate = [
                'location' => $location,
                'distance_meters' => round(
                    $distance,
                    2
                ),
                'within_geofence' => $within,
            ];

            if (
                $nearest === null
                ||
                $candidate['distance_meters']
                    <
                    $nearest['distance_meters']
            ) {
                $nearest = $candidate;
            }
        }

        return $nearest ?? [
            'location' => null,
            'distance_meters' => null,
            'within_geofence' => null,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Assert Allowed
    |--------------------------------------------------------------------------
    */

    public function assertAllowed(
        Organization $organization,
        Employee $employee,
        float $latitude,
        float $longitude
    ): array {
        $resolved = $this->resolve(
            $organization,
            $employee,
            $latitude,
            $longitude
        );

        if (!$resolved['location']) {
            throw ValidationException::withMessages([
                'location' => __(
                    'attendance.errors.no_work_location'
                ),
            ]);
        }

        if (
            $resolved['within_geofence']
            !== true
        ) {
            $location = $resolved['location'];

            throw ValidationException::withMessages([
                'location' => __(
                    'attendance.errors.outside_geofence',
                    [
                        'location' => $location->name,
                        'distance' => number_format(
                            (float) $resolved['distance_meters'],
                            0,
                            ',',
                            '.'
                        ),
                        'radius' => (int) $location->radius_meters,
                    ]
                ),
            ]);
        }

        return $resolved;
    }


    /*
    |--------------------------------------------------------------------------
    | Haversine
    |--------------------------------------------------------------------------
    */

    public function distanceMeters(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadius = 6371000.0;

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);

        $deltaLat = deg2rad(
            $lat2 - $lat1
        );

        $deltaLon = deg2rad(
            $lon2 - $lon1
        );

        $a =
            sin($deltaLat / 2) ** 2
            +
            cos($lat1Rad)
            *
            cos($lat2Rad)
            *
            sin($deltaLon / 2) ** 2;

        /*
        |--------------------------------------------------------------------------
        | Floating point safety
        |--------------------------------------------------------------------------
        */

        $a = max(
            0.0,
            min(
                1.0,
                $a
            )
        );

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        return $earthRadius * $c;
    }
}
