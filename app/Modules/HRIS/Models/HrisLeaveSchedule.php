<?php

namespace App\Modules\HRIS\Models;

use App\Models\Employee;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class HrisLeaveSchedule extends Model
{
    use SoftDeletes;

    protected $table =
    'hris_leave_schedules';

    protected $fillable = [
        'ulid',

        'organization_id',
        'employee_id',

        'start_date',
        'end_date',

        'duration_days',

        'title',
        'notes',

        'status',

        'quota_applied',

        'source',

        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',

        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' =>
            'date',

            'end_date' =>
            'date',

            'duration_days' =>
            'integer',

            'quota_applied' =>
            'boolean',

            'cancelled_at' =>
            'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (
                HrisLeaveSchedule $schedule
            ) {
                if (
                    blank($schedule->ulid)
                ) {
                    $schedule->ulid =
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cancelled_by'
        );
    }
}
