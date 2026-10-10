<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleUserLicense;
use App\Models\Organization;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BillingUserCounter
{
    public function count(
        Organization $organization,
        string $scope,
        ?Module $module = null,
        ?Collection $planModules = null
    ): int {
        return match ($scope) {
            'all_users' =>
            $this->countOrganizationUsers(
                $organization
            ),

            'employees' =>
            $this->countEmployees(
                $organization
            ),

            'active_employees' =>
            $this->countActiveEmployees(
                $organization
            ),

            'licensed_users' =>
            $this->countLicensedUsers(
                $organization,
                $module,
                $planModules
            ),

            'active_module_users' =>
            throw ValidationException::withMessages([
                'billing' =>
                'Billing scope active_module_users belum memiliki usage meter.',
            ]),

            'students' =>
            throw ValidationException::withMessages([
                'billing' =>
                'Billing scope students belum terhubung ke Education meter.',
            ]),

            'teachers' =>
            throw ValidationException::withMessages([
                'billing' =>
                'Billing scope teachers belum terhubung ke Education meter.',
            ]),

            default =>
            throw ValidationException::withMessages([
                'billing' =>
                'Billing user scope tidak dikenali: ' . $scope,
            ]),
        };
    }

    protected function countOrganizationUsers(
        Organization $organization
    ): int {
        return $organization
            ->users()
            ->wherePivot(
                'status',
                'active'
            )
            ->where(
                'users.status',
                'active'
            )
            ->count();
    }

    protected function countEmployees(
        Organization $organization
    ): int {
        return $organization
            ->employees()
            ->count();
    }

    protected function countActiveEmployees(
        Organization $organization
    ): int {
        return $organization
            ->employees()
            ->where(
                'status',
                'active'
            )
            ->count();
    }

    protected function countLicensedUsers(
        Organization $organization,
        ?Module $module,
        ?Collection $planModules
    ): int {
        $query =
            ModuleUserLicense::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(
                'status',
                'active'
            )
            ->where(function ($query) {
                $query
                    ->whereNull('ended_at')
                    ->orWhere(
                        'ended_at',
                        '>',
                        now()
                    );
            });

        if ($module) {
            $query->where(
                'module_id',
                $module->id
            );
        } elseif (
            $planModules
            && $planModules->isNotEmpty()
        ) {
            $query->whereIn(
                'module_id',
                $planModules->pluck('id')
            );
        }

        return $query
            ->distinct('user_id')
            ->count('user_id');
    }
}
