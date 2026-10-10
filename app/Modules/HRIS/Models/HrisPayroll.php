<?php

namespace App\Modules\HRIS\Models;

use App\Models\Employee;
use App\Models\Organization;
use App\Models\User;
use App\Modules\HRIS\Models\HrisPayrollItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class HrisPayroll extends Model
{
    protected $table =
    'hris_payrolls';

    protected $fillable = [
        'ulid',

        'organization_id',
        'payroll_period_id',
        'employee_id',

        'employee_number',
        'employee_name',

        'position_name',
        'department_name',
        'branch_name',

        'scheduled_days',
        'present_days',

        'leave_days',
        'sick_days',
        'permission_days',
        'absent_days',

        'late_minutes',

        'basic_salary',
        'fixed_allowance',

        'daily_bonus_rate',
        'daily_bonus_total',

        'annual_bonus',

        'late_deduction_rate',
        'late_deduction_total',

        'absence_deduction_rate',
        'absence_deduction_total',

        'other_fixed_deduction',

        'gross_salary',
        'total_deductions',
        'net_salary',

        'currency',

        'status',

        'paid_at',
        'paid_by',

        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_days' =>
            'integer',

            'present_days' =>
            'integer',

            'leave_days' =>
            'integer',

            'sick_days' =>
            'integer',

            'permission_days' =>
            'integer',

            'absent_days' =>
            'integer',

            'late_minutes' =>
            'integer',

            'basic_salary' =>
            'decimal:2',

            'fixed_allowance' =>
            'decimal:2',

            'daily_bonus_rate' =>
            'decimal:2',

            'daily_bonus_total' =>
            'decimal:2',

            'annual_bonus' =>
            'decimal:2',

            'late_deduction_rate' =>
            'decimal:2',

            'late_deduction_total' =>
            'decimal:2',

            'absence_deduction_rate' =>
            'decimal:2',

            'absence_deduction_total' =>
            'decimal:2',

            'other_fixed_deduction' =>
            'decimal:2',

            'gross_salary' =>
            'decimal:2',

            'total_deductions' =>
            'decimal:2',

            'net_salary' =>
            'decimal:2',

            'paid_at' =>
            'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (
                HrisPayroll $payroll
            ) {
                if (
                    blank($payroll->ulid)
                ) {
                    $payroll->ulid =
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

    public function period(): BelongsTo
    {
        return $this->belongsTo(
            HrisPayrollPeriod::class,
            'payroll_period_id'
        );
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            HrisPayrollItem::class,
            'payroll_id'
        );
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'paid_by'
        );
    }
}
