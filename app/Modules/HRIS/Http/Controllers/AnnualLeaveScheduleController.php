<?php

namespace App\Modules\HRIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Organization;
use App\Modules\HRIS\Models\HrisEmployeeProfile;
use App\Modules\HRIS\Models\HrisLeaveSchedule;
use App\Modules\HRIS\Services\AnnualLeaveScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnualLeaveScheduleController extends Controller
{
    public function __construct(
        private readonly AnnualLeaveScheduleService $scheduleService
    ) {}


    public function index(
        Request $request,
        Organization $organization
    ): View {
        $query =
            HrisLeaveSchedule::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->with([
                'employee.user',
                'employee.branch',
                'employee.department',
                'employee.position',
                'creator',
                'canceller',
            ]);

        if (
            $request->filled(
                'status'
            )
        ) {
            $query->where(
                'status',
                $request->input(
                    'status'
                )
            );
        }

        if (
            $request->filled(
                'employee_id'
            )
        ) {
            $query->where(
                'employee_id',
                $request->integer(
                    'employee_id'
                )
            );
        }

        if (
            $request->filled(
                'from'
            )
        ) {
            $query->whereDate(
                'end_date',
                '>=',
                $request->input(
                    'from'
                )
            );
        }

        if (
            $request->filled(
                'to'
            )
        ) {
            $query->whereDate(
                'start_date',
                '<=',
                $request->input(
                    'to'
                )
            );
        }

        $schedules =
            $query
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $employees =
            Employee::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(
                'status',
                'active'
            )
            ->with([
                'user',
                'position',
            ])
            ->orderBy('name')
            ->get();

        $profiles =
            HrisEmployeeProfile::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->whereIn(
                'employee_id',
                $employees->pluck('id')
            )
            ->get()
            ->keyBy(
                'employee_id'
            );

        $base =
            HrisLeaveSchedule::query()
            ->where(
                'organization_id',
                $organization->id
            );

        $scheduledCount =
            (clone $base)
            ->where(
                'status',
                'scheduled'
            )
            ->count();

        $scheduledDays =
            (clone $base)
            ->where(
                'status',
                'scheduled'
            )
            ->sum(
                'duration_days'
            );

        $cancelledCount =
            (clone $base)
            ->where(
                'status',
                'cancelled'
            )
            ->count();

        return view(
            'modules.hris.calendar.annual-leave',
            compact(
                'organization',

                'schedules',
                'employees',
                'profiles',

                'scheduledCount',
                'scheduledDays',
                'cancelledCount'
            )
        );
    }


    public function store(
        Request $request,
        Organization $organization
    ): RedirectResponse {
        $validated =
            $this->validateSchedule(
                $request,
                $organization
            );

        $employee =
            Employee::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->findOrFail(
                $validated['employee_id']
            );

        $this
            ->scheduleService
            ->create(
                $organization,
                $employee,
                $validated,
                $request->user()?->id
            );

        return back()
            ->with(
                'success',
                'Annual Leave Schedule berhasil dibuat.'
            );
    }


    public function update(
        Request $request,
        Organization $organization,
        HrisLeaveSchedule $leaveSchedule
    ): RedirectResponse {
        $this->guardOrganization(
            $organization,
            $leaveSchedule
        );

        $validated =
            $this->validateSchedule(
                $request,
                $organization
            );

        $employee =
            Employee::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->findOrFail(
                $validated['employee_id']
            );

        $this
            ->scheduleService
            ->update(
                $organization,
                $leaveSchedule,
                $employee,
                $validated,
                $request->user()?->id
            );

        return back()
            ->with(
                'success',
                'Annual Leave Schedule berhasil diperbarui.'
            );
    }


    public function cancel(
        Request $request,
        Organization $organization,
        HrisLeaveSchedule $leaveSchedule
    ): RedirectResponse {
        $this->guardOrganization(
            $organization,
            $leaveSchedule
        );

        $validated =
            $request->validate([
                'cancellation_reason' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],
            ]);

        $this
            ->scheduleService
            ->cancel(
                $organization,
                $leaveSchedule,
                $request->user()?->id,
                $validated['cancellation_reason']
                    ?? null
            );

        return back()
            ->with(
                'success',
                'Annual Leave Schedule berhasil dibatalkan dan kuota dikembalikan.'
            );
    }


    private function validateSchedule(
        Request $request,
        Organization $organization
    ): array {
        return $request->validate([
            'employee_id' => [
                'required',
                'integer',

                Rule::exists(
                    'employees',
                    'id'
                )->where(
                    fn($query) =>
                    $query->where(
                        'organization_id',
                        $organization->id
                    )
                ),
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);
    }


    private function guardOrganization(
        Organization $organization,
        HrisLeaveSchedule $leaveSchedule
    ): void {
        if (
            (int) $leaveSchedule
                ->organization_id
            !==
            (int) $organization->id
        ) {
            abort(404);
        }
    }
}
