<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid',

        'code',
        'name',
        'version',
        'description',

        'category',
        'icon',
        'type',

        'entry_url',
        'api_url',

        'workspace_path',

        'status',

        'pricing_type',
        'fixed_price',
        'price_per_user',

        'included_users',
        'minimum_users',
        'maximum_users',

        'billing_user_scope',
        'billing_interval',

        'sort_order',

        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'fixed_price' => 'decimal:2',
            'price_per_user' => 'decimal:2',

            'included_users' => 'integer',
            'minimum_users' => 'integer',
            'maximum_users' => 'integer',

            'sort_order' => 'integer',

            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Module $module): void {
            if (empty($module->ulid)) {
                $module->ulid =
                    (string) Str::ulid();
            }

            if (empty($module->version)) {
                $module->version =
                    '1.0.0';
            }
        });
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(
            Organization::class,
            'organization_modules',
            'module_id',
            'organization_id'
        )
            ->withPivot([
                'enabled',
                'status',

                'started_at',
                'expires_at',

                'settings',
            ])
            ->withTimestamps();
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(
            SubscriptionPlan::class,
            'plan_modules',
            'module_id',
            'plan_id'
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

    public function permissions(): HasMany
    {
        return $this->hasMany(
            Permission::class
        );
    }

    public function organizationPricing(): HasMany
    {
        return $this->hasMany(
            OrganizationModulePricing::class,
            'module_id'
        );
    }

    public function pricingLabel(): string
    {
        return match ($this->pricing_type) {
            'per_user' => 'Per User',
            'hybrid' => 'Hybrid',
            default => 'Fixed',
        };
    }
}
