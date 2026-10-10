<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class RegistrationInvite extends Model
{
    protected $fillable = [
        'ulid',
        'token_hash',
        'token_encrypted',
        'token_fingerprint',

        'organization_name',
        'organization_type',

        'contact_name',
        'contact_email',
        'contact_phone',

        'locked_fields',

        'module_ids',
        'pricing_snapshot',

        'expires_at',
        'used_at',
        'revoked_at',

        'created_by',

        'used_organization_id',
        'used_by_user_id',
    ];

    protected $hidden = [
        'token_hash',
        'token_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'locked_fields' => 'array',
            'module_ids' => 'array',
            'pricing_snapshot' => 'array',

            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function usedOrganization(): BelongsTo
    {
        return $this->belongsTo(
            Organization::class,
            'used_organization_id'
        );
    }

    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'used_by_user_id'
        );
    }

    public function plainToken(): ?string
    {
        try {
            return Crypt::decryptString(
                $this->token_encrypted
            );
        } catch (\Throwable) {
            return null;
        }
    }

    public function registrationUrl(): ?string
    {
        $token = $this->plainToken();

        if (!$token) {
            return null;
        }

        return route(
            'registration.invite.show',
            $token
        );
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsable(): bool
    {
        return !$this->isUsed()
            && !$this->isRevoked()
            && !$this->isExpired();
    }

    public function statusLabel(): string
    {
        if ($this->isUsed()) {
            return 'Used';
        }

        if ($this->isRevoked()) {
            return 'Revoked';
        }

        if ($this->isExpired()) {
            return 'Expired';
        }

        return 'Active';
    }
}
