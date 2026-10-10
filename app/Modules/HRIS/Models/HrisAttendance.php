<?php

namespace App\Modules\HRIS\Models;

use App\Models\Employee;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class HrisAttendance extends Model
{
    use SoftDeletes;

    protected $table = 'hris_attendances';

    protected $fillable = [
        'ulid',
        'organization_id',
        'employee_id',
        'work_location_id',

        'attendance_date',

        'clock_in',
        'clock_out',

        'scheduled_clock_in',
        'scheduled_clock_out',

        'status',
        'is_late',
        'late_minutes',
        'work_minutes',

        'method',

        'photo_in',
        'photo_out',

        'clock_in_latitude',
        'clock_in_longitude',
        'clock_in_accuracy_meters',
        'clock_in_distance_meters',
        'clock_in_within_geofence',

        'clock_out_latitude',
        'clock_out_longitude',
        'clock_out_accuracy_meters',
        'clock_out_distance_meters',
        'clock_out_within_geofence',

        'notes',
        'metadata',

        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',

            'clock_in' => 'datetime',
            'clock_out' => 'datetime',

            'is_late' => 'boolean',
            'late_minutes' => 'integer',
            'work_minutes' => 'integer',

            'clock_in_latitude' => 'decimal:7',
            'clock_in_longitude' => 'decimal:7',
            'clock_in_accuracy_meters' => 'decimal:2',
            'clock_in_distance_meters' => 'decimal:2',
            'clock_in_within_geofence' => 'boolean',

            'clock_out_latitude' => 'decimal:7',
            'clock_out_longitude' => 'decimal:7',
            'clock_out_accuracy_meters' => 'decimal:2',
            'clock_out_distance_meters' => 'decimal:2',
            'clock_out_within_geofence' => 'boolean',

            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (HrisAttendance $attendance) {
            if (blank($attendance->ulid)) {
                $attendance->ulid = (string) Str::ulid();
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function workLocation(): BelongsTo
    {
        return $this->belongsTo(
            HrisWorkLocation::class,
            'work_location_id'
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
