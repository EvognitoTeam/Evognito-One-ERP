<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrganizationSubscription extends Model
{
    protected $fillable = [
        'ulid',

        'organization_id',
        'subscription_plan_id',

        'status',

        'starts_at',
        'trial_ends_at',

        'current_period_start',
        'current_period_end',

        'ends_at',

        'auto_renew',

        'settings',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'trial_ends_at' => 'datetime',

            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',

            'ends_at' => 'datetime',

            'auto_renew' => 'boolean',

            'settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (
            OrganizationSubscription $subscription
        ): void {
            if (empty($subscription->ulid)) {
                $subscription->ulid =
                    (string) Str::ulid();
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(
            Organization::class
        );
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(
            SubscriptionPlan::class,
            'subscription_plan_id'
        );
    }
}
