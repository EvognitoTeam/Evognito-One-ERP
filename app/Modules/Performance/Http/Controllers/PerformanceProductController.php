<?php

namespace App\Modules\Performance\Http\Controllers;

use App\Core\Imports\Models\MasterDataImport;
use App\Models\Employee;
use App\Models\Organization;
use App\Modules\Performance\Models\PerformanceEmployeeProduct;
use App\Modules\Performance\Models\PerformanceProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PerformanceProductController extends PerformanceController
{
    public function index(Organization $organization): View
    {
        $products = PerformanceProduct::query()
            ->withCount(['employees', 'kpiAssignments'])
            ->where('organization_id', $organization->id)
            ->orderBy('name')
            ->get();

        $employees = Employee::query()
            ->where('organization_id', $organization->id)
            ->with(['user', 'position'])
            ->orderBy('id')
            ->get();

        $imports = MasterDataImport::query()
            ->where('organization_id', $organization->id)
            ->where('type', 'employee_master')
            ->latest('id')
            ->limit(10)
            ->get();

        $assignments = PerformanceEmployeeProduct::query()
            ->with(['employee.user', 'employee.position', 'product'])
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->orderByDesc('is_primary')
            ->orderBy('employee_id')
            ->get();

        return view('modules.performance.products.index', compact('organization', 'products', 'employees', 'assignments', 'imports'));
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        PerformanceProduct::create([
            'organization_id' => $organization->id,
            'code' => $data['code'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', __('performance.messages.product_created'));
    }

    public function assignEmployee(Request $request, Organization $organization, PerformanceProduct $product): RedirectResponse
    {
        $this->guardOrganization($organization, $product);

        $data = $request->validate([
            'employee_id' => ['required', 'integer'],
            'is_primary' => ['nullable', 'boolean'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);

        $employee = Employee::where('organization_id', $organization->id)->findOrFail($data['employee_id']);

        DB::transaction(function () use ($organization, $product, $employee, $data, $request) {
            $isPrimary = $request->boolean('is_primary');

            if ($isPrimary) {
                PerformanceEmployeeProduct::where('organization_id', $organization->id)
                    ->where('employee_id', $employee->id)
                    ->update(['is_primary' => false]);
            }

            PerformanceEmployeeProduct::updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'employee_id' => $employee->id,
                    'product_id' => $product->id,
                ],
                [
                    'is_primary' => $isPrimary,
                    'effective_from' => $data['effective_from'] ?? null,
                    'effective_until' => $data['effective_until'] ?? null,
                    'is_active' => true,
                ]
            );
        });

        return back()->with('success', __('performance.messages.employee_product_assigned'));
    }

    public function removeEmployee(Organization $organization, PerformanceProduct $product, Employee $employee): RedirectResponse
    {
        $this->guardOrganization($organization, $product);
        abort_unless((int) $employee->organization_id === (int) $organization->id, 404);

        PerformanceEmployeeProduct::where('organization_id', $organization->id)
            ->where('employee_id', $employee->id)
            ->where('product_id', $product->id)
            ->delete();

        return back()->with('success', __('performance.messages.employee_product_removed'));
    }
}
