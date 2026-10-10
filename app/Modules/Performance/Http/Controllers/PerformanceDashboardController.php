<?php

namespace App\Modules\Performance\Http\Controllers;

use App\Models\Organization;
use App\Modules\Performance\Models\PerformanceEmployeeProduct;
use App\Modules\Performance\Models\PerformanceKpi;
use App\Modules\Performance\Models\PerformanceKpiProductAssignment;
use App\Modules\Performance\Models\PerformanceProduct;
use Illuminate\View\View;

class PerformanceDashboardController extends PerformanceController
{
    public function index(Organization $organization): View
    {
        $stats = [
            'products' => PerformanceProduct::where('organization_id', $organization->id)->where('is_active', true)->count(),
            'kpis' => PerformanceKpi::where('organization_id', $organization->id)->where('is_active', true)->count(),
            'assignments' => PerformanceKpiProductAssignment::where('organization_id', $organization->id)->where('is_active', true)->count(),
            'employeeProducts' => PerformanceEmployeeProduct::where('organization_id', $organization->id)->where('is_active', true)->count(),
        ];

        $products = PerformanceProduct::query()
            ->withCount(['employees', 'kpiAssignments'])
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(8)
            ->get();

        return view('modules.performance.dashboard', compact('organization', 'stats', 'products'));
    }
}
