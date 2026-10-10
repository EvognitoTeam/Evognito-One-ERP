<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Organization;
use App\Modules\Sales\Models\SalesArea;
use App\Modules\Sales\Models\SalesEmployeeProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SalesTeamController extends Controller
{
    public function index(Request $request, Organization $organization): View
    {
        $search = trim((string) $request->query('search', ''));
        $areaId = $request->integer('area_id') ?: null;
        $status = $request->query('status');

        $profiles = SalesEmployeeProfile::query()
            ->where('organization_id', $organization->id)
            ->with(['employee.user', 'employee.branch', 'employee.position', 'supervisor.user', 'area'])
            ->when($areaId, fn ($q) => $q->where('area_id', $areaId))
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.$search.'%';
                $q->whereHas('employee', function ($employee) use ($like) {
                    $employee->where(function ($query) use ($like) {
                        $query->where('name', 'like', $like)
                            ->orWhere('employee_number', 'like', $like)
                            ->orWhereHas('user', fn ($user) => $user->where('name', 'like', $like)->orWhere('email', 'like', $like));
                    });
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $editing = null;
        if ($request->filled('edit')) {
            $editing = SalesEmployeeProfile::query()
                ->where('organization_id', $organization->id)
                ->with(['employee.user', 'supervisor.user', 'area'])
                ->findOrFail((int) $request->query('edit'));
        }

        $employees = Employee::query()
            ->where('organization_id', $organization->id)
            ->with(['user', 'branch', 'position'])
            ->orderBy('employee_number')
            ->get();

        $areas = SalesArea::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('modules.sales.team.index', compact(
            'organization', 'profiles', 'employees', 'areas', 'editing', 'search', 'areaId', 'status'
        ));
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $this->validatePayload($request, $organization);

        SalesEmployeeProfile::create([
            ...$validated,
            'organization_id' => $organization->id,
        ]);

        return redirect()
            ->route('sales.team.index', ['organization' => $organization->slug])
            ->with('success', __('sales.messages.team_saved'));
    }

    public function update(Request $request, Organization $organization, int $profile): RedirectResponse
    {
        $record = SalesEmployeeProfile::query()
            ->where('organization_id', $organization->id)
            ->findOrFail($profile);

        $validated = $this->validatePayload($request, $organization, $record->id);
        $record->update($validated);

        return redirect()
            ->route('sales.team.index', ['organization' => $organization->slug])
            ->with('success', __('sales.messages.team_updated'));
    }

    public function destroy(Organization $organization, int $profile): RedirectResponse
    {
        SalesEmployeeProfile::query()
            ->where('organization_id', $organization->id)
            ->findOrFail($profile)
            ->delete();

        return back()->with('success', __('sales.messages.team_removed'));
    }

    private function validatePayload(Request $request, Organization $organization, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'employee_id' => [
                'required', 'integer',
                Rule::exists('employees', 'id')->where('organization_id', $organization->id),
                Rule::unique('sales_employee_profiles', 'employee_id')
                    ->where('organization_id', $organization->id)
                    ->ignore($ignoreId),
            ],
            'supervisor_employee_id' => [
                'nullable', 'integer',
                Rule::exists('employees', 'id')->where('organization_id', $organization->id),
                'different:employee_id',
            ],
            'area_id' => [
                'nullable', 'integer',
                Rule::exists('sales_areas', 'id')->where('organization_id', $organization->id),
            ],
            'daily_visit_target' => ['required', 'integer', 'min:0', 'max:100'],
            'is_field_sales' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_field_sales'] = $request->boolean('is_field_sales');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
