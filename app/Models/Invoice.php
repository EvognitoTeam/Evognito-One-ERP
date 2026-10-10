<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    protected $fillable = [
        'ulid',
        'invoice_number',

        'organization_id',
        'organization_subscription_id',

        'source',
        'title',

        'period_start',
        'period_end',

        'issued_at',
        'due_at',

        'currency',
        'status',

        'subtotal',
        'additional_total',
        'discount_total',
        'tax_total',
        'total',

        'paid_at',

        'notes',
        'metadata',

        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' =>
            'date',

            'period_end' =>
            'date',

            'issued_at' =>
            'datetime',

            'due_at' =>
            'datetime',

            'paid_at' =>
            'datetime',

            'subtotal' =>
            'decimal:2',

            'additional_total' =>
            'decimal:2',

            'discount_total' =>
            'decimal:2',

            'tax_total' =>
            'decimal:2',

            'total' =>
            'decimal:2',

            'metadata' =>
            'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (
                Invoice $invoice
            ): void {
                if (!$invoice->ulid) {
                    $invoice->ulid =
                        (string) Str::ulid();
                }

                if (
                    !$invoice
                        ->invoice_number
                ) {
                    $invoice
                        ->invoice_number =
                        'EVO-'
                        . now()->format(
                            'Ym'
                        )
                        . '-'
                        . strtoupper(
                            substr(
                                $invoice->ulid,
                                -8
                            )
                        );
                }
            }
        );
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(
            Organization::class
        );
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(
            OrganizationSubscription::class,
            'organization_subscription_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            InvoiceItem::class
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            Payment::class
        );
    }

    public function getPaidAmountAttribute(): float
    {
        if (
            $this->relationLoaded(
                'payments'
            )
        ) {
            return (float) $this
                ->payments
                ->where(
                    'status',
                    'paid'
                )
                ->sum('amount');
        }

        return (float) $this
            ->payments()
            ->where(
                'status',
                'paid'
            )
            ->sum('amount');
    }

    public function getOutstandingAmountAttribute(): float
    {
        return max(
            0,
            (float) $this->total
                - $this->paid_amount
        );
    }

    public function isManual(): bool
    {
        return $this->source
            === 'manual';
    }

    public function isSubscription(): bool
    {
        return $this->source
            === 'subscription';
    }
}
