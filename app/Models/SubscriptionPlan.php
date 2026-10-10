<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SubscriptionPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ulid',
        'code',
        'name',
        'description',

        'status',
        'is_public',

        'billing_interval',
        'currency',

        'base_price',
        'price_per_user',
        'included_users',
        'billing_user_scope',

        'trial_days',
        'sort_order',

        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',

            'base_price' => 'decimal:2',
            'price_per_user' => 'decimal:2',

            'included_users' => 'integer',
            'trial_days' => 'integer',
            'sort_order' => 'integer',

            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (
                SubscriptionPlan $plan
            ): void {
                if (empty($plan->ulid)) {
                    $plan->ulid =
                        (string) Str::ulid();
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Modules
    |--------------------------------------------------------------------------
    |
    | Pivot table:
    |
    | plan_modules
    | ├── plan_id
    | └── module_id
    |
    | Jangan biarkan Laravel menebak
    | subscription_plan_id karena schema kita
    | menggunakan plan_id.
    |
    */

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(
            Module::class,
            'plan_modules',
            'plan_id',
            'module_id'
        )
            ->withPivot([
                'billing_mode',

                'pricing_type',
                'fixed_price',
                'price_per_user',

                'included_users',
                'minimum_users',
                'maximum_users',

                'billing_user_scope',
                'billing_interval',

                'settings',
            ])
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Organization Subscriptions
    |--------------------------------------------------------------------------
    */

    public function subscriptions(): HasMany
    {
        return $this->hasMany(
            OrganizationSubscription::class,
            'subscription_plan_id'
        );
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(
            OrganizationSubscription::class,
            'subscription_plan_id'
        )
            ->whereIn(
                'status',
                [
                    'trialing',
                    'active',
                    'past_due',
                ]
            );
    }
}