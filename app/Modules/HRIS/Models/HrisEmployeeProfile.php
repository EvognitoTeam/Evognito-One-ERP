<?php

namespace App\Modules\HRIS\Models;

use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class HrisEmployeeProfile extends Model
{
    protected $table =
    'hris_employee_profiles';

    protected $fillable = [
        'ulid',

        'organization_id',
        'employee_id',

        'supervisor_employee_id',

        'employment_type',

        'join_date',
        'end_date',

        'leave_quota',
        'debt_minutes',

        'notes',
    ];

    protected function casts(): array
    {
        return [
            'join_date' =>
            'date',

            'end_date' =>
            'date',

            'leave_quota' =>
            'integer',

            'debt_minutes' =>
            'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (
                HrisEmployeeProfile $profile
            ) {
                if (
                    blank(
                        $profile->ulid
                    )
                ) {
                    $profile->ulid =
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

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            'supervisor_employee_id'
        );
    }

    public function getDebtHoursAttribute(): float
    {
        return round(
            $this->debt_minutes / 60,
            2
        );
    }
}
