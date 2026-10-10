<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationRequest;
use App\Services\OrganizationProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationRequestController extends Controller
{
    public function __construct(
        protected OrganizationProvisioningService $provisioning
    ) {}

    public function index(): View
    {
        $registrations =
            RegistrationRequest::query()
            ->with([
                'reviewer',
                'organization',
            ])
            ->latest()
            ->paginate(15);

        return view(
            'superadmin.registration-requests.index',
            compact('registrations')
        );
    }

    public function approve(
        Request $request,
        RegistrationRequest $registration
    ): RedirectResponse {
        if (
            $registration->status
            !== 'submitted'
        ) {
            return back()->with(
                'error',
                'Registration request sudah diproses.'
            );
        }

        $registration->update([
            'reviewed_at' =>
            now(),

            'reviewed_by' =>
            $request->user()->id,
        ]);

        [$organization] =
            $this->provisioning
            ->provisionFromRequest(
                $registration->id
            );

        return back()->with(
            'success',
            'Registration ' . $organization->name . ' berhasil disetujui.'
        );
    }

    public function reject(
        Request $request,
        RegistrationRequest $registration
    ): RedirectResponse {
        if (
            $registration->status
            !== 'submitted'
        ) {
            return back()->with(
                'error',
                'Registration request sudah diproses.'
            );
        }

        $validated =
            $request->validate([
                'reason' => [
                    'required',
                    'string',
                    'max:1000',
                ],
            ]);

        $registration->update([
            'status' =>
            'rejected',

            'reviewed_at' =>
            now(),

            'reviewed_by' =>
            $request->user()->id,

            'rejection_reason' =>
            $validated['reason'],
        ]);

        return back()->with(
            'success',
            'Registration request ditolak.'
        );
    }
}
