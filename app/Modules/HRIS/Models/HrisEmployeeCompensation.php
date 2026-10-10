<?php

namespace App\Modules\HRIS\Models;

use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class HrisEmployeeCompensation extends Model
{
    protected $table =
    'hris_employee_compensations';

    protected $fillable = [
        'ulid',

        'organization_id',
        'employee_id',

        'basic_salary',
        'fixed_allowance',

        'daily_bonus',
        'annual_bonus',

        'late_deduction_per_minute',
        'absence_deduction_per_day',

        'other_fixed_deduction',

        'currency',

        'effective_from',

        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' =>
            'decimal:2',

            'fixed_allowance' =>
            'decimal:2',

            'daily_bonus' =>
            'decimal:2',

            'annual_bonus' =>
            'decimal:2',

            'late_deduction_per_minute' =>
            'decimal:2',

            'absence_deduction_per_day' =>
            'decimal:2',

            'other_fixed_deduction' =>
            'decimal:2',

            'effective_from' =>
            'date',

            'is_active' =>
            'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (
                HrisEmployeeCompensation $compensation
            ) {
                if (
                    blank(
                        $compensation->ulid
                    )
                ) {
                    $compensation->ulid =
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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class
        );
    }
}
