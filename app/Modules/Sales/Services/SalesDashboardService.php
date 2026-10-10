<?php

namespace App\Modules\Sales\Services;

use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalesDashboardService
{
    public function build(
        Organization $organization
    ): array {
        $today =
            Carbon::today();

        $fieldSalesCount =
            $this->countFieldSales(
                $organization
            );

        $activeCustomers =
            $this->countActiveCustomers(
                $organization
            );

        $visitsToday =
            $this->countVisitsToday(
                $organization,
                $today
            );

        $completedToday =
            $this->countCompletedVisitsToday(
                $organization,
                $today
            );

        $inProgressToday =
            $this->countInProgressVisitsToday(
                $organization,
                $today
            );

        $upcomingVisits =
            $this->upcomingVisits(
                $organization,
                $today
            );

        return [
            'fieldSalesCount' => $fieldSalesCount,
            'activeCustomers' => $activeCustomers,
            'visitsToday' => $visitsToday,
            'completedToday' => $completedToday,
            'inProgressToday' => $inProgressToday,
            'upcomingVisits' => $upcomingVisits,
        ];
    }

    private function countFieldSales(
        Organization $organization
    ): int {
        if (
            !Schema::hasTable(
                'sales_employee_profiles'
            )
        ) {
            return 0;
        }

        return DB::table(
            'sales_employee_profiles'
        )
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(
                'is_field_sales',
                true
            )
            ->where(
                'is_active',
                true
            )
            ->count();
    }

    private function countActiveCustomers(
        Organization $organization
    ): int {
        if (
            !Schema::hasTable(
                'sales_customers'
            )
        ) {
            return 0;
        }

        return DB::table(
            'sales_customers'
        )
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(
                'is_active',
                true
            )
            ->count();
    }

    private function countVisitsToday(
        Organization $organization,
        Carbon $today
    ): int {
        if (
            !Schema::hasTable(
                'sales_visit_schedules'
            )
        ) {
            return 0;
        }

        return DB::table(
            'sales_visit_schedules'
        )
            ->where(
                'organization_id',
                $organization->id
            )
            ->whereDate(
                'start_date',
                '<=',
                $today->toDateString()
            )
            ->whereDate(
                'end_date',
                '>=',
                $today->toDateString()
            )
            ->whereNotIn(
                'status',
                [
                    'cancelled',
                ]
            )
            ->count();
    }

    private function countCompletedVisitsToday(
        Organization $organization,
        Carbon $today
    ): int {
        if (
            !Schema::hasTable(
                'sales_visit_schedules'
            )
        ) {
            return 0;
        }

        return DB::table(
            'sales_visit_schedules'
        )
            ->where(
                'organization_id',
                $organization->id
            )
            ->whereDate(
                'start_date',
                '<=',
                $today->toDateString()
            )
            ->whereDate(
                'end_date',
                '>=',
                $today->toDateString()
            )
            ->where(
                'status',
                'completed'
            )
            ->count();
    }

    private function countInProgressVisitsToday(
        Organization $organization,
        Carbon $today
    ): int {
        if (
            !Schema::hasTable(
                'sales_visit_schedules'
            )
        ) {
            return 0;
        }

        return DB::table(
            'sales_visit_schedules'
        )
            ->where(
                'organization_id',
                $organization->id
            )
            ->whereDate(
                'start_date',
                '<=',
                $today->toDateString()
            )
            ->whereDate(
                'end_date',
                '>=',
                $today->toDateString()
            )
            ->where(
                'status',
                'in_progress'
            )
            ->count();
    }

    private function upcomingVisits(
        Organization $organization,
        Carbon $today
    ): Collection {
        if (
            !Schema::hasTable(
                'sales_visit_schedules'
            )
            ||
            !Schema::hasTable(
                'sales_customers'
            )
            ||
            !Schema::hasTable(
                'employees'
            )
        ) {
            return collect();
        }

        $employeeHasName =
            Schema::hasColumn(
                'employees',
                'name'
            );

        $employeeHasNumber =
            Schema::hasColumn(
                'employees',
                'employee_number'
            );

        $employeeHasUserId =
            Schema::hasColumn(
                'employees',
                'user_id'
            )
            &&
            Schema::hasTable(
                'users'
            );

        $query =
            DB::table(
                'sales_visit_schedules as schedules'
            )
                ->leftJoin(
                    'sales_customers as customers',
                    function ($join) {
                        $join
                            ->on(
                                'customers.id',
                                '=',
                                'schedules.customer_id'
                            )
                            ->on(
                                'customers.organization_id',
                                '=',
                                'schedules.organization_id'
                            );
                    }
                )
                ->leftJoin(
                    'employees',
                    function ($join) {
                        $join
                            ->on(
                                'employees.id',
                                '=',
                                'schedules.employee_id'
                            )
                            ->on(
                                'employees.organization_id',
                                '=',
                                'schedules.organization_id'
                            );
                    }
                );

        if (
            $employeeHasUserId
        ) {
            $query->leftJoin(
                'users',
                'users.id',
                '=',
                'employees.user_id'
            );
        }

        $employeeNameSelect =
            $employeeHasName
                ? 'employees.name as employee_name'
                : (
                    $employeeHasUserId
                        ? 'users.name as employee_name'
                        : DB::raw(
                            "CONCAT('Employee #', schedules.employee_id) as employee_name"
                        )
                );

        $employeeNumberSelect =
            $employeeHasNumber
                ? 'employees.employee_number'
                : DB::raw(
                    'NULL as employee_number'
                );

        return $query
            ->where(
                'schedules.organization_id',
                $organization->id
            )
            ->whereDate(
                'schedules.end_date',
                '>=',
                $today->toDateString()
            )
            ->whereNotIn(
                'schedules.status',
                [
                    'completed',
                    'cancelled',
                ]
            )
            ->orderBy(
                'schedules.start_date'
            )
            ->orderBy(
                'schedules.planned_start_time'
            )
            ->limit(8)
            ->get([
                'schedules.id',
                'schedules.ulid',
                'schedules.start_date',
                'schedules.end_date',
                'schedules.planned_start_time',
                'schedules.planned_end_time',
                'schedules.status',
                'schedules.purpose',
                'customers.name as customer_name',
                'customers.address as customer_address',
                $employeeNumberSelect,
                $employeeNameSelect,
            ]);
    }
}
