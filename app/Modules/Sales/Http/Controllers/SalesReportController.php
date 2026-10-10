<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Modules\Sales\Models\SalesArea;
use App\Modules\Sales\Models\SalesCustomer;
use App\Modules\Sales\Models\SalesEmployeeProfile;
use App\Modules\Sales\Models\SalesLocationTrack;
use App\Modules\Sales\Models\SalesRecord;
use App\Modules\Sales\Models\SalesVisitSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportController extends Controller
{
    public function index(Request $request, Organization $organization): View
    {
        $filters = $this->filters($request);
        $type = $filters['type'];

        $summary = match ($type) {
            'sales' => $this->salesSummary($organization, $filters),
            'tracking' => $this->trackingSummary($organization, $filters),
            default => $this->visitSummary($organization, $filters),
        };

        $employees = SalesEmployeeProfile::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->with('employee.user')
            ->get();

        $customers = SalesCustomer::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $areas = SalesArea::query()
            ->where('organization_id', $organization->id)
            ->orderBy('name')
            ->get();

        return view('modules.sales.reports.index', compact(
            'organization', 'filters', 'type', 'summary', 'employees', 'customers', 'areas'
        ));
    }

    public function export(Request $request, Organization $organization): StreamedResponse
    {
        $filters = $this->filters($request);
        $type = $filters['type'];
        $filename = 'sales-force-'.$type.'-'.$filters['from'].'-to-'.$filters['to'].'.csv';

        return Response::streamDownload(function () use ($organization, $filters, $type) {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");

            match ($type) {
                'sales' => $this->writeSalesCsv($handle, $organization, $filters),
                'tracking' => $this->writeTrackingCsv($handle, $organization, $filters),
                default => $this->writeVisitCsv($handle, $organization, $filters),
            };

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function visitSummary(Organization $organization, array $filters): array
    {
        $query = $this->visitQuery($organization, $filters);

        return [
            'total' => (clone $query)->count(),
            'scheduled' => (clone $query)->where('status', 'scheduled')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ];
    }

    private function salesSummary(Organization $organization, array $filters): array
    {
        $query = $this->salesQuery($organization, $filters);

        return [
            'records' => (clone $query)->count(),
            'quantity' => (float) (clone $query)->sum('quantity'),
            'gross_amount' => (float) (clone $query)->sum('gross_amount'),
            'sales_dpp' => (float) (clone $query)->sum('sales_dpp'),
            'owing' => (float) (clone $query)->sum('owing'),
        ];
    }

    private function trackingSummary(Organization $organization, array $filters): array
    {
        $query = $this->trackingQuery($organization, $filters);

        return [
            'points' => (clone $query)->count(),
            'employees' => (clone $query)->distinct('employee_id')->count('employee_id'),
            'moving_points' => (clone $query)->where('is_moving', true)->count(),
            'low_accuracy_points' => (clone $query)->where('accuracy_meters', '>', 100)->count(),
        ];
    }

    private function visitQuery(Organization $organization, array $filters)
    {
        return SalesVisitSchedule::query()
            ->where('organization_id', $organization->id)
            ->with(['employee.user', 'customer', 'area', 'attendance'])
            ->where(function ($q) use ($filters) {
                $q->whereBetween('start_date', [$filters['from'], $filters['to']])
                    ->orWhereBetween('end_date', [$filters['from'], $filters['to']]);
            })
            ->when($filters['employee_id'], fn ($q) => $q->where('employee_id', $filters['employee_id']))
            ->when($filters['customer_id'], fn ($q) => $q->where('customer_id', $filters['customer_id']))
            ->when($filters['area_id'], fn ($q) => $q->where('area_id', $filters['area_id']))
            ->when($filters['status'], fn ($q) => $q->where('status', $filters['status']));
    }

    private function salesQuery(Organization $organization, array $filters)
    {
        $query = SalesRecord::query()
            ->where('organization_id', $organization->id)
            ->whereBetween('invoice_date', [$filters['from'], $filters['to']]);

        if ($filters['salesman'] !== '') {
            $query->where('salesman', $filters['salesman']);
        }

        if ($filters['search'] !== '') {
            $like = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($like) {
                $q->where('customer_name', 'like', $like)
                    ->orWhere('product_name', 'like', $like)
                    ->orWhere('invoice_no', 'like', $like)
                    ->orWhere('salesman', 'like', $like);
            });
        }

        return $query;
    }

    private function trackingQuery(Organization $organization, array $filters)
    {
        return SalesLocationTrack::query()
            ->where('organization_id', $organization->id)
            ->with(['employee.user', 'visitSchedule.customer'])
            ->whereBetween('recorded_at', [
                $filters['from'].' 00:00:00',
                $filters['to'].' 23:59:59',
            ])
            ->when($filters['employee_id'], fn ($q) => $q->where('employee_id', $filters['employee_id']));
    }

    private function writeVisitCsv($handle, Organization $organization, array $filters): void
    {
        fputcsv($handle, [
            'Start Date', 'End Date', 'Sales Employee', 'Employee Number', 'Customer', 'Area',
            'Purpose', 'Status', 'Clock In', 'Clock Out', 'Clock In Geofence', 'Clock Out Geofence',
        ], ';');

        $this->visitQuery($organization, $filters)
            ->orderBy('start_date')
            ->chunkById(300, function ($rows) use ($handle) {
                foreach ($rows as $visit) {
                    fputcsv($handle, [
                        $visit->start_date?->format('Y-m-d'),
                        $visit->end_date?->format('Y-m-d'),
                        $this->employeeName($visit->employee),
                        $visit->employee?->employee_number,
                        $visit->customer?->name,
                        $visit->area?->name,
                        $visit->purpose,
                        $visit->status,
                        $visit->attendance?->clock_in?->format('Y-m-d H:i:s'),
                        $visit->attendance?->clock_out?->format('Y-m-d H:i:s'),
                        $this->geoText($visit->attendance?->clock_in_within_geofence),
                        $this->geoText($visit->attendance?->clock_out_within_geofence),
                    ], ';');
                }
            });
    }

    private function writeSalesCsv($handle, Organization $organization, array $filters): void
    {
        fputcsv($handle, [
            'Invoice Date', 'Invoice No', 'Salesman', 'Customer', 'Customer No', 'Area',
            'Product', 'Packaging', 'Quantity', 'Gross Amount', 'DPP Sales', 'Owing', 'Category', 'Market Sector',
        ], ';');

        $this->salesQuery($organization, $filters)
            ->orderBy('invoice_date')
            ->chunkById(500, function ($rows) use ($handle) {
                foreach ($rows as $record) {
                    fputcsv($handle, [
                        $record->invoice_date?->format('Y-m-d'), $record->invoice_no, $record->salesman,
                        $record->customer_name, $record->customer_number, $record->area, $record->product_name,
                        $record->packaging, $record->quantity, $record->gross_amount, $record->sales_dpp,
                        $record->owing, $record->category, $record->market_sector,
                    ], ';');
                }
            });
    }

    private function writeTrackingCsv($handle, Organization $organization, array $filters): void
    {
        fputcsv($handle, [
            'Recorded At', 'Employee', 'Employee Number', 'Latitude', 'Longitude', 'Accuracy (m)',
            'Speed (m/s)', 'Battery (%)', 'Moving', 'Visit Customer',
        ], ';');

        $this->trackingQuery($organization, $filters)
            ->orderBy('recorded_at')
            ->chunkById(1000, function ($rows) use ($handle) {
                foreach ($rows as $track) {
                    fputcsv($handle, [
                        $track->recorded_at?->format('Y-m-d H:i:s'),
                        $this->employeeName($track->employee),
                        $track->employee?->employee_number,
                        $track->latitude, $track->longitude, $track->accuracy_meters, $track->speed_mps,
                        $track->battery_level, $track->is_moving ? 'Yes' : 'No',
                        $track->visitSchedule?->customer?->name,
                    ], ';');
                }
            });
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'type' => ['nullable', 'in:visits,sales,tracking'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'employee_id' => ['nullable', 'integer'],
            'customer_id' => ['nullable', 'integer'],
            'area_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:scheduled,in_progress,completed,cancelled'],
            'salesman' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        return [
            'type' => $validated['type'] ?? 'visits',
            'from' => $validated['from'] ?? now()->startOfMonth()->toDateString(),
            'to' => $validated['to'] ?? now()->endOfMonth()->toDateString(),
            'employee_id' => $validated['employee_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'area_id' => $validated['area_id'] ?? null,
            'status' => $validated['status'] ?? null,
            'salesman' => trim($validated['salesman'] ?? ''),
            'search' => trim($validated['search'] ?? ''),
        ];
    }

    private function employeeName($employee): string
    {
        return $employee?->name ?? $employee?->user?->name ?? '-';
    }

    private function geoText(mixed $value): string
    {
        return $value === true ? 'Inside' : ($value === false ? 'Outside' : 'Unknown');
    }
}
