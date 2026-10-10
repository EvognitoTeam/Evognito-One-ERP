<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));
        $status = trim((string) $request->get('status'));
        $type = trim((string) $request->get('type'));

        $users = User::query()
            ->withCount('organizations')

            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($subQuery) use ($search) {
                        $subQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                }
            )

            ->when(
                $status !== '',
                fn($query) =>
                $query->where('status', $status)
            )

            ->when(
                $type === 'superadmin',
                fn($query) =>
                $query->where('is_super_admin', true)
            )

            ->when(
                $type === 'organization',
                fn($query) =>
                $query->where('is_super_admin', false)
            )

            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'superadmin.users.index',
            compact(
                'users',
                'search',
                'status',
                'type'
            )
        );
    }

    public function create(): View
    {
        $organizations = Organization::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $roles = Role::query()
            ->whereNotNull('organization_id')
            ->orderBy('name')
            ->get();

        return view(
            'superadmin.users.create',
            compact(
                'organizations',
                'roles'
            )
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'username' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('users', 'username'),
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

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                    'suspended',
                ]),
            ],

            'organization_id' => [
                'nullable',
                'integer',
                'exists:organizations,id',
            ],

            'role_id' => [
                'nullable',
                'integer',
                'exists:roles,id',
            ],

            'is_default' => [
                'nullable',
                'boolean',
            ],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'ulid' => (string) Str::ulid(),

                'name' =>
                $validated['name'],

                'email' =>
                $validated['email'],

                'username' =>
                $validated['username']
                    ?: null,

                'phone' =>
                $validated['phone']
                    ?? null,

                'password' =>
                Hash::make(
                    $validated['password']
                ),

                'status' =>
                $validated['status'],

                'is_super_admin' =>
                false,
            ]);

            if (!empty($validated['organization_id'])) {
                $organizationId =
                    (int) $validated['organization_id'];

                DB::table('organization_users')
                    ->insert([
                        'organization_id' =>
                        $organizationId,

                        'user_id' =>
                        $user->id,

                        'status' =>
                        'active',

                        'is_default' =>
                        (bool) (
                            $validated['is_default']
                            ?? true
                        ),

                        'joined_at' =>
                        now(),

                        'created_at' =>
                        now(),

                        'updated_at' =>
                        now(),
                    ]);

                if (!empty($validated['role_id'])) {
                    $role = Role::query()
                        ->where(
                            'organization_id',
                            $organizationId
                        )
                        ->findOrFail(
                            $validated['role_id']
                        );

                    DB::table('user_roles')
                        ->insert([
                            'user_id' =>
                            $user->id,

                            'role_id' =>
                            $role->id,

                            'organization_id' =>
                            $organizationId,

                            'created_at' =>
                            now(),

                            'updated_at' =>
                            now(),
                        ]);
                }
            }

            return $user;
        });

        return redirect()
            ->route(
                'superadmin.users.show',
                $user
            )
            ->with(
                'success',
                'User berhasil dibuat.'
            );
    }

    public function show(
        User $user
    ): View {
        $user->load([
            'organizations' => function ($query) {
                $query->orderBy(
                    'organizations.name'
                );
            },

            'roles' => function ($query) {
                $query->orderBy('roles.name');
            },
        ]);

        $organizations = Organization::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $roles = Role::query()
            ->whereNotNull('organization_id')
            ->orderBy('name')
            ->get();

        return view(
            'superadmin.users.show',
            compact(
                'user',
                'organizations',
                'roles'
            )
        );
    }

    public function edit(
        User $user
    ): View {
        return view(
            'superadmin.users.edit',
            compact('user')
        );
    }

    public function update(
        Request $request,
        User $user
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',

                Rule::unique(
                    'users',
                    'email'
                )->ignore($user->id),
            ],

            'username' => [
                'nullable',
                'string',
                'max:100',

                Rule::unique(
                    'users',
                    'username'
                )->ignore($user->id),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                    'suspended',
                ]),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $payload = [
            'name' =>
            $validated['name'],

            'email' =>
            $validated['email'],

            'username' =>
            $validated['username']
                ?: null,

            'phone' =>
            $validated['phone']
                ?? null,

            'status' =>
            $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $payload['password'] =
                Hash::make(
                    $validated['password']
                );
        }

        $user->update($payload);

        return redirect()
            ->route(
                'superadmin.users.show',
                $user
            )
            ->with(
                'success',
                'User berhasil diperbarui.'
            );
    }

    public function toggleStatus(
        Request $request,
        User $user
    ): RedirectResponse {
        if ($user->id === $request->user()->id) {
            return back()->with(
                'error',
                'Anda tidak dapat menonaktifkan akun sendiri.'
            );
        }

        $newStatus =
            $user->status === 'active'
            ? 'suspended'
            : 'active';

        $user->update([
            'status' => $newStatus,
        ]);

        return back()->with(
            'success',
            'Status user berhasil diperbarui.'
        );
    }

    public function addMembership(
        Request $request,
        User $user
    ): RedirectResponse {
        if ($user->isSuperAdmin()) {
            return back()->with(
                'error',
                'Platform Superadmin tidak membutuhkan organization membership.'
            );
        }

        $validated = $request->validate([
            'organization_id' => [
                'required',
                'integer',
                'exists:organizations,id',
            ],

            'role_id' => [
                'nullable',
                'integer',
                'exists:roles,id',
            ],

            'is_default' => [
                'nullable',
                'boolean',
            ],
        ]);

        $organizationId =
            (int) $validated['organization_id'];

        DB::transaction(function () use (
            $validated,
            $organizationId,
            $user
        ) {
            if (
                $validated['is_default']
                ?? false
            ) {
                DB::table('organization_users')
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->update([
                        'is_default' =>
                        false,

                        'updated_at' =>
                        now(),
                    ]);
            }

            DB::table('organization_users')
                ->updateOrInsert(
                    [
                        'organization_id' =>
                        $organizationId,

                        'user_id' =>
                        $user->id,
                    ],
                    [
                        'status' =>
                        'active',

                        'is_default' =>
                        (bool) (
                            $validated['is_default']
                            ?? false
                        ),

                        'joined_at' =>
                        now(),

                        'updated_at' =>
                        now(),

                        'created_at' =>
                        now(),
                    ]
                );

            if (!empty($validated['role_id'])) {
                $role = Role::query()
                    ->where(
                        'organization_id',
                        $organizationId
                    )
                    ->findOrFail(
                        $validated['role_id']
                    );

                DB::table('user_roles')
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->where(
                        'organization_id',
                        $organizationId
                    )
                    ->delete();

                DB::table('user_roles')
                    ->insert([
                        'user_id' =>
                        $user->id,

                        'role_id' =>
                        $role->id,

                        'organization_id' =>
                        $organizationId,

                        'created_at' =>
                        now(),

                        'updated_at' =>
                        now(),
                    ]);
            }
        });

        return back()->with(
            'success',
            'Organization membership berhasil ditambahkan.'
        );
    }

    public function setDefaultOrganization(
        User $user,
        Organization $organization
    ): RedirectResponse {
        $membershipExists =
            DB::table('organization_users')
            ->where(
                'user_id',
                $user->id
            )
            ->where(
                'organization_id',
                $organization->id
            )
            ->exists();

        if (!$membershipExists) {
            return back()->with(
                'error',
                'User bukan member organization tersebut.'
            );
        }

        DB::transaction(function () use (
            $user,
            $organization
        ) {
            DB::table('organization_users')
                ->where(
                    'user_id',
                    $user->id
                )
                ->update([
                    'is_default' =>
                    false,

                    'updated_at' =>
                    now(),
                ]);

            DB::table('organization_users')
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->update([
                    'is_default' =>
                    true,

                    'updated_at' =>
                    now(),
                ]);
        });

        return back()->with(
            'success',
            'Default organization berhasil diperbarui.'
        );
    }

    public function removeMembership(
        Request $request,
        User $user,
        Organization $organization
    ): RedirectResponse {
        if ($user->isSuperAdmin()) {
            return back()->with(
                'error',
                'Platform Superadmin tidak menggunakan organization membership.'
            );
        }

        DB::transaction(function () use (
            $user,
            $organization
        ) {
            DB::table('user_roles')
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->delete();

            DB::table('organization_users')
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->delete();

            $hasDefault =
                DB::table('organization_users')
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'is_default',
                    true
                )
                ->exists();

            if (!$hasDefault) {
                $firstMembership =
                    DB::table('organization_users')
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->orderBy('id')
                    ->first();

                if ($firstMembership) {
                    DB::table('organization_users')
                        ->where(
                            'id',
                            $firstMembership->id
                        )
                        ->update([
                            'is_default' =>
                            true,

                            'updated_at' =>
                            now(),
                        ]);
                }
            }
        });

        return back()->with(
            'success',
            'Organization membership berhasil dicabut.'
        );
    }
}
