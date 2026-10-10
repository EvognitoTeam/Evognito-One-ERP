<?php

namespace App\Services;

use App\Models\Module;
use App\Models\OrganizationModulePricing;
use App\Models\OrganizationSubscription;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class BillingCalculator
{
    public function __construct(
        protected BillingUserCounter $userCounter
    ) {}

    public function calculate(
        OrganizationSubscription $subscription,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        $subscription->loadMissing([
            'organization',
            'plan.modules',
        ]);

        $organization =
            $subscription->organization;

        $plan =
            $subscription->plan;

        if (!$organization) {
            throw ValidationException::withMessages([
                'billing' =>
                'Organization subscription tidak ditemukan.',
            ]);
        }

        if (!$plan) {
            throw ValidationException::withMessages([
                'billing' =>
                'Subscription tidak memiliki plan.',
            ]);
        }

        $items = [];

        /*
        |--------------------------------------------------------------------------
        | Plan Base Price
        |--------------------------------------------------------------------------
        */

        if ((float) $plan->base_price > 0) {
            $items[] = [
                'module_id' => null,

                'type' =>
                'plan_base',

                'code' =>
                'PLAN_BASE',

                'description' =>
                $plan->name
                    . ' — Base Subscription',

                'quantity' =>
                1,

                'unit_price' =>
                (float) $plan->base_price,

                'amount' =>
                (float) $plan->base_price,

                'metadata' => [
                    'plan_id' =>
                    $plan->id,

                    'pricing_source' =>
                    'plan',
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Plan Per User
        |--------------------------------------------------------------------------
        */

        if ((float) $plan->price_per_user > 0) {
            $actualCount =
                $this->userCounter->count(
                    $organization,
                    $plan->billing_user_scope,
                    null,
                    $plan->modules
                );

            $billableCount =
                max(
                    0,
                    $actualCount
                        - (int) $plan->included_users
                );

            if ($billableCount > 0) {
                $amount =
                    $billableCount
                    * (float) $plan->price_per_user;

                $items[] = [
                    'module_id' => null,

                    'type' =>
                    'plan_usage',

                    'code' =>
                    'PLAN_USERS',

                    'description' =>
                    $plan->name
                        . ' — '
                        . $this->scopeLabel(
                            $plan->billing_user_scope
                        ),

                    'quantity' =>
                    $billableCount,

                    'unit_price' =>
                    (float) $plan->price_per_user,

                    'amount' =>
                    $amount,

                    'metadata' => [
                        'actual_count' =>
                        $actualCount,

                        'included_users' =>
                        (int) $plan->included_users,

                        'billing_user_scope' =>
                        $plan->billing_user_scope,

                        'pricing_source' =>
                        'plan',
                    ],
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Plan Modules
        |--------------------------------------------------------------------------
        */

        foreach ($plan->modules as $module) {
            $pricing =
                $this->resolveModulePricing(
                    $organization->id,
                    $module,
                    $periodStart,
                    $periodEnd
                );

            /*
             * Organization custom pricing
             * mempunyai prioritas tertinggi.
             */

            if ($pricing === null) {
                $mode =
                    $module->pivot->billing_mode
                    ?? 'included';

                if ($mode === 'included') {
                    $items[] = [
                        'module_id' =>
                        $module->id,

                        'type' =>
                        'module_included',

                        'code' =>
                        strtoupper(
                            $module->code
                        ),

                        'description' =>
                        $module->name
                            . ' — Included in Plan',

                        'quantity' =>
                        1,

                        'unit_price' =>
                        0,

                        'amount' =>
                        0,

                        'metadata' => [
                            'billing_mode' =>
                            'included',

                            'pricing_source' =>
                            'plan',
                        ],
                    ];

                    continue;
                }

                if ($mode === 'inherit') {
                    $pricing = [
                        'pricing_type' =>
                        $module->pricing_type,

                        'fixed_price' =>
                        (float) $module->fixed_price,

                        'price_per_user' =>
                        (float) $module->price_per_user,

                        'included_users' =>
                        (int) $module->included_users,

                        'minimum_users' =>
                        $module->minimum_users,

                        'maximum_users' =>
                        $module->maximum_users,

                        'billing_user_scope' =>
                        $module->billing_user_scope,

                        'pricing_source' =>
                        'module_default',
                    ];
                } else {
                    $pricing = [
                        'pricing_type' =>
                        $module->pivot->pricing_type,

                        'fixed_price' =>
                        (float) $module->pivot->fixed_price,

                        'price_per_user' =>
                        (float) $module->pivot->price_per_user,

                        'included_users' =>
                        (int) $module->pivot->included_users,

                        'minimum_users' =>
                        $module->pivot->minimum_users,

                        'maximum_users' =>
                        $module->pivot->maximum_users,

                        'billing_user_scope' =>
                        $module->pivot->billing_user_scope,

                        'pricing_source' =>
                        'plan_override',
                    ];
                }
            }

            $moduleItems =
                $this->calculateModule(
                    $organization,
                    $module,
                    $pricing
                );

            foreach ($moduleItems as $item) {
                $items[] = $item;
            }
        }

        $subtotal =
            round(
                collect($items)
                    ->sum('amount'),
                2
            );

        return [
            'items' =>
            $items,

            'subtotal' =>
            $subtotal,

            'additional_total' =>
            0,

            'discount_total' =>
            0,

            'tax_total' =>
            0,

            'total' =>
            $subtotal,
        ];
    }

    protected function resolveModulePricing(
        int $organizationId,
        Module $module,
        Carbon $periodStart,
        Carbon $periodEnd
    ): ?array {
        $pricing =
            OrganizationModulePricing::query()
            ->where(
                'organization_id',
                $organizationId
            )
            ->where(
                'module_id',
                $module->id
            )
            ->whereDate(
                'effective_from',
                '<=',
                $periodEnd->toDateString()
            )
            ->where(function ($query) use (
                $periodStart
            ) {
                $query
                    ->whereNull(
                        'effective_until'
                    )
                    ->orWhereDate(
                        'effective_until',
                        '>=',
                        $periodStart
                            ->toDateString()
                    );
            })
            ->latest('effective_from')
            ->first();

        if (!$pricing) {
            return null;
        }

        return [
            'pricing_type' =>
            $pricing->pricing_type,

            'fixed_price' =>
            (float) $pricing->fixed_price,

            'price_per_user' =>
            (float) $pricing->price_per_user,

            'included_users' =>
            (int) $pricing->included_users,

            'minimum_users' =>
            $pricing->minimum_users,

            'maximum_users' =>
            $pricing->maximum_users,

            'billing_user_scope' =>
            $pricing->billing_user_scope,

            'pricing_source' =>
            'organization_override',
        ];
    }

    protected function calculateModule(
        $organization,
        Module $module,
        array $pricing
    ): array {
        $type =
            $pricing['pricing_type']
            ?? 'fixed';

        $items = [];

        if (
            in_array(
                $type,
                [
                    'fixed',
                    'hybrid',
                ],
                true
            )
            && (float) $pricing['fixed_price'] > 0
        ) {
            $items[] = [
                'module_id' =>
                $module->id,

                'type' =>
                'module_base',

                'code' =>
                strtoupper(
                    $module->code
                ) . '_BASE',

                'description' =>
                $module->name
                    . ' — Base',

                'quantity' =>
                1,

                'unit_price' =>
                (float) $pricing['fixed_price'],

                'amount' =>
                (float) $pricing['fixed_price'],

                'metadata' => [
                    'pricing_type' =>
                    $type,

                    'pricing_source' =>
                    $pricing['pricing_source'],
                ],
            ];
        }

        if (
            in_array(
                $type,
                [
                    'per_user',
                    'hybrid',
                ],
                true
            )
            && (float) $pricing['price_per_user'] > 0
        ) {
            $scope =
                $pricing['billing_user_scope']
                ?? 'licensed_users';

            $actualCount =
                $this->userCounter->count(
                    $organization,
                    $scope,
                    $module
                );

            $minimum =
                $pricing['minimum_users']
                !== null
                ? (int) $pricing['minimum_users']
                : null;

            $maximum =
                $pricing['maximum_users']
                !== null
                ? (int) $pricing['maximum_users']
                : null;

            if (
                $maximum !== null
                && $actualCount > $maximum
            ) {
                throw ValidationException::withMessages([
                    'billing' =>
                    $module->name
                        . ' memiliki '
                        . $actualCount
                        . ' user, melebihi batas maksimum '
                        . $maximum
                        . ' user.',
                ]);
            }

            $effectiveCount =
                $minimum !== null
                ? max(
                    $actualCount,
                    $minimum
                )
                : $actualCount;

            $included =
                (int) (
                    $pricing['included_users']
                    ?? 0
                );

            $billable =
                max(
                    0,
                    $effectiveCount
                        - $included
                );

            if ($billable > 0) {
                $unitPrice =
                    (float) $pricing['price_per_user'];

                $items[] = [
                    'module_id' =>
                    $module->id,

                    'type' =>
                    'module_usage',

                    'code' =>
                    strtoupper(
                        $module->code
                    ) . '_USERS',

                    'description' =>
                    $module->name
                        . ' — '
                        . $this->scopeLabel(
                            $scope
                        ),

                    'quantity' =>
                    $billable,

                    'unit_price' =>
                    $unitPrice,

                    'amount' =>
                    $billable
                        * $unitPrice,

                    'metadata' => [
                        'pricing_type' =>
                        $type,

                        'actual_count' =>
                        $actualCount,

                        'effective_count' =>
                        $effectiveCount,

                        'included_users' =>
                        $included,

                        'minimum_users' =>
                        $minimum,

                        'maximum_users' =>
                        $maximum,

                        'billing_user_scope' =>
                        $scope,

                        'pricing_source' =>
                        $pricing['pricing_source'],
                    ],
                ];
            }
        }

        return $items;
    }

    protected function scopeLabel(
        string $scope
    ): string {
        return match ($scope) {
            'all_users' =>
            'All Users',

            'employees' =>
            'Employees',

            'active_employees' =>
            'Active Employees',

            'licensed_users' =>
            'Licensed Users',

            'active_module_users' =>
            'Active Module Users',

            'students' =>
            'Students',

            'teachers' =>
            'Teachers',

            default =>
            ucwords(
                str_replace(
                    '_',
                    ' ',
                    $scope
                )
            ),
        };
    }
}
