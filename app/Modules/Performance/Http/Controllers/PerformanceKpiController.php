<?php

namespace App\Modules\Performance\Http\Controllers;

use App\Models\Organization;
use App\Modules\Performance\Models\PerformanceKpi;
use App\Modules\Performance\Models\PerformanceKpiProductAssignment;
use App\Modules\Performance\Models\PerformanceProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceKpiController extends PerformanceController
{
    public function index(Organization $organization): View
    {
        $kpis = PerformanceKpi::query()
            ->with(['productAssignments.product'])
            ->where('organization_id', $organization->id)
            ->orderBy('name')
            ->get();

        $products = PerformanceProduct::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('modules.performance.kpi.index', compact('organization', 'kpis', 'products'));
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:220'],
            'description' => ['nullable', 'string', 'max:3000'],
            'unit' => ['nullable', 'string', 'max:80'],
            'default_frequency' => ['required', 'string', 'max:50'],
            'default_calculation_type' => ['required', 'in:HIGHER_BETTER,LOWER_BETTER'],
        ]);

        PerformanceKpi::create([...$data, 'organization_id' => $organization->id, 'is_active' => true]);
        return back()->with('success', __('performance.messages.kpi_created'));
    }

    public function assignProduct(Request $request, Organization $organization, PerformanceKpi $kpi): RedirectResponse
    {
        $this->guardOrganization($organization, $kpi);

        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'target' => ['required', 'numeric', 'min:0'],
            'target_time' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', 'string', 'max:50'],
            'calculation_type' => ['required', 'in:HIGHER_BETTER,LOWER_BETTER'],
            'quantity_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'time_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_achievement' => ['required', 'numeric', 'min:100', 'max:999.99'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);

        abort_unless(
            abs(((float) $data['quantity_weight'] + (float) $data['time_weight']) - 100) < 0.001,
            422,
            __('performance.validation.quantity_time_weights')
        );

        $product = PerformanceProduct::where('organization_id', $organization->id)->findOrFail($data['product_id']);

        PerformanceKpiProductAssignment::updateOrCreate(
            [
                'organization_id' => $organization->id,
                'kpi_id' => $kpi->id,
                'product_id' => $product->id,
            ],
            [
                'weight' => $data['weight'],
                'target' => $data['target'],
                'target_time' => $data['target_time'],
                'frequency' => $data['frequency'],
                'calculation_type' => $data['calculation_type'],
                'quantity_weight' => $data['quantity_weight'],
                'time_weight' => $data['time_weight'],
                'max_achievement' => $data['max_achievement'],
                'notes' => $data['notes'] ?? null,
                'is_active' => true,
            ]
        );

        return back()->with('success', __('performance.messages.kpi_product_assigned'));
    }

    public function removeProductAssignment(Organization $organization, PerformanceKpi $kpi, PerformanceKpiProductAssignment $assignment): RedirectResponse
    {
        $this->guardOrganization($organization, $kpi);
        $this->guardOrganization($organization, $assignment);
        abort_unless((int) $assignment->kpi_id === (int) $kpi->id, 404);
        $assignment->delete();
        return back()->with('success', __('performance.messages.kpi_product_removed'));
    }
}
