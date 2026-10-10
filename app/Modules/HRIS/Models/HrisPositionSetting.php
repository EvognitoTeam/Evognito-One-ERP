<?php

namespace App\Modules\HRIS\Models;

use App\Models\Organization;
use App\Models\Position;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class HrisPositionSetting extends Model
{
    protected $table =
    'hris_position_settings';

    protected $fillable = [
        'ulid',

        'organization_id',
        'position_id',

        'clock_in',
        'clock_out',

        'is_mobile',
        'late_tolerance_minutes',

        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_mobile' =>
            'boolean',

            'late_tolerance_minutes' =>
            'integer',

            'settings' =>
            'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (
                HrisPositionSetting $setting
            ) {
                if (
                    blank($setting->ulid)
                ) {
                    $setting->ulid =
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

    public function position(): BelongsTo
    {
        return $this->belongsTo(
            Position::class
        );
    }
}
