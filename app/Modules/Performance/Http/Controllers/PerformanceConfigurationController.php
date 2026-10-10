<?php

namespace App\Modules\Performance\Http\Controllers;

use App\Models\Organization;
use App\Modules\Performance\Models\PerformancePolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceConfigurationController extends PerformanceController
{
    public function index(Organization $organization): View
    {
        $policy = PerformancePolicy::resolveFor($organization);
        return view('modules.performance.configuration.index', compact('organization', 'policy'));
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $data = $request->validate([
            'competency_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'kpi_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'require_level1_approval' => ['nullable', 'boolean'],
            'require_level2_approval' => ['nullable', 'boolean'],
            'require_employee_acknowledgement' => ['nullable', 'boolean'],
        ]);

        abort_unless(
            abs(((float) $data['competency_weight'] + (float) $data['kpi_weight']) - 100) < 0.001,
            422,
            __('performance.validation.final_weights')
        );

        PerformancePolicy::resolveFor($organization)->update([
            'competency_weight' => $data['competency_weight'],
            'kpi_weight' => $data['kpi_weight'],
            'require_level1_approval' => $request->boolean('require_level1_approval'),
            'require_level2_approval' => $request->boolean('require_level2_approval'),
            'require_employee_acknowledgement' => $request->boolean('require_employee_acknowledgement'),
        ]);

        return back()->with('success', __('performance.messages.configuration_saved'));
    }
}
