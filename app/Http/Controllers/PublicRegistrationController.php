<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\PlatformSetting;
use App\Models\RegistrationInvite;
use App\Models\RegistrationRequest;
use App\Services\OrganizationProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicRegistrationController extends Controller
{
    public function __construct(
        protected OrganizationProvisioningService $provisioning
    ) {}

    public function showInvite(
        string $token
    ): View {
        $invite =
            $this->resolveInvite(
                $token
            );

        if (!$invite) {
            return view(
                'registration.invalid',
                [
                    'message' =>
                    'Registration link tidak ditemukan.',
                ]
            );
        }

        if (!$invite->isUsable()) {
            return view(
                'registration.invalid',
                [
                    'message' =>
                    $this->invalidInviteMessage(
                        $invite
                    ),
                ]
            );
        }

        $modules = Module::query()
            ->whereIn(
                'id',
                $invite->module_ids ?? []
            )
            ->orderBy(
                'sort_order'
            )
            ->get();

        return view(
            'registration.invite',
            compact(
                'invite',
                'token',
                'modules'
            )
        );
    }

    public function acceptInvite(
        Request $request,
        string $token
    ): RedirectResponse {
        $invite =
            $this->resolveInvite(
                $token
            );

        if (
            !$invite
            || !$invite->isUsable()
        ) {
            return redirect()
                ->route(
                    'registration.invite.show',
                    $token
                );
        }

        $validated =
            $request->validate([
                'organization_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'organization_type' => [
                    'required',
                    Rule::in([
                        'business',
                        'education',
                        'nonprofit',
                        'government',
                        'other',
                    ]),
                ],

                'owner_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'owner_email' => [
                    'required',
                    'email',
                    'max:255',
                    'unique:users,email',
                ],

                'owner_phone' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ]);

        $locked =
            $invite->locked_fields
            ?? [];

        $organizationName =
            in_array(
                'organization_name',
                $locked,
                true
            )
            && $invite->organization_name
            ? $invite->organization_name
            : $validated['organization_name'];

        $organizationType =
            in_array(
                'organization_type',
                $locked,
                true
            )
            ? $invite->organization_type
            : $validated['organization_type'];

        $email =
            in_array(
                'contact_email',
                $locked,
                true
            )
            && $invite->contact_email
            ? $invite->contact_email
            : $validated['owner_email'];

        /*
        |--------------------------------------------------------------------------
        | Provision Organization
        |--------------------------------------------------------------------------
        */

        [
            $organization,
            $user,
        ] = $this->provisioning
            ->provisionFromInvite(
                $invite->id,
                [
                    'organization_name' =>
                    $organizationName,

                    'organization_type' =>
                    $organizationType,

                    'owner_name' =>
                    $validated['owner_name'],

                    'email' =>
                    $email,

                    'phone' =>
                    $validated['owner_phone']
                        ?? $invite
                        ->contact_phone,

                    'password' =>
                    $validated['password'],
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Login Owner Automatically
        |--------------------------------------------------------------------------
        |
        | Kalau sebelumnya sedang login sebagai user lain
        | (misalnya Superadmin sedang melakukan testing),
        | sesi tersebut diganti menjadi Owner baru.
        |
        */

        Auth::logout();

        $request->session()
            ->invalidate();

        $request->session()
            ->regenerateToken();

        Auth::login(
            $user,
            true
        );

        $request->session()
            ->regenerate();

        return redirect()
            ->route(
                'workspace.dashboard',
                [
                    'organization' =>
                    $organization->slug,
                ]
            )
            ->with(
                'success',
                'Organization berhasil dibuat. Selamat datang di Evognito One.'
            );
    }

    public function showSelf(): View
    {
        $mode =
            PlatformSetting::getValue(
                'registration.mode',
                'invite_only'
            );

        if ($mode === 'invite_only') {
            return view(
                'registration.closed'
            );
        }

        return view(
            'registration.register',
            compact('mode')
        );
    }

    public function storeSelf(
        Request $request
    ): RedirectResponse {
        $mode =
            PlatformSetting::getValue(
                'registration.mode',
                'invite_only'
            );

        abort_if(
            $mode === 'invite_only',
            403
        );

        $validated =
            $request->validate([
                'organization_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'organization_type' => [
                    'required',
                    Rule::in([
                        'business',
                        'education',
                        'nonprofit',
                        'government',
                        'other',
                    ]),
                ],

                'owner_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',
                    'unique:users,email',
                ],

                'phone' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ]);

        /*
        |--------------------------------------------------------------------------
        | Approval Registration
        |--------------------------------------------------------------------------
        */

        if ($mode === 'approval') {
            RegistrationRequest::create([
                'ulid' =>
                (string) Str::ulid(),

                'source' =>
                'self',

                'organization_name' =>
                $validated['organization_name'],

                'organization_type' =>
                $validated['organization_type'],

                'owner_name' =>
                $validated['owner_name'],

                'email' =>
                $validated['email'],

                'phone' =>
                $validated['phone']
                    ?? null,

                'password_hash' =>
                Hash::make(
                    $validated['password']
                ),

                'status' =>
                'submitted',

                'submitted_at' =>
                now(),
            ]);

            return redirect()
                ->route(
                    'registration.self'
                )
                ->with(
                    'success',
                    'Pendaftaran berhasil dikirim dan menunggu review Evognito.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Open Registration
        |--------------------------------------------------------------------------
        */

        [
            $organization,
            $user,
        ] = $this->provisioning
            ->provisionSelf(
                [
                    'organization_name' =>
                    $validated['organization_name'],

                    'organization_type' =>
                    $validated['organization_type'],

                    'owner_name' =>
                    $validated['owner_name'],

                    'email' =>
                    $validated['email'],

                    'phone' =>
                    $validated['phone']
                        ?? null,

                    'password' =>
                    $validated['password'],

                    'module_ids' =>
                    [],

                    'pricing_snapshot' =>
                    [],
                ]
            );

        Auth::logout();

        $request->session()
            ->invalidate();

        $request->session()
            ->regenerateToken();

        Auth::login(
            $user,
            true
        );

        $request->session()
            ->regenerate();

        return redirect()
            ->route(
                'workspace.dashboard',
                [
                    'organization' =>
                    $organization->slug,
                ]
            )
            ->with(
                'success',
                'Organization berhasil dibuat. Selamat datang di Evognito One.'
            );
    }

    protected function resolveInvite(
        string $token
    ): ?RegistrationInvite {
        return RegistrationInvite::query()
            ->where(
                'token_hash',
                hash(
                    'sha256',
                    $token
                )
            )
            ->first();
    }

    protected function invalidInviteMessage(
        RegistrationInvite $invite
    ): string {
        if ($invite->isUsed()) {
            return 'Registration link ini sudah digunakan.';
        }

        if ($invite->isRevoked()) {
            return 'Registration link ini sudah direvoke.';
        }

        if ($invite->isExpired()) {
            return 'Registration link ini sudah kedaluwarsa.';
        }

        return 'Registration link tidak dapat digunakan.';
    }
}
