<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\RegistrationInvite;
use App\Models\RegistrationRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrganizationProvisioningService
{
    public function __construct(
        protected OrganizationSlugService $slugService
    ) {}

    public function provisionFromInvite(
        int $inviteId,
        array $data
    ): array {
        return DB::transaction(function () use (
            $inviteId,
            $data
        ) {
            $invite = RegistrationInvite::query()
                ->lockForUpdate()
                ->findOrFail($inviteId);

            if (!$invite->isUsable()) {
                throw ValidationException::withMessages([
                    'invite' => 'Registration link sudah tidak dapat digunakan.',
                ]);
            }

            $payload = [
                'organization_name' =>
                $data['organization_name'],

                'organization_type' =>
                $data['organization_type'],

                'owner_name' =>
                $data['owner_name'],

                'email' =>
                $data['email'],

                'phone' =>
                $data['phone'] ?? null,

                'password' =>
                $data['password'],

                'module_ids' =>
                $invite->module_ids ?? [],

                'pricing_snapshot' =>
                $invite->pricing_snapshot ?? [],
            ];

            [$organization, $user] =
                $this->provision(
                    $payload,
                    activateModules: true
                );

            $invite->update([
                'used_at' => now(),

                'used_organization_id' =>
                $organization->id,

                'used_by_user_id' =>
                $user->id,
            ]);

            return [
                $organization,
                $user,
            ];
        });
    }

    public function provisionSelf(
        array $data,
        bool $activateModules = false
    ): array {
        return DB::transaction(
            fn() => $this->provision(
                $data,
                $activateModules
            )
        );
    }

    public function provisionFromRequest(
        int $requestId
    ): array {
        return DB::transaction(function () use (
            $requestId
        ) {
            $registration = RegistrationRequest::query()
                ->lockForUpdate()
                ->findOrFail($requestId);

            if ($registration->status !== 'submitted') {
                throw ValidationException::withMessages([
                    'registration' =>
                    'Registration request sudah diproses.',
                ]);
            }

            [$organization, $user] =
                $this->provision(
                    [
                        'organization_name' =>
                        $registration->organization_name,

                        'organization_type' =>
                        $registration->organization_type,

                        'owner_name' =>
                        $registration->owner_name,

                        'email' =>
                        $registration->email,

                        'phone' =>
                        $registration->phone,

                        'password_hash' =>
                        $registration->password_hash,

                        'module_ids' =>
                        $registration->module_ids ?? [],

                        'pricing_snapshot' =>
                        $registration->pricing_snapshot ?? [],
                    ],
                    activateModules: false
                );

            $registration->update([
                'status' => 'completed',

                'reviewed_at' =>
                $registration->reviewed_at
                    ?? now(),

                'completed_at' => now(),

                'created_organization_id' =>
                $organization->id,

                'created_user_id' =>
                $user->id,
            ]);

            return [
                $organization,
                $user,
            ];
        });
    }

    protected function provision(
        array $data,
        bool $activateModules
    ): array {
        if (
            User::query()
            ->where('email', $data['email'])
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'email' =>
                'Email tersebut sudah terdaftar di Evognito One.',
            ]);
        }

        $organization = Organization::create([
            'ulid' => (string) Str::ulid(),

            'type' =>
            $data['organization_type'],

            'name' =>
            $data['organization_name'],

            'slug' =>
            $this->slugService->generate(
                $data['organization_name']
            ),

            'email' =>
            $data['email'],

            'phone' =>
            $data['phone'] ?? null,

            'timezone' =>
            'Asia/Jakarta',

            'locale' =>
            'id',

            'status' =>
            'active',
        ]);

        $password = $data['password_hash']
            ?? Hash::make($data['password']);

        $user = User::create([
            'ulid' => (string) Str::ulid(),

            'name' =>
            $data['owner_name'],

            'email' =>
            $data['email'],

            'phone' =>
            $data['phone'] ?? null,

            'password' =>
            $password,

            'status' =>
            'active',

            'is_super_admin' =>
            false,
        ]);

        DB::table('organization_users')
            ->insert([
                'organization_id' =>
                $organization->id,

                'user_id' =>
                $user->id,

                'status' =>
                'active',

                'is_default' =>
                true,

                'joined_at' =>
                now(),

                'created_at' =>
                now(),

                'updated_at' =>
                now(),
            ]);

        $ownerRole = Role::firstOrCreate(
            [
                'organization_id' =>
                $organization->id,

                'scope' =>
                'organization',

                'code' =>
                'organization_owner',
            ],
            [
                'ulid' =>
                (string) Str::ulid(),

                'name' =>
                'Organization Owner',

                'description' =>
                'Pemilik organization dengan akses administrasi organization.',

                'is_system' =>
                true,
            ]
        );

        DB::table('user_roles')
            ->insert([
                'user_id' =>
                $user->id,

                'role_id' =>
                $ownerRole->id,

                'organization_id' =>
                $organization->id,

                'created_at' =>
                now(),

                'updated_at' =>
                now(),
            ]);

        $moduleIds = collect(
            $data['module_ids'] ?? []
        )
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $pricingSnapshot =
            $data['pricing_snapshot'] ?? [];

        foreach ($moduleIds as $moduleId) {
            DB::table('organization_modules')
                ->updateOrInsert(
                    [
                        'organization_id' =>
                        $organization->id,

                        'module_id' =>
                        $moduleId,
                    ],
                    [
                        'enabled' =>
                        $activateModules,

                        'status' =>
                        $activateModules
                            ? 'active'
                            : 'pending',

                        'started_at' =>
                        $activateModules
                            ? now()
                            : null,

                        'expires_at' =>
                        null,

                        'settings' =>
                        null,

                        'created_at' =>
                        now(),

                        'updated_at' =>
                        now(),
                    ]
                );

            $pricing =
                $pricingSnapshot[(string) $moduleId]
                ?? $pricingSnapshot[$moduleId]
                ?? null;

            if (!$pricing) {
                continue;
            }

            DB::table(
                'organization_module_pricing'
            )->insert([
                'organization_id' =>
                $organization->id,

                'module_id' =>
                $moduleId,

                'pricing_type' =>
                $pricing['pricing_type']
                    ?? 'fixed',

                'fixed_price' =>
                $pricing['fixed_price']
                    ?? 0,

                'price_per_user' =>
                $pricing['price_per_user']
                    ?? 0,

                'included_users' =>
                $pricing['included_users']
                    ?? 0,

                'minimum_users' =>
                $pricing['minimum_users']
                    ?? null,

                'maximum_users' =>
                $pricing['maximum_users']
                    ?? null,

                'billing_user_scope' =>
                $pricing['billing_user_scope']
                    ?? 'licensed_users',

                'effective_from' =>
                now()->toDateString(),

                'effective_until' =>
                null,

                'settings' =>
                null,

                'created_at' =>
                now(),

                'updated_at' =>
                now(),
            ]);
        }

        return [
            $organization,
            $user,
        ];
    }
}
