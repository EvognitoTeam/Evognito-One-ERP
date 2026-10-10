<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Organization;
use App\Models\OrganizationModulePricing;
use App\Models\OrganizationSubscription;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        Organization $organization
    ): View {
        $user =
            auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Organization Summary
        |--------------------------------------------------------------------------
        */

        $organization->loadCount([
            'users',
            'employees',
            'branches',
            'departments',
            'positions',
        ]);

        /*
        |--------------------------------------------------------------------------
        | All Platform Applications
        |--------------------------------------------------------------------------
        |
        | Semua aplikasi aktif di platform ditampilkan.
        | Organization entitlement ditentukan secara terpisah.
        |
        */

        $modules =
            Module::query()
            ->where(
                'status',
                'active'
            )
            ->orderBy(
                'sort_order'
            )
            ->orderBy(
                'name'
            )
            ->get();

        $internalApps =
            config(
                'evognito-apps.internal',
                []
            );

        $modules->each(
            function (
                Module $module
            ) use (
                $organization,
                $internalApps
            ) {
                $workspaceUrl = null;

                /*
        |--------------------------------------------------------------------------
        | Internal Module
        |--------------------------------------------------------------------------
        */

                if (
                    $module->type
                    === 'internal'
                ) {
                    $configuration =
                        $internalApps[$module->code]
                        ?? null;

                    if (
                        $configuration
                        && !empty($configuration['route'])
                        && \Illuminate\Support\Facades\Route::has(
                            $configuration['route']
                        )
                    ) {
                        $workspaceUrl =
                            route(
                                $configuration['route'],
                                [
                                    'organization' =>
                                    $organization->slug,
                                ]
                            );
                    }
                }

                /*
        |--------------------------------------------------------------------------
        | External Module
        |--------------------------------------------------------------------------
        */

                if (
                    $module->type
                    === 'external'
                ) {
                    $workspaceUrl =
                        $module->entry_url;
                }

                $module->setAttribute(
                    'workspace_url',
                    $workspaceUrl
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Organization Enabled Modules
        |--------------------------------------------------------------------------
        */

        $organizationModules =
            $organization
            ->modules()
            ->get()
            ->keyBy('id');

        /*
        |--------------------------------------------------------------------------
        | Active Subscription
        |--------------------------------------------------------------------------
        */

        $subscription =
            OrganizationSubscription::query()
            ->with([
                'plan.modules',
            ])
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

        /*
        |--------------------------------------------------------------------------
        | Organization Pricing Override
        |--------------------------------------------------------------------------
        */

        $organizationPricing =
            OrganizationModulePricing::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->where(function ($query) {
                $query
                    ->whereNull(
                        'effective_from'
                    )
                    ->orWhereDate(
                        'effective_from',
                        '<=',
                        now()->toDateString()
                    );
            })
            ->where(function ($query) {
                $query
                    ->whereNull(
                        'effective_until'
                    )
                    ->orWhereDate(
                        'effective_until',
                        '>=',
                        now()->toDateString()
                    );
            })
            ->get()
            ->keyBy('module_id');

        /*
        |--------------------------------------------------------------------------
        | Resolve App State
        |--------------------------------------------------------------------------
        */

        $modules->each(
            function (
                Module $module
            ) use (
                $organizationModules,
                $organizationPricing,
                $subscription
            ) {
                $organizationModule =
                    $organizationModules->get(
                        $module->id
                    );

                $enabled =
                    $organizationModule
                    && (bool) $organizationModule
                        ->pivot
                        ->enabled
                    && $organizationModule
                    ->pivot
                    ->status
                    === 'active';

                /*
                 * Jika module punya expiry,
                 * akses dianggap tidak aktif
                 * ketika sudah lewat.
                 */
                if (
                    $enabled
                    && $organizationModule
                    ->pivot
                    ->expires_at
                ) {
                    $expiresAt =
                        \Illuminate\Support\Carbon::parse(
                            $organizationModule
                                ->pivot
                                ->expires_at
                        );

                    if ($expiresAt->isPast()) {
                        $enabled =
                            false;
                    }
                }

                $module->setAttribute(
                    'workspace_enabled',
                    $enabled
                );

                $module->setAttribute(
                    'workspace_locked',
                    !$enabled
                );

                /*
                |--------------------------------------------------------------------------
                | Pricing Source
                |--------------------------------------------------------------------------
                */

                $pricingOverride =
                    $organizationPricing->get(
                        $module->id
                    );

                if ($pricingOverride) {
                    $module->setAttribute(
                        'workspace_pricing_type',
                        $pricingOverride
                            ->pricing_type
                    );

                    $module->setAttribute(
                        'workspace_fixed_price',
                        (float) $pricingOverride
                            ->fixed_price
                    );

                    $module->setAttribute(
                        'workspace_price_per_user',
                        (float) $pricingOverride
                            ->price_per_user
                    );

                    $module->setAttribute(
                        'workspace_pricing_source',
                        'organization'
                    );

                    return;
                }

                /*
                 * Check apakah module ada dalam plan.
                 */
                $planModule =
                    $subscription
                    ?->plan
                    ?->modules
                    ?->firstWhere(
                        'id',
                        $module->id
                    );

                if ($planModule) {
                    $billingMode =
                        $planModule
                        ->pivot
                        ->billing_mode
                        ?? 'included';

                    if (
                        $billingMode
                        === 'included'
                    ) {
                        $module->setAttribute(
                            'workspace_pricing_type',
                            'included'
                        );

                        $module->setAttribute(
                            'workspace_fixed_price',
                            0
                        );

                        $module->setAttribute(
                            'workspace_price_per_user',
                            0
                        );

                        $module->setAttribute(
                            'workspace_pricing_source',
                            'plan'
                        );

                        return;
                    }

                    if (
                        $billingMode
                        === 'override'
                    ) {
                        $module->setAttribute(
                            'workspace_pricing_type',
                            $planModule
                                ->pivot
                                ->pricing_type
                        );

                        $module->setAttribute(
                            'workspace_fixed_price',
                            (float) $planModule
                                ->pivot
                                ->fixed_price
                        );

                        $module->setAttribute(
                            'workspace_price_per_user',
                            (float) $planModule
                                ->pivot
                                ->price_per_user
                        );

                        $module->setAttribute(
                            'workspace_pricing_source',
                            'plan'
                        );

                        return;
                    }
                }

                /*
                 * Default module pricing.
                 */
                $module->setAttribute(
                    'workspace_pricing_type',
                    $module->pricing_type
                );

                $module->setAttribute(
                    'workspace_fixed_price',
                    (float) $module->fixed_price
                );

                $module->setAttribute(
                    'workspace_price_per_user',
                    (float) $module->price_per_user
                );

                $module->setAttribute(
                    'workspace_pricing_source',
                    'module'
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Counters
        |--------------------------------------------------------------------------
        */

        $activeAppCount =
            $modules
            ->where(
                'workspace_enabled',
                true
            )
            ->count();

        $lockedAppCount =
            $modules
            ->where(
                'workspace_locked',
                true
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Organization Switcher
        |--------------------------------------------------------------------------
        */

        if ($user->isSuperAdmin()) {
            $availableOrganizations =
                Organization::query()
                ->where(
                    'status',
                    'active'
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'slug',
                    'logo',
                ]);
        } else {
            $availableOrganizations =
                $user
                ->organizations()
                ->wherePivot(
                    'status',
                    'active'
                )
                ->where(
                    'organizations.status',
                    'active'
                )
                ->orderBy(
                    'organizations.name'
                )
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        $categories =
            $modules
            ->pluck('category')
            ->filter()
            ->map(
                fn($category) =>
                trim(
                    $category
                )
            )
            ->filter()
            ->unique()
            ->values();

        return view(
            'workspace.dashboard',
            compact(
                'organization',
                'modules',
                'availableOrganizations',
                'categories',
                'subscription',
                'activeAppCount',
                'lockedAppCount'
            )
        );
    }
}
