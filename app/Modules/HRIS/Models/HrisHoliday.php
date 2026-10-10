<?php

namespace App\Modules\HRIS\Models;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class HrisHoliday extends Model
{
    use SoftDeletes;

    protected $table =
    'hris_holidays';

    protected $fillable = [
        'ulid',

        'organization_id',

        'holiday_date',
        'end_date',

        'name',
        'color',

        'is_recurring',
        'description',

        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'holiday_date' =>
            'date',

            'end_date' =>
            'date',

            'is_recurring' =>
            'boolean',

            'is_active' =>
            'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (
                HrisHoliday $holiday
            ) {
                if (
                    blank($holiday->ulid)
                ) {
                    $holiday->ulid =
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
}
