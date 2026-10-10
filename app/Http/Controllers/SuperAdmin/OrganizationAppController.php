<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrganizationAppController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Manage Organization Apps
    |--------------------------------------------------------------------------
    */

    public function index(
        Organization $organization
    ): View {
        /*
        |--------------------------------------------------------------------------
        | Semua module platform
        |--------------------------------------------------------------------------
        */

        $modules = Module::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Entitlement organization
        |--------------------------------------------------------------------------
        */

        $organizationModules =
            $organization
                ->modules()
                ->get()
                ->keyBy('id');

        /*
        |--------------------------------------------------------------------------
        | Tambahkan state organization ke setiap module
        |--------------------------------------------------------------------------
        */

        $modules->each(
            function (
                Module $module
            ) use (
                $organizationModules
            ) {
                $organizationModule =
                    $organizationModules->get(
                        $module->id
                    );

                $module->setAttribute(
                    'organization_enabled',
                    $organizationModule
                        ? (bool) $organizationModule
                            ->pivot
                            ->enabled
                        : false
                );

                $module->setAttribute(
                    'organization_status',
                    $organizationModule
                        ?->pivot
                        ?->status
                    ?? 'inactive'
                );

                $module->setAttribute(
                    'organization_started_at',
                    $organizationModule
                        ?->pivot
                        ?->started_at
                );

                $module->setAttribute(
                    'organization_expires_at',
                    $organizationModule
                        ?->pivot
                        ?->expires_at
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Subscription aktif
        |--------------------------------------------------------------------------
        */

        $subscription =
            OrganizationSubscription::query()
                ->with('plan')
                ->where(
                    'organization_id',
                    $organization->id
                )
                ->whereIn(
                    'status',
                    [
                        'trialing',
                        'active',
                        'past_due',
                    ]
                )
                ->latest()
                ->first();

        $activeCount =
            $modules
                ->where(
                    'organization_enabled',
                    true
                )
                ->count();

        $lockedCount =
            $modules->count()
            - $activeCount;

        return view(
            'superadmin.organizations.apps',
            compact(
                'organization',
                'modules',
                'subscription',
                'activeCount',
                'lockedCount'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Entitlements
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Organization $organization
    ): RedirectResponse {
        $validated =
            $request->validate([
                'modules' => [
                    'nullable',
                    'array',
                ],

                'modules.*.enabled' => [
                    'nullable',
                    'boolean',
                ],

                'modules.*.expires_at' => [
                    'nullable',
                    'date',
                ],
            ]);

        $modules =
            Module::query()
                ->get();

        DB::transaction(
            function () use (
                $request,
                $organization,
                $modules
            ) {
                foreach (
                    $modules
                    as $module
                ) {
                    /*
                     * Module global inactive
                     * tidak boleh diaktifkan
                     * untuk organization.
                     */
                    $enabled =
                        $module->status === 'active'
                        && $request->boolean(
                            "modules.{$module->id}.enabled"
                        );

                    $expiresAt =
                        $request->input(
                            "modules.{$module->id}.expires_at"
                        );

                    $existing =
                        DB::table(
                            'organization_modules'
                        )
                            ->where(
                                'organization_id',
                                $organization->id
                            )
                            ->where(
                                'module_id',
                                $module->id
                            )
                            ->first();

                    $data = [
                        'enabled' =>
                            $enabled,

                        'status' =>
                            $enabled
                                ? 'active'
                                : 'inactive',

                        'expires_at' =>
                            $enabled
                            && filled($expiresAt)
                                ? $expiresAt
                                : null,

                        'updated_at' =>
                            now(),
                    ];

                    /*
                     * Jika baru diaktifkan,
                     * set started_at.
                     */
                    if (
                        $enabled
                        && (
                            !$existing
                            || !(bool) $existing
                                ->enabled
                        )
                    ) {
                        $data['started_at'] =
                            now();
                    } elseif (
                        $existing
                    ) {
                        $data['started_at'] =
                            $existing
                                ->started_at;
                    } else {
                        $data['started_at'] =
                            null;
                    }

                    /*
                     * Insert baru.
                     */
                    if (!$existing) {
                        $data['created_at'] =
                            now();
                    }

                    DB::table(
                        'organization_modules'
                    )->updateOrInsert(
                        [
                            'organization_id' =>
                                $organization->id,

                            'module_id' =>
                                $module->id,
                        ],
                        $data
                    );
                }
            }
        );

        return redirect()
            ->route(
                'superadmin.organizations.apps.index',
                $organization
            )
            ->with(
                'success',
                'Akses aplikasi organization berhasil diperbarui.'
            );
    }
}