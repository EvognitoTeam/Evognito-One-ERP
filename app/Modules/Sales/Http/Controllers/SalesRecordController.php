<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Modules\Sales\Models\SalesRecord;
use App\Modules\Sales\Services\SalesRecordImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SalesRecordController extends Controller
{
    public function __construct(
        private readonly SalesRecordImportService $importService
    ) {
    }

    public function index(Request $request, Organization $organization): View
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->endOfMonth()->toDateString());
        $search = trim((string) $request->query('search', ''));
        $salesman = trim((string) $request->query('salesman', ''));

        $records = SalesRecord::query()
            ->where('organization_id', $organization->id)
            ->whereBetween('invoice_date', [$from, $to])
            ->when($salesman !== '', fn ($q) => $q->where('salesman', $salesman))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.$search.'%';
                $q->where(function ($query) use ($like) {
                    $query->where('product_name', 'like', $like)
                        ->orWhere('customer_name', 'like', $like)
                        ->orWhere('customer_number', 'like', $like)
                        ->orWhere('invoice_no', 'like', $like)
                        ->orWhere('salesman', 'like', $like)
                        ->orWhere('area', 'like', $like);
                });
            })
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate(40)
            ->withQueryString();

        $salesmen = SalesRecord::query()
            ->where('organization_id', $organization->id)
            ->whereNotNull('salesman')
            ->where('salesman', '<>', '')
            ->distinct()
            ->orderBy('salesman')
            ->pluck('salesman');

        $totalsQuery = SalesRecord::query()
            ->where('organization_id', $organization->id)
            ->whereBetween('invoice_date', [$from, $to])
            ->when($salesman !== '', fn ($q) => $q->where('salesman', $salesman))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.$search.'%';
                $q->where(function ($query) use ($like) {
                    $query->where('product_name', 'like', $like)
                        ->orWhere('customer_name', 'like', $like)
                        ->orWhere('invoice_no', 'like', $like)
                        ->orWhere('salesman', 'like', $like);
                });
            });

        $summary = [
            'records' => (clone $totalsQuery)->count(),
            'quantity' => (float) (clone $totalsQuery)->sum('quantity'),
            'gross_amount' => (float) (clone $totalsQuery)->sum('gross_amount'),
            'sales_dpp' => (float) (clone $totalsQuery)->sum('sales_dpp'),
            'owing' => (float) (clone $totalsQuery)->sum('owing'),
        ];

        return view('modules.sales.records.index', compact(
            'organization', 'records', 'salesmen', 'summary', 'from', 'to', 'search', 'salesman'
        ));
    }

    public function import(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        try {
            $result = $this->importService->import(
                $organization,
                $validated['file'],
                $request->user()?->id
            );
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            'success',
            __('sales.messages.records_imported', [
                'created' => $result['created'],
                'skipped' => $result['skipped'],
            ])
        );
    }

    public function edit(Organization $organization, int $record): View
    {
        $salesRecord = SalesRecord::query()
            ->where('organization_id', $organization->id)
            ->findOrFail($record);

        return view('modules.sales.records.edit', compact('organization', 'salesRecord'));
    }

    public function update(Request $request, Organization $organization, int $record): RedirectResponse
    {
        $salesRecord = SalesRecord::query()
            ->where('organization_id', $organization->id)
            ->findOrFail($record);

        $validated = $request->validate([
            'product_name' => ['nullable', 'string', 'max:255'],
            'packaging' => ['nullable', 'string', 'max:255'],
            'principal_product_code' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:3000'],
            'area' => ['nullable', 'string', 'max:255'],
            'invoice_date' => ['nullable', 'date'],
            'delivery_note_no' => ['nullable', 'string', 'max:255'],
            'invoice_no' => ['nullable', 'string', 'max:255'],
            'salesman' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric'],
            'gross_amount' => ['nullable', 'numeric'],
            'sales_dpp' => ['nullable', 'numeric'],
            'owing' => ['nullable', 'numeric'],
            'market_sector' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'department_name' => ['nullable', 'string', 'max:255'],
        ]);

        $salesRecord->update($validated);

        return redirect()
            ->route('sales.records.index', ['organization' => $organization->slug])
            ->with('success', __('sales.messages.record_updated'));
    }

    public function destroy(Organization $organization, int $record): RedirectResponse
    {
        SalesRecord::query()
            ->where('organization_id', $organization->id)
            ->findOrFail($record)
            ->delete();

        return back()->with('success', __('sales.messages.record_removed'));
    }
}
