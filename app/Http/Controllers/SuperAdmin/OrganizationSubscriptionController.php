<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationSubscriptionController extends Controller
{
    public function index(
        Request $request
    ): View {
        $search = trim(
            (string) $request->get('search')
        );

        $status = trim(
            (string) $request->get('status')
        );

        $subscriptions =
            OrganizationSubscription::query()
            ->with([
                'organization',
                'plan',
            ])

            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->whereHas(
                        'organization',
                        function ($organizationQuery) use ($search) {
                            $organizationQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'slug',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )

            ->when(
                $status !== '',
                fn($query) =>
                $query->where(
                    'status',
                    $status
                )
            )

            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'superadmin.subscriptions.index',
            compact(
                'subscriptions',
                'search',
                'status'
            )
        );
    }

    public function create(): View
    {
        $organizations =
            Organization::query()
            ->where(
                'status',
                'active'
            )
            ->orderBy('name')
            ->get();

        $plans =
            SubscriptionPlan::query()
            ->where(
                'status',
                'active'
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view(
            'superadmin.subscriptions.create',
            compact(
                'organizations',
                'plans'
            )
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $request->validate([
                'organization_id' => [
                    'required',
                    'integer',
                    'exists:organizations,id',
                ],

                'subscription_plan_id' => [
                    'required',
                    'integer',
                    'exists:subscription_plans,id',
                ],

                'starts_at' => [
                    'required',
                    'date',
                ],

                'auto_renew' => [
                    'nullable',
                    'boolean',
                ],
            ]);

        $existing =
            OrganizationSubscription::query()
            ->where(
                'organization_id',
                $validated['organization_id']
            )
            ->whereIn(
                'status',
                [
                    'trialing',
                    'active',
                    'past_due',
                ]
            )
            ->exists();

        if ($existing) {
            return back()
                ->withInput()
                ->withErrors([
                    'organization_id' =>
                    'Organization sudah memiliki subscription aktif.',
                ]);
        }

        $plan =
            SubscriptionPlan::findOrFail(
                $validated['subscription_plan_id']
            );

        $start =
            now()->parse(
                $validated['starts_at']
            );

        $periodEnd =
            $plan->billing_interval
            === 'yearly'
            ? $start->copy()->addYear()
            : $start->copy()->addMonth();

        $trialEndsAt =
            $plan->trial_days > 0
            ? $start
            ->copy()
            ->addDays(
                $plan->trial_days
            )
            : null;

        $subscription =
            DB::transaction(
                function () use (
                    $validated,
                    $request,
                    $plan,
                    $start,
                    $periodEnd,
                    $trialEndsAt
                ) {
                    $subscription =
                        OrganizationSubscription::create([
                            'organization_id' =>
                            $validated['organization_id'],

                            'subscription_plan_id' =>
                            $plan->id,

                            'status' =>
                            $trialEndsAt
                                ? 'trialing'
                                : 'active',

                            'starts_at' =>
                            $start,

                            'trial_ends_at' =>
                            $trialEndsAt,

                            'current_period_start' =>
                            $start,

                            'current_period_end' =>
                            $periodEnd,

                            'auto_renew' =>
                            $request->boolean(
                                'auto_renew'
                            ),
                        ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Activate Modules Included in Plan
                    |--------------------------------------------------------------------------
                    */

                    $plan->load('modules');

                    foreach (
                        $plan->modules
                        as $module
                    ) {
                        DB::table(
                            'organization_modules'
                        )->updateOrInsert(
                            [
                                'organization_id' =>
                                $validated['organization_id'],

                                'module_id' =>
                                $module->id,
                            ],
                            [
                                'enabled' =>
                                true,

                                'status' =>
                                'active',

                                'started_at' =>
                                $start,

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
                    }

                    return $subscription;
                }
            );

        return redirect()
            ->route(
                'superadmin.subscriptions.show',
                $subscription
            )
            ->with(
                'success',
                'Subscription berhasil dibuat.'
            );
    }

    public function show(
        OrganizationSubscription $subscription
    ): View {
        $subscription->load([
            'organization',
            'plan.modules',
        ]);

        return view(
            'superadmin.subscriptions.show',
            compact('subscription')
        );
    }

    public function activate(
        OrganizationSubscription $subscription
    ): RedirectResponse {
        $subscription->update([
            'status' =>
            'active',

            'ends_at' =>
            null,
        ]);

        return back()->with(
            'success',
            'Subscription diaktifkan.'
        );
    }

    public function suspend(
        OrganizationSubscription $subscription
    ): RedirectResponse {
        $subscription->update([
            'status' =>
            'suspended',
        ]);

        return back()->with(
            'success',
            'Subscription disuspend.'
        );
    }

    public function cancel(
        OrganizationSubscription $subscription
    ): RedirectResponse {
        $subscription->update([
            'status' =>
            'canceled',

            'auto_renew' =>
            false,

            'ends_at' =>
            now(),
        ]);

        return back()->with(
            'success',
            'Subscription dibatalkan.'
        );
    }
}
