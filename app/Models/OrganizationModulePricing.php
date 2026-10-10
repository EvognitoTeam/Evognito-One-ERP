<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationModulePricing extends Model
{
    protected $table = 'organization_module_pricing';

    protected $fillable = [
        'organization_id',
        'module_id',

        'pricing_type',

        'fixed_price',
        'price_per_user',

        'included_users',
        'minimum_users',
        'maximum_users',

        'billing_user_scope',

        'effective_from',
        'effective_until',

        'settings',
    ];

    protected function casts(): array
    {
        return [
            'fixed_price' => 'decimal:2',
            'price_per_user' => 'decimal:2',

            'included_users' => 'integer',
            'minimum_users' => 'integer',
            'maximum_users' => 'integer',

            'effective_from' => 'date',
            'effective_until' => 'date',

            'settings' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(
            Organization::class
        );
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(
            Module::class
        );
    }
}
