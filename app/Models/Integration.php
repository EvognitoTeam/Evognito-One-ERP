<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Integration extends Model
{
    protected $fillable = [
        'ulid',

        'code',
        'name',

        'category',
        'provider',

        'environment',
        'status',

        'base_url',
        'webhook_url',

        'config',
        'credentials',

        'last_checked_at',
        'last_check_status',
        'last_check_message',

        'notes',

        'created_by',
    ];

    protected $hidden = [
        'credentials',
    ];

    protected function casts(): array
    {
        return [
            'config' =>
            'array',

            /*
             * Laravel akan mengenkripsi/dekripsi
             * credentials menggunakan APP_KEY.
             */
            'credentials' =>
            'encrypted:array',

            'last_checked_at' =>
            'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (
            Integration $integration
        ): void {
            if (!$integration->ulid) {
                $integration->ulid =
                    (string) Str::ulid();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function hasCredentials(): bool
    {
        return !empty($this->credentials);
    }

    public function credentialKeys(): array
    {
        return array_keys(
            $this->credentials ?? []
        );
    }
}
