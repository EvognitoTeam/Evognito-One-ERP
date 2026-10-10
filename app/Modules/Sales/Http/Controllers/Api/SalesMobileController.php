<?php

namespace App\Modules\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Organization;
use App\Modules\Sales\Models\SalesEmployeeProfile;
use App\Modules\Sales\Models\SalesLocationTrack;
use App\Modules\Sales\Models\SalesPolicy;
use App\Modules\Sales\Models\SalesReceipt;
use App\Modules\Sales\Models\SalesVisitAttendance;
use App\Modules\Sales\Models\SalesVisitSchedule;
use App\Modules\Sales\Services\SalesGeofenceService;
use App\Modules\Sales\Services\SalesTrackingPolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SalesMobileController extends Controller
{
    public function __construct(
        private readonly SalesGeofenceService $geofenceService,
        private readonly SalesTrackingPolicyService $trackingPolicyService
    ) {
    }

    public function bootstrap(Request $request, Organization $organization): JsonResponse
    {
        $employee = $this->employeeFor($request, $organization);
        $profile = $this->salesProfile($organization, $employee);

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => [
                    'id' => $employee->id,
                    'employee_number' => $employee->employee_number,
                    'name' => $this->employeeName($employee),
                    'area_id' => $profile->area_id,
                    'daily_visit_target' => $profile->daily_visit_target,
                ],
                'tracking_policy' => $this->trackingPolicyService->resolve($organization),
                'visit_policy' => $this->visitPolicy(SalesPolicy::resolveFor($organization)),
            ],
        ]);
    }

    public function visits(Request $request, Organization $organization): JsonResponse
    {
        $employee = $this->employeeFor($request, $organization);
        $this->salesProfile($organization, $employee);

        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $from = $validated['from'] ?? now()->toDateString();
        $to = $validated['to'] ?? now()->addDays(7)->toDateString();

        $visits = SalesVisitSchedule::query()
            ->where('organization_id', $organization->id)
            ->where('employee_id', $employee->id)
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('start_date', [$from, $to])
                    ->orWhereBetween('end_date', [$from, $to])
                    ->orWhere(function ($range) use ($from, $to) {
                        $range->whereDate('start_date', '<=', $from)
                            ->whereDate('end_date', '>=', $to);
                    });
            })
            ->with(['customer', 'area', 'products', 'attendance', 'receipts'])
            ->orderBy('start_date')
            ->orderBy('planned_start_time')
            ->get()
            ->map(fn ($visit) => [
                'id' => $visit->id,
                'ulid' => $visit->ulid,
                'start_date' => $visit->start_date?->format('Y-m-d'),
                'end_date' => $visit->end_date?->format('Y-m-d'),
                'planned_start_time' => $visit->planned_start_time ? substr((string) $visit->planned_start_time, 0, 5) : null,
                'planned_end_time' => $visit->planned_end_time ? substr((string) $visit->planned_end_time, 0, 5) : null,
                'status' => $visit->status,
                'purpose' => $visit->purpose,
                'notes' => $visit->notes,
                'customer' => [
                    'id' => $visit->customer?->id,
                    'name' => $visit->customer?->name,
                    'address' => $visit->customer?->address,
                    'city' => $visit->customer?->city,
                    'latitude' => $visit->customer?->latitude !== null ? (float) $visit->customer->latitude : null,
                    'longitude' => $visit->customer?->longitude !== null ? (float) $visit->customer->longitude : null,
                    'geofence_radius_meters' => $visit->customer?->geofence_radius_meters,
                    'geofence_enabled' => $visit->customer?->geofence_enabled,
                ],
                'area' => $visit->area ? ['id' => $visit->area->id, 'name' => $visit->area->name] : null,
                'products' => $visit->products->map(fn ($product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'packaging' => $product->packaging,
                ])->values(),
                'attendance' => $visit->attendance ? [
                    'status' => $visit->attendance->status,
                    'clock_in' => $visit->attendance->clock_in?->toIso8601String(),
                    'clock_out' => $visit->attendance->clock_out?->toIso8601String(),
                    'clock_in_within_geofence' => $visit->attendance->clock_in_within_geofence,
                    'clock_out_within_geofence' => $visit->attendance->clock_out_within_geofence,
                ] : null,
                'receipt_count' => $visit->receipts->count(),
            ]);

        return response()->json(['success' => true, 'data' => $visits]);
    }

    public function clockIn(Request $request, Organization $organization, int $visit): JsonResponse
    {
        $employee = $this->employeeFor($request, $organization);
        $this->salesProfile($organization, $employee);
        $policy = SalesPolicy::resolveFor($organization);
        $schedule = $this->visitForEmployee($organization, $employee, $visit);

        $validated = $this->validateAttendanceRequest($request, (bool) $policy->visit_photo_required);
        $today = now()->toDateString();

        if ($today < $schedule->start_date->format('Y-m-d') || $today > $schedule->end_date->format('Y-m-d')) {
            return response()->json([
                'success' => false,
                'message' => __('sales.api.wrong_visit_date', [
                    'start' => $schedule->start_date->format('Y-m-d'),
                    'end' => $schedule->end_date->format('Y-m-d'),
                ]),
            ], 422);
        }

        if ($policy->prevent_visit_after_work_end && now()->format('H:i:s') > (string) $policy->tracking_work_end) {
            return response()->json(['success' => false, 'message' => __('sales.api.after_hours')], 422);
        }

        if ($policy->require_daily_attendance_before_visit && Schema::hasTable('hris_attendances')) {
            $hasDailyAttendance = DB::table('hris_attendances')
                ->where('organization_id', $organization->id)
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', $today)
                ->exists();

            if (!$hasDailyAttendance) {
                return response()->json(['success' => false, 'message' => __('sales.api.daily_attendance_required')], 403);
            }
        }

        $customer = $schedule->customer;
        $geo = $this->geofenceResult($customer, (float) $validated['latitude'], (float) $validated['longitude']);

        if ($policy->visit_geofence_required && !$policy->allow_outside_geofence && $geo['inside'] === false) {
            return response()->json([
                'success' => false,
                'message' => __('sales.api.outside_geofence', ['distance' => round($geo['distance'] ?? 0)]),
                'data' => $geo,
            ], 422);
        }

        $existing = SalesVisitAttendance::query()
            ->where('organization_id', $organization->id)
            ->where('visit_schedule_id', $schedule->id)
            ->whereDate('visit_date', $today)
            ->first();

        if ($existing?->clock_in) {
            return response()->json(['success' => false, 'message' => __('sales.api.already_clocked_in')], 422);
        }

        $photoPath = $request->file('photo')?->store('sales/visit-attendance/'.$organization->id, 'public');

        $attendance = SalesVisitAttendance::updateOrCreate(
            [
                'visit_schedule_id' => $schedule->id,
                'visit_date' => $today,
            ],
            [
                'organization_id' => $organization->id,
                'employee_id' => $employee->id,
                'customer_id' => $schedule->customer_id,
                'clock_in' => now(),
                'clock_in_latitude' => $validated['latitude'],
                'clock_in_longitude' => $validated['longitude'],
                'clock_in_accuracy_meters' => $validated['accuracy_meters'] ?? null,
                'clock_in_distance_meters' => $geo['distance'],
                'clock_in_within_geofence' => $geo['inside'],
                'photo_in' => $photoPath,
                'status' => 'open',
            ]
        );

        if ($schedule->status === 'scheduled') {
            $schedule->update(['status' => 'in_progress']);
        }

        return response()->json([
            'success' => true,
            'message' => __('sales.api.clock_in_success'),
            'data' => [
                'attendance_id' => $attendance->id,
                'clock_in' => $attendance->clock_in?->toIso8601String(),
                'geofence' => $geo,
            ],
        ]);
    }

    public function clockOut(Request $request, Organization $organization, int $visit): JsonResponse
    {
        $employee = $this->employeeFor($request, $organization);
        $this->salesProfile($organization, $employee);
        $policy = SalesPolicy::resolveFor($organization);
        $schedule = $this->visitForEmployee($organization, $employee, $visit);
        $validated = $this->validateAttendanceRequest($request, (bool) $policy->visit_photo_required);

        $attendance = SalesVisitAttendance::query()
            ->where('organization_id', $organization->id)
            ->where('visit_schedule_id', $schedule->id)
            ->where('employee_id', $employee->id)
            ->whereNotNull('clock_in')
            ->latest('visit_date')
            ->first();

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => __('sales.api.clock_in_first')], 422);
        }

        if ($attendance->clock_out) {
            return response()->json(['success' => false, 'message' => __('sales.api.visit_already_completed')], 422);
        }

        $geo = $this->geofenceResult($schedule->customer, (float) $validated['latitude'], (float) $validated['longitude']);
        if ($policy->visit_geofence_required && !$policy->allow_outside_geofence && $geo['inside'] === false) {
            return response()->json(['success' => false, 'message' => __('sales.api.outside_geofence', ['distance' => round($geo['distance'] ?? 0)]), 'data' => $geo], 422);
        }

        $photoPath = $request->file('photo')?->store('sales/visit-attendance/'.$organization->id, 'public');

        $attendance->update([
            'clock_out' => now(),
            'clock_out_latitude' => $validated['latitude'],
            'clock_out_longitude' => $validated['longitude'],
            'clock_out_accuracy_meters' => $validated['accuracy_meters'] ?? null,
            'clock_out_distance_meters' => $geo['distance'],
            'clock_out_within_geofence' => $geo['inside'],
            'photo_out' => $photoPath,
            'status' => 'completed',
            'notes' => $validated['notes'] ?? $attendance->notes,
        ]);

        $schedule->update(['status' => 'completed']);

        return response()->json([
            'success' => true,
            'message' => __('sales.api.clock_out_success'),
            'data' => [
                'clock_out' => $attendance->clock_out?->toIso8601String(),
                'geofence' => $geo,
            ],
        ]);
    }

    public function track(Request $request, Organization $organization): JsonResponse
    {
        $employee = $this->employeeFor($request, $organization);
        $this->salesProfile($organization, $employee);
        $policy = SalesPolicy::resolveFor($organization);

        if (!$policy->tracking_enabled) {
            return response()->json(['success' => false, 'message' => __('sales.api.tracking_disabled')], 403);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['nullable', 'numeric', 'min:0'],
            'speed_mps' => ['nullable', 'numeric', 'min:0'],
            'heading_degrees' => ['nullable', 'numeric', 'between:0,360'],
            'battery_level' => ['nullable', 'integer', 'between:0,100'],
            'is_moving' => ['nullable', 'boolean'],
            'visit_schedule_id' => [
                'nullable', 'integer',
                Rule::exists('sales_visit_schedules', 'id')->where('organization_id', $organization->id),
            ],
            'recorded_at' => ['nullable', 'date'],
        ]);

        if (
            isset($validated['accuracy_meters'])
            && (float) $validated['accuracy_meters'] > (int) $policy->tracking_accuracy_limit_meters
        ) {
            return response()->json([
                'success' => false,
                'message' => __('sales.api.gps_accuracy_low', ['limit' => $policy->tracking_accuracy_limit_meters]),
            ], 422);
        }

        SalesLocationTrack::create([
            'organization_id' => $organization->id,
            'employee_id' => $employee->id,
            'visit_schedule_id' => $validated['visit_schedule_id'] ?? null,
            'recorded_at' => $validated['recorded_at'] ?? now(),
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'accuracy_meters' => $validated['accuracy_meters'] ?? null,
            'speed_mps' => $validated['speed_mps'] ?? null,
            'heading_degrees' => $validated['heading_degrees'] ?? null,
            'battery_level' => $validated['battery_level'] ?? null,
            'is_moving' => $validated['is_moving'] ?? null,
            'source' => 'mobile',
        ]);

        return response()->json([
            'success' => true,
            'message' => __('sales.api.tracking_saved'),
            'data' => [
                'next_policy' => $this->trackingPolicyService->resolve($organization),
            ],
        ]);
    }

    public function uploadReceipt(Request $request, Organization $organization, int $visit): JsonResponse
    {
        $employee = $this->employeeFor($request, $organization);
        $this->salesProfile($organization, $employee);
        $schedule = $this->visitForEmployee($organization, $employee, $visit);

        $validated = $request->validate([
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $file = $validated['receipt'];
        $path = $file->store('sales/receipts/'.$organization->id, 'public');

        $receipt = SalesReceipt::create([
            'organization_id' => $organization->id,
            'visit_schedule_id' => $schedule->id,
            'employee_id' => $employee->id,
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

        return response()->json([
            'success' => true,
            'message' => __('sales.api.receipt_uploaded'),
            'data' => ['id' => $receipt->id, 'ulid' => $receipt->ulid],
        ]);
    }

    private function employeeFor(Request $request, Organization $organization): Employee
    {
        $employee = Employee::query()
            ->where('organization_id', $organization->id)
            ->where('user_id', $request->user()->id)
            ->with('user')
            ->first();

        abort_unless($employee, 403, __('sales.api.employee_not_linked'));
        return $employee;
    }

    private function salesProfile(Organization $organization, Employee $employee): SalesEmployeeProfile
    {
        $profile = SalesEmployeeProfile::query()
            ->where('organization_id', $organization->id)
            ->where('employee_id', $employee->id)
            ->where('is_active', true)
            ->first();

        abort_unless($profile, 403, __('sales.api.not_sales_employee'));
        return $profile;
    }

    private function visitForEmployee(Organization $organization, Employee $employee, int $visit): SalesVisitSchedule
    {
        return SalesVisitSchedule::query()
            ->where('organization_id', $organization->id)
            ->where('employee_id', $employee->id)
            ->with('customer')
            ->findOrFail($visit);
    }

    private function validateAttendanceRequest(Request $request, bool $photoRequired): array
    {
        return $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['nullable', 'numeric', 'min:0'],
            'photo' => [$photoRequired ? 'required' : 'nullable', 'image', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function geofenceResult($customer, float $latitude, float $longitude): array
    {
        if (!$customer?->geofence_enabled || $customer->latitude === null || $customer->longitude === null) {
            return ['inside' => null, 'distance' => null, 'radius_meters' => $customer?->geofence_radius_meters];
        }

        $distance = $this->geofenceService->distanceMeters(
            $latitude,
            $longitude,
            (float) $customer->latitude,
            (float) $customer->longitude
        );

        return [
            'inside' => $distance <= (int) $customer->geofence_radius_meters,
            'distance' => round($distance, 2),
            'radius_meters' => (int) $customer->geofence_radius_meters,
        ];
    }

    private function visitPolicy(SalesPolicy $policy): array
    {
        return [
            'visit_geofence_required' => (bool) $policy->visit_geofence_required,
            'allow_outside_geofence' => (bool) $policy->allow_outside_geofence,
            'visit_photo_required' => (bool) $policy->visit_photo_required,
            'receipt_upload_required' => (bool) $policy->receipt_upload_required,
            'prevent_visit_after_work_end' => (bool) $policy->prevent_visit_after_work_end,
            'require_daily_attendance_before_visit' => (bool) $policy->require_daily_attendance_before_visit,
        ];
    }

    private function employeeName(Employee $employee): string
    {
        return $employee->name ?? $employee->user?->name ?? 'Employee #'.$employee->id;
    }
}
