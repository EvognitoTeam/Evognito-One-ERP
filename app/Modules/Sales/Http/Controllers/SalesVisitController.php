<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Organization;
use App\Modules\Sales\Models\SalesArea;
use App\Modules\Sales\Models\SalesCustomer;
use App\Modules\Sales\Models\SalesEmployeeProfile;
use App\Modules\Sales\Models\SalesProduct;
use App\Modules\Sales\Models\SalesReceipt;
use App\Modules\Sales\Models\SalesVisitSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SalesVisitController extends Controller
{
    public function index(Request $request, Organization $organization): View
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->endOfMonth()->toDateString());
        $employeeId = $request->integer('employee_id') ?: null;
        $customerId = $request->integer('customer_id') ?: null;
        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $visits = SalesVisitSchedule::query()
            ->where('organization_id', $organization->id)
            ->with(['employee.user', 'employee.branch', 'customer', 'area', 'products', 'attendance', 'receipts'])
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('start_date', [$from, $to])
                    ->orWhereBetween('end_date', [$from, $to])
                    ->orWhere(function ($range) use ($from, $to) {
                        $range->whereDate('start_date', '<=', $from)
                            ->whereDate('end_date', '>=', $to);
                    });
            })
            ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.$search.'%';
                $q->where(function ($query) use ($like) {
                    $query->where('purpose', 'like', $like)
                        ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', $like)->orWhere('address', 'like', $like))
                        ->orWhereHas('employee', function ($employee) use ($like) {
                            $employee->where('name', 'like', $like)
                                ->orWhere('employee_number', 'like', $like)
                                ->orWhereHas('user', fn ($user) => $user->where('name', 'like', $like));
                        });
                });
            })
            ->orderByDesc('start_date')
            ->orderByDesc('planned_start_time')
            ->paginate(20)
            ->withQueryString();

        $editing = null;
        if ($request->filled('edit')) {
            $editing = SalesVisitSchedule::query()
                ->where('organization_id', $organization->id)
                ->with('products')
                ->findOrFail((int) $request->query('edit'));
        }

        $salesEmployees = $this->salesEmployees($organization);
        $customers = SalesCustomer::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $areas = SalesArea::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $products = SalesProduct::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('modules.sales.visits.index', compact(
            'organization', 'visits', 'editing', 'salesEmployees', 'customers', 'areas', 'products',
            'from', 'to', 'employeeId', 'customerId', 'status', 'search'
        ));
    }

    public function show(Organization $organization, int $visit): View
    {
        $record = SalesVisitSchedule::query()
            ->where('organization_id', $organization->id)
            ->with([
                'employee.user', 'employee.branch', 'employee.position', 'customer', 'area',
                'products', 'attendance', 'receipts.createdBy',
            ])
            ->findOrFail($visit);

        return view('modules.sales.visits.show', [
            'organization' => $organization,
            'visit' => $record,
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $this->validatePayload($request, $organization);
        $productIds = $validated['product_ids'] ?? [];
        unset($validated['product_ids']);

        DB::transaction(function () use ($validated, $productIds, $organization, $request) {
            $visit = SalesVisitSchedule::create([
                ...$validated,
                'organization_id' => $organization->id,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ]);

            $visit->products()->sync(
                collect($productIds)->mapWithKeys(
                    fn ($id) => [(int) $id => ['organization_id' => $organization->id]]
                )->all()
            );
        });

        return redirect()
            ->route('sales.visits.index', ['organization' => $organization->slug])
            ->with('success', __('sales.messages.visit_saved'));
    }

    public function update(Request $request, Organization $organization, int $visit): RedirectResponse
    {
        $record = SalesVisitSchedule::query()
            ->where('organization_id', $organization->id)
            ->findOrFail($visit);

        $validated = $this->validatePayload($request, $organization);
        $productIds = $validated['product_ids'] ?? [];
        unset($validated['product_ids']);

        DB::transaction(function () use ($record, $validated, $productIds, $organization, $request) {
            $record->update([
                ...$validated,
                'updated_by' => $request->user()?->id,
            ]);

            $record->products()->sync(
                collect($productIds)->mapWithKeys(
                    fn ($id) => [(int) $id => ['organization_id' => $organization->id]]
                )->all()
            );
        });

        return redirect()
            ->route('sales.visits.index', ['organization' => $organization->slug])
            ->with('success', __('sales.messages.visit_updated'));
    }

    public function destroy(Organization $organization, int $visit): RedirectResponse
    {
        SalesVisitSchedule::query()
            ->where('organization_id', $organization->id)
            ->findOrFail($visit)
            ->delete();

        return back()->with('success', __('sales.messages.visit_removed'));
    }

    public function uploadReceipt(Request $request, Organization $organization, int $visit): RedirectResponse
    {
        $schedule = SalesVisitSchedule::query()
            ->where('organization_id', $organization->id)
            ->findOrFail($visit);

        $validated = $request->validate([
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $file = $validated['receipt'];
        $path = $file->store('sales/receipts/'.$organization->id, 'public');

        SalesReceipt::create([
            'organization_id' => $organization->id,
            'visit_schedule_id' => $schedule->id,
            'employee_id' => $schedule->employee_id,
            'customer_id' => $schedule->customer_id,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'amount' => $validated['amount'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'uploaded_at' => now(),
            'created_by' => $request->user()?->id,
        ]);

        return back()->with('success', __('sales.messages.receipt_uploaded'));
    }

    public function destroyReceipt(Organization $organization, int $receipt): RedirectResponse
    {
        $record = SalesReceipt::query()
            ->where('organization_id', $organization->id)
            ->findOrFail($receipt);

        Storage::disk('public')->delete($record->file_path);
        $record->delete();

        return back()->with('success', __('sales.messages.receipt_removed'));
    }

    private function validatePayload(Request $request, Organization $organization): array
    {
        return $request->validate([
            'employee_id' => [
                'required', 'integer',
                Rule::exists('employees', 'id')->where('organization_id', $organization->id),
            ],
            'customer_id' => [
                'required', 'integer',
                Rule::exists('sales_customers', 'id')->where('organization_id', $organization->id),
            ],
            'area_id' => [
                'nullable', 'integer',
                Rule::exists('sales_areas', 'id')->where('organization_id', $organization->id),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'planned_start_time' => ['nullable', 'date_format:H:i'],
            'planned_end_time' => ['nullable', 'date_format:H:i'],
            'status' => ['required', Rule::in(['scheduled', 'in_progress', 'completed', 'cancelled'])],
            'purpose' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => [
                'integer',
                Rule::exists('sales_products', 'id')->where('organization_id', $organization->id),
            ],
        ]);
    }

    private function salesEmployees(Organization $organization)
    {
        $profileEmployeeIds = SalesEmployeeProfile::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->where('is_field_sales', true)
            ->pluck('employee_id');

        return Employee::query()
            ->where('organization_id', $organization->id)
            ->whereIn('id', $profileEmployeeIds)
            ->with(['user', 'branch', 'position'])
            ->orderBy('employee_number')
            ->get();
    }
}
