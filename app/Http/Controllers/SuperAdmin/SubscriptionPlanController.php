<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
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

        $plans = SubscriptionPlan::query()
            ->withCount([
                'modules',
                'activeSubscriptions',
            ])

            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        function ($subQuery) use ($search) {
                            $subQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'code',
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

            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view(
            'superadmin.plans.index',
            compact(
                'plans',
                'search',
                'status'
            )
        );
    }

    public function create(): View
    {
        $modules = Module::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view(
            'superadmin.plans.create',
            compact('modules')
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $this->validatePlan(
                $request
            );

        $plan = DB::transaction(
            function () use (
                $validated,
                $request
            ) {
                $plan =
                    SubscriptionPlan::create([
                        'ulid' =>
                        (string) Str::ulid(),

                        'code' =>
                        $validated['code'],

                        'name' =>
                        $validated['name'],

                        'description' =>
                        $validated['description']
                            ?? null,

                        'status' =>
                        $validated['status'],

                        'is_public' =>
                        $request->boolean(
                            'is_public'
                        ),

                        'billing_interval' =>
                        $validated['billing_interval'],

                        'currency' =>
                        'IDR',

                        'base_price' =>
                        $validated['base_price'],

                        'price_per_user' =>
                        $validated['price_per_user'],

                        'included_users' =>
                        $validated['included_users'],

                        'billing_user_scope' =>
                        $validated['billing_user_scope'],

                        'trial_days' =>
                        $validated['trial_days'],

                        'sort_order' =>
                        $validated['sort_order'],
                    ]);

                $this->syncModules(
                    $plan,
                    $validated['module_ids'] ?? [],
                    $request
                );

                return $plan;
            }
        );

        return redirect()
            ->route(
                'superadmin.plans.show',
                $plan
            )
            ->with(
                'success',
                'Subscription plan berhasil dibuat.'
            );
    }

    public function show(
        SubscriptionPlan $plan
    ): View {
        $plan->load([
            'modules' => function ($query) {
                $query
                    ->orderBy(
                        'modules.sort_order'
                    )
                    ->orderBy(
                        'modules.name'
                    );
            },
        ]);

        $plan->loadCount([
            'modules',
            'activeSubscriptions',
        ]);

        return view(
            'superadmin.plans.show',
            compact('plan')
        );
    }

    public function edit(
        SubscriptionPlan $plan
    ): View {
        $plan->load('modules');

        $modules = Module::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view(
            'superadmin.plans.edit',
            compact(
                'plan',
                'modules'
            )
        );
    }

    public function update(
        Request $request,
        SubscriptionPlan $plan
    ): RedirectResponse {
        $validated =
            $this->validatePlan(
                $request,
                $plan
            );

        DB::transaction(
            function () use (
                $validated,
                $request,
                $plan
            ) {
                $plan->update([
                    'code' =>
                    $validated['code'],

                    'name' =>
                    $validated['name'],

                    'description' =>
                    $validated['description']
                        ?? null,

                    'status' =>
                    $validated['status'],

                    'is_public' =>
                    $request->boolean(
                        'is_public'
                    ),

                    'billing_interval' =>
                    $validated['billing_interval'],

                    'base_price' =>
                    $validated['base_price'],

                    'price_per_user' =>
                    $validated['price_per_user'],

                    'included_users' =>
                    $validated['included_users'],

                    'billing_user_scope' =>
                    $validated['billing_user_scope'],

                    'trial_days' =>
                    $validated['trial_days'],

                    'sort_order' =>
                    $validated['sort_order'],
                ]);

                $this->syncModules(
                    $plan,
                    $validated['module_ids'] ?? [],
                    $request
                );
            }
        );

        return redirect()
            ->route(
                'superadmin.plans.show',
                $plan
            )
            ->with(
                'success',
                'Subscription plan berhasil diperbarui.'
            );
    }

    public function toggleStatus(
        SubscriptionPlan $plan
    ): RedirectResponse {
        $plan->update([
            'status' =>
            $plan->status === 'active'
                ? 'inactive'
                : 'active',
        ]);

        return back()->with(
            'success',
            'Status plan berhasil diperbarui.'
        );
    }

    protected function validatePlan(
        Request $request,
        ?SubscriptionPlan $plan = null
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:100',

                Rule::unique(
                    'subscription_plans',
                    'code'
                )->ignore(
                    $plan?->id
                ),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],

            'billing_interval' => [
                'required',
                Rule::in([
                    'monthly',
                    'yearly',
                ]),
            ],

            'base_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'price_per_user' => [
                'required',
                'numeric',
                'min:0',
            ],

            'included_users' => [
                'required',
                'integer',
                'min:0',
            ],

            'billing_user_scope' => [
                'required',
                Rule::in([
                    'licensed_users',
                    'all_users',
                    'employees',
                    'active_employees',
                    'active_module_users',
                    'students',
                    'teachers',
                ]),
            ],

            'trial_days' => [
                'required',
                'integer',
                'min:0',
                'max:365',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],

            'module_ids' => [
                'nullable',
                'array',
            ],

            'module_ids.*' => [
                'integer',
                'exists:modules,id',
            ],
        ]);
    }

    protected function syncModules(
        SubscriptionPlan $plan,
        array $moduleIds,
        Request $request
    ): void {
        $sync = [];

        foreach ($moduleIds as $moduleId) {
            $moduleId =
                (int) $moduleId;

            $row = $request->input(
                "modules.{$moduleId}",
                []
            );

            $billingMode =
                $row['billing_mode']
                ?? 'included';

            if (
                !in_array(
                    $billingMode,
                    [
                        'included',
                        'inherit',
                        'override',
                    ],
                    true
                )
            ) {
                $billingMode =
                    'included';
            }

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
                $pricingType =
                    'fixed';
            }

            $sync[$moduleId] = [
                'billing_mode' =>
                $billingMode,

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
                filled(
                    $row['minimum_users']
                        ?? null
                )
                    ? max(
                        0,
                        (int) $row['minimum_users']
                    )
                    : null,

                'maximum_users' =>
                filled(
                    $row['maximum_users']
                        ?? null
                )
                    ? max(
                        0,
                        (int) $row['maximum_users']
                    )
                    : null,

                'billing_user_scope' =>
                $row['billing_user_scope']
                    ?? 'licensed_users',

                'billing_interval' =>
                $plan->billing_interval,

                'settings' =>
                null,
            ];
        }

        $plan->modules()
            ->sync($sync);
    }
}
