<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\RegistrationInvite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationInviteController extends Controller
{
    public function index(): View
    {
        $invites = RegistrationInvite::query()
            ->with([
                'creator',
                'usedOrganization',
            ])
            ->latest()
            ->paginate(15);

        return view(
            'superadmin.registration-invites.index',
            compact('invites')
        );
    }

    public function create(): View
    {
        $modules = Module::query()
            ->where(
                'status',
                'active'
            )
            ->orderBy(
                'sort_order'
            )
            ->get();

        $defaultExpiryHours =
            (int) \App\Models\PlatformSetting::getValue(
                'registration.default_invite_expiry_hours',
                72
            );

        return view(
            'superadmin.registration-invites.create',
            compact(
                'modules',
                'defaultExpiryHours'
            )
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'organization_name' => [
                'nullable',
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

            'contact_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'contact_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'contact_phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'expires_at' => [
                'required',
                'date',
                'after:now',
            ],

            'locked_fields' => [
                'nullable',
                'array',
            ],

            'locked_fields.*' => [
                Rule::in([
                    'organization_name',
                    'organization_type',
                    'contact_email',
                ]),
            ],

            'module_ids' => [
                'nullable',
                'array',
            ],

            'module_ids.*' => [
                'integer',
                'exists:modules,id',
            ],

            'pricing' => [
                'nullable',
                'array',
            ],
        ]);

        $moduleIds = collect(
            $validated['module_ids'] ?? []
        )
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $pricingSnapshot = [];

        foreach ($moduleIds as $moduleId) {
            $row = $request->input(
                "pricing.{$moduleId}",
                []
            );

            $pricingType =
                $row['pricing_type']
                ?? 'fixed';

            if (
                !in_array(
                    $pricingType,
                    [
                        'fixed',
                        'per_user',
                        'hybrid',
                    ],
                    true
                )
            ) {
                $pricingType = 'fixed';
            }

            $pricingSnapshot[(string) $moduleId] = [
                'pricing_type' =>
                $pricingType,

                'fixed_price' =>
                max(
                    0,
                    (float) (
                        $row['fixed_price']
                        ?? 0
                    )
                ),

                'price_per_user' =>
                max(
                    0,
                    (float) (
                        $row['price_per_user']
                        ?? 0
                    )
                ),

                'included_users' =>
                max(
                    0,
                    (int) (
                        $row['included_users']
                        ?? 0
                    )
                ),

                'minimum_users' =>
                !empty($row['minimum_users'])
                    ? max(
                        0,
                        (int) $row['minimum_users']
                    )
                    : null,

                'maximum_users' =>
                !empty($row['maximum_users'])
                    ? max(
                        0,
                        (int) $row['maximum_users']
                    )
                    : null,

                'billing_user_scope' =>
                $row['billing_user_scope']
                    ?? 'licensed_users',
            ];
        }

        $token = bin2hex(
            random_bytes(32)
        );

        $invite =
            RegistrationInvite::create([
                'ulid' =>
                (string) Str::ulid(),

                'token_hash' =>
                hash(
                    'sha256',
                    $token
                ),

                'token_encrypted' =>
                Crypt::encryptString(
                    $token
                ),

                'token_fingerprint' =>
                substr(
                    $token,
                    -12
                ),

                'organization_name' =>
                $validated['organization_name']
                    ?? null,

                'organization_type' =>
                $validated['organization_type'],

                'contact_name' =>
                $validated['contact_name']
                    ?? null,

                'contact_email' =>
                $validated['contact_email']
                    ?? null,

                'contact_phone' =>
                $validated['contact_phone']
                    ?? null,

                'locked_fields' =>
                $validated['locked_fields']
                    ?? [],

                'module_ids' =>
                $moduleIds,

                'pricing_snapshot' =>
                $pricingSnapshot,

                'expires_at' =>
                $validated['expires_at'],

                'created_by' =>
                $request->user()->id,
            ]);

        return redirect()
            ->route(
                'superadmin.registration-invites.index'
            )
            ->with(
                'success',
                'Registration link berhasil dibuat.'
            )
            ->with(
                'invite_url',
                $invite->registrationUrl()
            );
    }

    public function revoke(
        RegistrationInvite $invite
    ): RedirectResponse {
        if ($invite->used_at) {
            return back()->with(
                'error',
                'Invite yang sudah digunakan tidak dapat direvoke.'
            );
        }

        $invite->update([
            'revoked_at' => now(),
        ]);

        return back()->with(
            'success',
            'Registration link berhasil direvoke.'
        );
    }

    public function regenerate(
        RegistrationInvite $invite
    ): RedirectResponse {
        if ($invite->used_at) {
            return back()->with(
                'error',
                'Invite yang sudah digunakan tidak dapat diregenerate.'
            );
        }

        $token = bin2hex(
            random_bytes(32)
        );

        $invite->update([
            'token_hash' =>
            hash(
                'sha256',
                $token
            ),

            'token_encrypted' =>
            Crypt::encryptString(
                $token
            ),

            'token_fingerprint' =>
            substr(
                $token,
                -12
            ),

            'revoked_at' =>
            null,

            'expires_at' =>
            $invite->expires_at->isFuture()
                ? $invite->expires_at
                : now()->addDays(3),
        ]);

        return back()
            ->with(
                'success',
                'Registration link berhasil diregenerate.'
            )
            ->with(
                'invite_url',
                $invite->fresh()
                    ->registrationUrl()
            );
    }
}
