<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Modules\Sales\Models\SalesProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SalesProductController extends Controller
{
    public function index(Request $request, Organization $organization): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $products = SalesProduct::query()
            ->where('organization_id', $organization->id)
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.$search.'%';
                $q->where(function ($query) use ($like) {
                    $query->where('name', 'like', $like)
                        ->orWhere('code', 'like', $like)
                        ->orWhere('packaging', 'like', $like)
                        ->orWhere('principal_product_code', 'like', $like);
                });
            })
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $editing = null;
        if ($request->filled('edit')) {
            $editing = SalesProduct::query()
                ->where('organization_id', $organization->id)
                ->findOrFail((int) $request->query('edit'));
        }

        return view('modules.sales.products.index', compact(
            'organization', 'products', 'editing', 'search', 'status'
        ));
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        SalesProduct::create([
            ...$this->validatePayload($request, $organization),
            'organization_id' => $organization->id,
        ]);

        return redirect()
            ->route('sales.products.index', ['organization' => $organization->slug])
            ->with('success', __('sales.messages.product_saved'));
    }

    public function update(Request $request, Organization $organization, int $product): RedirectResponse
    {
        $record = SalesProduct::query()
            ->where('organization_id', $organization->id)
            ->findOrFail($product);

        $record->update($this->validatePayload($request, $organization, $record->id));

        return redirect()
            ->route('sales.products.index', ['organization' => $organization->slug])
            ->with('success', __('sales.messages.product_updated'));
    }

    public function destroy(Organization $organization, int $product): RedirectResponse
    {
        SalesProduct::query()
            ->where('organization_id', $organization->id)
            ->findOrFail($product)
            ->delete();

        return back()->with('success', __('sales.messages.product_removed'));
    }

    private function validatePayload(Request $request, Organization $organization, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'code' => [
                'nullable', 'string', 'max:100',
                Rule::unique('sales_products', 'code')
                    ->where('organization_id', $organization->id)
                    ->ignore($ignoreId),
            ],
            'name' => ['required', 'string', 'max:200'],
            'packaging' => ['nullable', 'string', 'max:160'],
            'principal_product_code' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
