<?php

namespace App\Modules\HRIS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class HrisPayrollItem extends Model
{
    protected $table =
    'hris_payroll_items';

    protected $fillable = [
        'ulid',

        'payroll_id',

        'type',
        'code',

        'name',
        'amount',

        'is_system',

        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' =>
            'decimal:2',

            'is_system' =>
            'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (
                HrisPayrollItem $item
            ) {
                if (
                    blank($item->ulid)
                ) {
                    $item->ulid =
                        (string) Str::ulid();
                }
            }
        );
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(
            HrisPayroll::class,
            'payroll_id'
        );
    }
}
