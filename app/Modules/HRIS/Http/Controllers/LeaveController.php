<?php

namespace App\Modules\HRIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Organization;
use App\Modules\HRIS\Models\HrisEmployeeProfile;
use App\Modules\HRIS\Models\HrisLeaveRequest;
use App\Modules\HRIS\Services\LeaveService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveController extends Controller
{
    public function __construct(
        private readonly LeaveService $leaveService
    ) {}


    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request,
        Organization $organization
    ): View {
        $query =
            HrisLeaveRequest::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->with([
                'employee.user',
                'employee.branch',
                'employee.department',
                'employee.position',
                'approver',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('search')
        ) {
            $search =
                trim(
                    $request
                        ->string('search')
                        ->toString()
                );

            $query->whereHas(
                'employee',
                function (
                    Builder $employeeQuery
                ) use (
                    $search
                ) {
                    $employeeQuery->where(
                        function (
                            Builder $inner
                        ) use (
                            $search
                        ) {
                            $inner
                                ->where(
                                    'employee_number',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'name',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhereHas(
                                    'user',
                                    function (
                                        Builder $userQuery
                                    ) use (
                                        $search
                                    ) {
                                        $userQuery
                                            ->where(
                                                'name',
                                                'like',
                                                '%' . $search . '%'
                                            )
                                            ->orWhere(
                                                'email',
                                                'like',
                                                '%' . $search . '%'
                                            );
                                    }
                                );
                        }
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('status')
        ) {
            $query->where(
                'status',
                $request
                    ->string('status')
                    ->toString()
            );
        }

        if (
            $request->filled('type')
        ) {
            $query->where(
                'type',
                $request
                    ->string('type')
                    ->toString()
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
            $request->filled('from')
        ) {
            $query->whereDate(
                'end_date',
                '>=',
                $request->input('from')
            );
        }

        if (
            $request->filled('to')
        ) {
            $query->whereDate(
                'start_date',
                '<=',
                $request->input('to')
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $leaves =
            $query
            ->latest('id')
            ->paginate(25)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Employees
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Profiles / Remaining Quota
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $base =
            HrisLeaveRequest::query()
            ->where(
                'organization_id',
                $organization->id
            );

        $pendingCount =
            (clone $base)
            ->where(
                'status',
                'pending'
            )
            ->count();

        $approvedCount =
            (clone $base)
            ->where(
                'status',
                'approved'
            )
            ->count();

        $rejectedCount =
            (clone $base)
            ->where(
                'status',
                'rejected'
            )
            ->count();

        $approvedThisMonth =
            (clone $base)
            ->where(
                'status',
                'approved'
            )
            ->whereYear(
                'start_date',
                now()->year
            )
            ->whereMonth(
                'start_date',
                now()->month
            )
            ->sum(
                'duration_days'
            );

        return view(
            'modules.hris.leave.index',
            compact(
                'organization',

                'leaves',
                'employees',
                'profiles',

                'pendingCount',
                'approvedCount',
                'rejectedCount',
                'approvedThisMonth'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        Organization $organization
    ): RedirectResponse {
        $validated =
            $this->validateLeave(
                $request,
                $organization,
                null,
                true
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

        $endDate =
            $validated['end_date']
            ??
            $validated['start_date'];

        $this
            ->leaveService
            ->assertNoOverlap(
                $organization,
                $employee,
                $validated['start_date'],
                $endDate
            );

        $attachmentPath =
            null;

        $attachmentName =
            null;

        if (
            $request->hasFile(
                'attachment'
            )
        ) {
            $attachment =
                $request->file(
                    'attachment'
                );

            $attachmentPath =
                $attachment->store(
                    'hris/leaves/'
                        . $organization->id,
                    'public'
                );

            $attachmentName =
                $attachment
                ->getClientOriginalName();
        }

        DB::transaction(
            function () use (
                $validated,
                $organization,
                $request,
                $endDate,
                $attachmentPath,
                $attachmentName
            ) {
                HrisLeaveRequest::query()
                    ->create([
                        'organization_id' =>
                        $organization->id,

                        'employee_id' =>
                        $validated['employee_id'],

                        'start_date' =>
                        $validated['start_date'],

                        'end_date' =>
                        $endDate,

                        'start_time' =>
                        $validated['start_time']
                            ?? null,

                        'end_time' =>
                        $validated['end_time']
                            ?? null,

                        'duration_days' =>
                        $this
                            ->leaveService
                            ->durationDays(
                                $validated['start_date'],
                                $endDate
                            ),

                        'type' =>
                        $validated['type'],

                        'detail_type' =>
                        $validated['detail_type']
                            ?? null,

                        'reason' =>
                        $validated['reason'],

                        'emergency_name' =>
                        $validated['emergency_name']
                            ?? null,

                        'emergency_address' =>
                        $validated['emergency_address']
                            ?? null,

                        'emergency_phone' =>
                        $validated['emergency_phone']
                            ?? null,

                        'attachment_path' =>
                        $attachmentPath,

                        'attachment_original_name' =>
                        $attachmentName,

                        'status' =>
                        'pending',

                        'source' =>
                        'manual',

                        'created_by' =>
                        $request->user()?->id,

                        'updated_by' =>
                        $request->user()?->id,
                    ]);
            }
        );

        return back()
            ->with(
                'success',
                'Leave request berhasil dibuat dan menunggu approval.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Organization $organization,
        HrisLeaveRequest $leave
    ): RedirectResponse {
        $this->guardOrganization(
            $organization,
            $leave
        );

        /*
        |--------------------------------------------------------------------------
        | Approved Locked
        |--------------------------------------------------------------------------
        */

        if (
            $leave->status
            === 'approved'
        ) {
            return back()
                ->withErrors([
                    'leave' =>
                    'Leave yang sudah approved tidak dapat diedit. Reject terlebih dahulu jika perlu melakukan koreksi.',
                ]);
        }

        $validated =
            $this->validateLeave(
                $request,
                $organization,
                $leave,
                false
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

        $endDate =
            $validated['end_date']
            ??
            $validated['start_date'];

        $this
            ->leaveService
            ->assertNoOverlap(
                $organization,
                $employee,
                $validated['start_date'],
                $endDate,
                $leave
            );

        $newAttachmentPath =
            null;

        $newAttachmentName =
            null;

        if (
            $request->hasFile(
                'attachment'
            )
        ) {
            $attachment =
                $request->file(
                    'attachment'
                );

            $newAttachmentPath =
                $attachment->store(
                    'hris/leaves/'
                        . $organization->id,
                    'public'
                );

            $newAttachmentName =
                $attachment
                ->getClientOriginalName();
        }

        DB::transaction(
            function () use (
                $leave,
                $validated,
                $request,
                $endDate,
                $newAttachmentPath,
                $newAttachmentName
            ) {
                $oldAttachment =
                    $leave->attachment_path;

                $leave->fill([
                    'employee_id' =>
                    $validated['employee_id'],

                    'start_date' =>
                    $validated['start_date'],

                    'end_date' =>
                    $endDate,

                    'start_time' =>
                    $validated['start_time']
                        ?? null,

                    'end_time' =>
                    $validated['end_time']
                        ?? null,

                    'duration_days' =>
                    $this
                        ->leaveService
                        ->durationDays(
                            $validated['start_date'],
                            $endDate
                        ),

                    'type' =>
                    $validated['type'],

                    'detail_type' =>
                    $validated['detail_type']
                        ?? null,

                    'reason' =>
                    $validated['reason'],

                    'emergency_name' =>
                    $validated['emergency_name']
                        ?? null,

                    'emergency_address' =>
                    $validated['emergency_address']
                        ?? null,

                    'emergency_phone' =>
                    $validated['emergency_phone']
                        ?? null,

                    'status' =>
                    'pending',

                    'approved_by' =>
                    null,

                    'approved_at' =>
                    null,

                    'rejected_at' =>
                    null,

                    'approval_notes' =>
                    null,

                    'updated_by' =>
                    $request->user()?->id,
                ]);

                if (
                    $newAttachmentPath
                ) {
                    $leave->attachment_path =
                        $newAttachmentPath;

                    $leave->attachment_original_name =
                        $newAttachmentName;
                }

                $leave->save();

                if (
                    $newAttachmentPath
                    &&
                    $oldAttachment
                ) {
                    Storage::disk('public')
                        ->delete(
                            $oldAttachment
                        );
                }
            }
        );

        return back()
            ->with(
                'success',
                'Leave request berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        Request $request,
        Organization $organization,
        HrisLeaveRequest $leave
    ): RedirectResponse {
        $this->guardOrganization(
            $organization,
            $leave
        );

        $validated =
            $request->validate([
                'action' => [
                    'required',

                    Rule::in([
                        'approve',
                        'reject',
                    ]),
                ],

                'approval_notes' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],
            ]);

        if (
            $validated['action']
            === 'approve'
        ) {
            $this
                ->leaveService
                ->approve(
                    $leave,
                    $request->user()->id,
                    $validated['approval_notes']
                        ?? null
                );

            return back()
                ->with(
                    'success',
                    'Leave request berhasil disetujui.'
                );
        }

        $this
            ->leaveService
            ->reject(
                $leave,
                $request->user()->id,
                $validated['approval_notes']
                    ?? null
            );

        return back()
            ->with(
                'success',
                'Leave request berhasil ditolak.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Organization $organization,
        HrisLeaveRequest $leave
    ): RedirectResponse {
        $this->guardOrganization(
            $organization,
            $leave
        );

        if (
            $leave->status
            === 'approved'
        ) {
            throw ValidationException::withMessages([
                'leave' =>
                'Leave yang sudah approved tidak dapat dihapus.',
            ]);
        }

        $leave->delete();

        return back()
            ->with(
                'success',
                'Leave request berhasil dihapus.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Attachment
    |--------------------------------------------------------------------------
    */

    public function attachment(
        Organization $organization,
        HrisLeaveRequest $leave
    ): StreamedResponse {
        $this->guardOrganization(
            $organization,
            $leave
        );

        if (
            blank(
                $leave->attachment_path
            )
            ||
            !Storage::disk('public')
                ->exists(
                    $leave->attachment_path
                )
        ) {
            abort(404);
        }

        return Storage::disk('public')
            ->download(
                $leave->attachment_path,
                $leave->attachment_original_name
                    ?? basename(
                        $leave->attachment_path
                    )
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validateLeave(
        Request $request,
        Organization $organization,
        ?HrisLeaveRequest $leave = null,
        bool $requireAttachment = false
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

            'type' => [
                'required',

                Rule::in([
                    'cuti',
                    'izin',
                    'sakit',
                ]),
            ],

            'detail_type' => [
                'nullable',
                'string',
                'max:100',
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

            'start_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'end_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'reason' => [
                'required',
                'string',
                'max:5000',
            ],

            'emergency_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'emergency_address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'emergency_phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'attachment' => [
                $requireAttachment
                    ? 'required'
                    : 'nullable',

                'file',

                'mimes:jpg,jpeg,png,pdf',

                'max:2048',
            ],
        ]);
    }


    private function guardOrganization(
        Organization $organization,
        HrisLeaveRequest $leave
    ): void {
        if (
            (int) $leave->organization_id
            !==
            (int) $organization->id
        ) {
            abort(404);
        }
    }
}
