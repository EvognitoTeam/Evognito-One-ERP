<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Modules\Sales\Models\SalesArea;
use App\Modules\Sales\Models\SalesCustomer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SalesCustomerController extends Controller
{
    public function index(Request $request, Organization $organization): View
    {
        $search = trim((string) $request->query('search', ''));
        $areaId = $request->integer('area_id') ?: null;
        $status = $request->query('status');

        $customers = SalesCustomer::query()
            ->where('organization_id', $organization->id)
            ->with('area')
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.$search.'%';
                $q->where(function ($query) use ($like) {
                    $query->where('name', 'like', $like)
                        ->orWhere('code', 'like', $like)
                        ->orWhere('city', 'like', $like)
                        ->orWhere('address', 'like', $like)
                        ->orWhere('contact_name', 'like', $like);
                });
            })
            ->when($areaId, fn ($q) => $q->where('area_id', $areaId))
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $editing = null;
        if ($request->filled('edit')) {
            $editing = SalesCustomer::query()
                ->where('organization_id', $organization->id)
                ->findOrFail((int) $request->query('edit'));
        }

        $areas = SalesArea::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('modules.sales.customers.index', compact(
            'organization', 'customers', 'areas', 'editing', 'search', 'areaId', 'status'
        ));
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        SalesCustomer::create([
            ...$this->validatePayload($request, $organization),
            'organization_id' => $organization->id,
        ]);

        return redirect()
            ->route('sales.customers.index', ['organization' => $organization->slug])
            ->with('success', __('sales.messages.customer_saved'));
    }

    public function update(Request $request, Organization $organization, int $customer): RedirectResponse
    {
        $record = SalesCustomer::query()
            ->where('organization_id', $organization->id)
            ->findOrFail($customer);

        $record->update($this->validatePayload($request, $organization, $record->id));

        return redirect()
            ->route('sales.customers.index', ['organization' => $organization->slug])
            ->with('success', __('sales.messages.customer_updated'));
    }

    public function destroy(Organization $organization, int $customer): RedirectResponse
    {
        SalesCustomer::query()
            ->where('organization_id', $organization->id)
            ->findOrFail($customer)
            ->delete();

        return back()->with('success', __('sales.messages.customer_removed'));
    }

    private function validatePayload(Request $request, Organization $organization, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'code' => [
                'nullable', 'string', 'max:80',
                Rule::unique('sales_customers', 'code')
                    ->where('organization_id', $organization->id)
                    ->ignore($ignoreId),
            ],
            'name' => ['required', 'string', 'max:200'],
            'contact_name' => ['nullable', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:3000'],
            'city' => ['nullable', 'string', 'max:120'],
            'area_id' => [
                'nullable', 'integer',
                Rule::exists('sales_areas', 'id')->where('organization_id', $organization->id),
            ],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'geofence_radius_meters' => ['required', 'integer', 'min:10', 'max:50000'],
            'geofence_enabled' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $validated['geofence_enabled'] = $request->boolean('geofence_enabled');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
