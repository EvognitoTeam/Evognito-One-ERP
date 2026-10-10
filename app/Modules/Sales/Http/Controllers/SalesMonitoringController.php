<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Organization;
use App\Modules\Sales\Models\SalesEmployeeProfile;
use App\Modules\Sales\Models\SalesLocationTrack;
use App\Modules\Sales\Models\SalesPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesMonitoringController extends Controller
{
    public function index(Request $request, Organization $organization): View
    {
        $date = $request->query('date', now()->toDateString());
        $employeeId = $request->integer('employee_id') ?: null;

        $employees = $this->salesEmployees($organization);
        $policy = SalesPolicy::resolveFor($organization);

        return view('modules.sales.monitoring.index', compact(
            'organization', 'date', 'employeeId', 'employees', 'policy'
        ));
    }

    public function data(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'employee_id' => ['nullable', 'integer'],
        ]);

        $date = $validated['date'] ?? now()->toDateString();
        $employeeId = $validated['employee_id'] ?? null;

        $query = SalesLocationTrack::query()
            ->where('organization_id', $organization->id)
            ->whereDate('recorded_at', $date)
            ->with(['employee.user', 'employee.branch', 'visitSchedule.customer'])
            ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
            ->orderBy('recorded_at');

        $tracks = $query->get();

        $employees = $tracks
            ->groupBy('employee_id')
            ->map(function ($points) {
                $latest = $points->last();
                $employee = $latest?->employee;

                return [
                    'employee_id' => $latest?->employee_id,
                    'employee_name' => $this->employeeName($employee),
                    'employee_number' => $employee?->employee_number,
                    'branch' => $employee?->branch?->name,
                    'latest' => $latest ? [
                        'latitude' => (float) $latest->latitude,
                        'longitude' => (float) $latest->longitude,
                        'accuracy_meters' => $latest->accuracy_meters !== null ? (float) $latest->accuracy_meters : null,
                        'speed_mps' => $latest->speed_mps !== null ? (float) $latest->speed_mps : null,
                        'battery_level' => $latest->battery_level,
                        'is_moving' => $latest->is_moving,
                        'recorded_at' => $latest->recorded_at?->toIso8601String(),
                        'visit_customer' => $latest->visitSchedule?->customer?->name,
                    ] : null,
                    'points' => $points->map(fn ($point) => [
                        'latitude' => (float) $point->latitude,
                        'longitude' => (float) $point->longitude,
                        'accuracy_meters' => $point->accuracy_meters !== null ? (float) $point->accuracy_meters : null,
                        'recorded_at' => $point->recorded_at?->toIso8601String(),
                    ])->values(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'date' => $date,
            'employees' => $employees,
            'total_points' => $tracks->count(),
            'refreshed_at' => now()->toIso8601String(),
        ]);
    }

    private function salesEmployees(Organization $organization)
    {
        $ids = SalesEmployeeProfile::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->where('is_field_sales', true)
            ->pluck('employee_id');

        return Employee::query()
            ->where('organization_id', $organization->id)
            ->whereIn('id', $ids)
            ->with(['user', 'branch'])
            ->orderBy('employee_number')
            ->get();
    }

    private function employeeName(?Employee $employee): string
    {
        return $employee?->name
            ?? $employee?->user?->name
            ?? ($employee ? 'Employee #'.$employee->id : '-');
    }
}
