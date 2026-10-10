<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Modules\Sales\Models\SalesArea;
use App\Modules\Sales\Models\SalesPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SalesConfigurationController extends Controller
{
    public function index(Request $request, Organization $organization): View
    {
        $areas = SalesArea::query()
            ->where('organization_id', $organization->id)
            ->orderBy('name')
            ->get();

        $editingArea = null;
        if ($request->filled('edit_area')) {
            $editingArea = SalesArea::query()
                ->where('organization_id', $organization->id)
                ->findOrFail((int) $request->query('edit_area'));
        }

        $policy = SalesPolicy::resolveFor($organization);

        return view('modules.sales.configuration.index', compact(
            'organization', 'areas', 'editingArea', 'policy'
        ));
    }

    public function storeArea(Request $request, Organization $organization): RedirectResponse
    {
        SalesArea::create([
            ...$this->validateArea($request, $organization),
            'organization_id' => $organization->id,
        ]);

        return back()->with('success', __('sales.messages.area_saved'));
    }

    public function updateArea(Request $request, Organization $organization, int $area): RedirectResponse
    {
        $record = SalesArea::query()
            ->where('organization_id', $organization->id)
            ->findOrFail($area);

        $record->update($this->validateArea($request, $organization, $record->id));

        return redirect()
            ->route('sales.configuration.index', ['organization' => $organization->slug])
            ->with('success', __('sales.messages.area_updated'));
    }

    public function destroyArea(Organization $organization, int $area): RedirectResponse
    {
        $record = SalesArea::query()
            ->where('organization_id', $organization->id)
            ->findOrFail($area);

        if ($record->customers()->exists() || $record->employeeProfiles()->exists()) {
            return back()->with('error', __('sales.messages.area_in_use'));
        }

        $record->delete();

        return back()->with('success', __('sales.messages.area_removed'));
    }

    public function updatePolicy(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validate([
            'default_customer_radius_meters' => ['required', 'integer', 'min:10', 'max:50000'],
            'tracking_moving_interval_seconds' => ['required', 'integer', 'min:5', 'max:3600'],
            'tracking_active_interval_seconds' => ['required', 'integer', 'min:5', 'max:3600'],
            'tracking_idle_interval_seconds' => ['required', 'integer', 'min:30', 'max:7200'],
            'tracking_min_distance_meters' => ['required', 'integer', 'min:1', 'max:5000'],
            'tracking_accuracy_limit_meters' => ['required', 'integer', 'min:5', 'max:5000'],
            'tracking_work_start' => ['required', 'date_format:H:i'],
            'tracking_work_end' => ['required', 'date_format:H:i'],
            'tracking_retention_days' => ['required', 'integer', 'min:1', 'max:1095'],
        ]);

        $policy = SalesPolicy::resolveFor($organization);
        $policy->update([
            ...$validated,
            'visit_geofence_required' => $request->boolean('visit_geofence_required'),
            'allow_outside_geofence' => $request->boolean('allow_outside_geofence'),
            'visit_photo_required' => $request->boolean('visit_photo_required'),
            'receipt_upload_required' => $request->boolean('receipt_upload_required'),
            'tracking_enabled' => $request->boolean('tracking_enabled'),
            'prevent_visit_after_work_end' => $request->boolean('prevent_visit_after_work_end'),
            'require_daily_attendance_before_visit' => $request->boolean('require_daily_attendance_before_visit'),
        ]);

        return back()->with('success', __('sales.messages.policy_updated'));
    }

    private function validateArea(Request $request, Organization $organization, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'code' => [
                'nullable', 'string', 'max:60',
                Rule::unique('sales_areas', 'code')
                    ->where('organization_id', $organization->id)
                    ->ignore($ignoreId),
            ],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:3000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
