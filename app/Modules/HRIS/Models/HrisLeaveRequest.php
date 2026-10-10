<?php

namespace App\Modules\HRIS\Models;

use App\Models\Employee;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class HrisLeaveRequest extends Model
{
    use SoftDeletes;

    protected $table =
    'hris_leave_requests';

    protected $fillable = [
        'ulid',

        'organization_id',
        'employee_id',

        'start_date',
        'end_date',

        'start_time',
        'end_time',

        'duration_days',

        'type',
        'detail_type',

        'reason',

        'emergency_name',
        'emergency_address',
        'emergency_phone',

        'attachment_path',
        'attachment_original_name',

        'status',

        'approved_by',
        'approved_at',
        'rejected_at',

        'approval_notes',

        'quota_applied',

        'source',

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

            'approved_at' =>
            'datetime',

            'rejected_at' =>
            'datetime',

            'quota_applied' =>
            'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (
                HrisLeaveRequest $leave
            ) {
                if (
                    blank($leave->ulid)
                ) {
                    $leave->ulid =
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
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
}
