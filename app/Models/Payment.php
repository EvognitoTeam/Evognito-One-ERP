<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $fillable = [
        'ulid',
        'payment_number',

        'organization_id',
        'invoice_id',

        'provider',
        'method',
        'status',

        'amount',
        'currency',

        'reference',
        'provider_reference',

        'paid_at',

        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (
            Payment $payment
        ): void {
            if (!$payment->ulid) {
                $payment->ulid =
                    (string) Str::ulid();
            }

            if (!$payment->payment_number) {
                $payment->payment_number =
                    'PAY-'
                    . now()->format('Ym')
                    . '-'
                    . strtoupper(
                        substr(
                            $payment->ulid,
                            -8
                        )
                    );
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(
            Organization::class
        );
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(
            Invoice::class
        );
    }
}
