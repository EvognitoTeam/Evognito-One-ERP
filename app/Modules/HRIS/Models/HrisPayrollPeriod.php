<?php

namespace App\Modules\HRIS\Models;

use App\Models\Organization;
use App\Models\User;
use App\Modules\HRIS\Models\HrisPayroll;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class HrisPayrollPeriod extends Model
{
    protected $table =
    'hris_payroll_periods';

    protected $fillable = [
        'ulid',

        'organization_id',

        'name',

        'period_start',
        'period_end',

        'pay_date',

        'status',

        'notes',

        'generated_at',
        'generated_by',

        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' =>
            'date',

            'period_end' =>
            'date',

            'pay_date' =>
            'date',

            'generated_at' =>
            'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (
                HrisPayrollPeriod $period
            ) {
                if (
                    blank($period->ulid)
                ) {
                    $period->ulid =
                        (string) Str::ulid();
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

    public function payrolls(): HasMany
    {
        return $this->hasMany(
            HrisPayroll::class,
            'payroll_period_id'
        );
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'generated_by'
        );
    }
}
