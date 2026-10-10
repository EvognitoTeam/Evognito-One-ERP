<?php

namespace App\Modules\HRIS\Models;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class HrisAttendancePolicy extends Model
{
    protected $table =
    'hris_attendance_policies';

    protected $fillable = [
        'ulid',

        'organization_id',

        'work_days',

        'require_location',

        'require_photo_clock_in',
        'require_photo_clock_out',

        'maximum_gps_accuracy_meters',

        'geofence_mode',

        'clock_in_before_minutes',
        'clock_in_after_minutes',

        'allow_early_clock_out',

        'allow_attendance_on_holidays',

        'allow_attendance_on_non_workdays',

        'allow_manual_adjustment',

        'settings',
    ];

    protected function casts(): array
    {
        return [
            'work_days' =>
            'array',

            'require_location' =>
            'boolean',

            'require_photo_clock_in' =>
            'boolean',

            'require_photo_clock_out' =>
            'boolean',

            'maximum_gps_accuracy_meters' =>
            'integer',

            'clock_in_before_minutes' =>
            'integer',

            'clock_in_after_minutes' =>
            'integer',

            'allow_early_clock_out' =>
            'boolean',

            'allow_attendance_on_holidays' =>
            'boolean',

            'allow_attendance_on_non_workdays' =>
            'boolean',

            'allow_manual_adjustment' =>
            'boolean',

            'settings' =>
            'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (
                HrisAttendancePolicy $policy
            ) {
                if (
                    blank($policy->ulid)
                ) {
                    $policy->ulid =
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

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */

    public static function defaults(): array
    {
        return [
            'work_days' => [
                1,
                2,
                3,
                4,
                5,
            ],

            'require_location' =>
            true,

            'require_photo_clock_in' =>
            true,

            'require_photo_clock_out' =>
            true,

            'maximum_gps_accuracy_meters' =>
            100,

            'geofence_mode' =>
            'mobile_remote',

            'clock_in_before_minutes' =>
            120,

            'clock_in_after_minutes' =>
            240,

            'allow_early_clock_out' =>
            true,

            'allow_attendance_on_holidays' =>
            false,

            'allow_attendance_on_non_workdays' =>
            false,

            'allow_manual_adjustment' =>
            true,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Policy
    |--------------------------------------------------------------------------
    |
    | Tidak membuat row otomatis saat GET.
    |
    */

    public static function resolveFor(
        Organization $organization
    ): self {
        $policy =
            static::query()
            ->where(
                'organization_id',
                $organization->id
            )
            ->first();

        if ($policy) {
            return $policy;
        }

        $policy =
            new static(
                static::defaults()
            );

        $policy->organization_id =
            $organization->id;

        return $policy;
    }
}
