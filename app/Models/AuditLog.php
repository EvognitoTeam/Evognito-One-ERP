<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ulid',

        'organization_id',
        'user_id',

        'event',
        'action',

        'subject_type',
        'subject_id',

        'description',

        'route',
        'method',

        'ip_address',
        'user_agent',

        'metadata',

        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' =>
            'array',

            'created_at' =>
            'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (
            AuditLog $log
        ): void {
            if (!$log->ulid) {
                $log->ulid =
                    (string) Str::ulid();
            }

            if (!$log->created_at) {
                $log->created_at =
                    now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(
            Organization::class
        );
    }
}
