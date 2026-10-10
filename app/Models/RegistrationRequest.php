<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationRequest extends Model
{
    protected $fillable = [
        'ulid',
        'source',
        'invite_id',

        'organization_name',
        'organization_type',

        'owner_name',
        'email',
        'phone',

        'password_hash',

        'module_ids',
        'pricing_snapshot',

        'status',

        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'completed_at',

        'rejection_reason',

        'created_organization_id',
        'created_user_id',

        'metadata',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'module_ids' => 'array',
            'pricing_snapshot' => 'array',
            'metadata' => 'array',

            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function invite(): BelongsTo
    {
        return $this->belongsTo(
            RegistrationInvite::class,
            'invite_id'
        );
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(
            Organization::class,
            'created_organization_id'
        );
    }

    public function createdUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_user_id'
        );
    }
}
