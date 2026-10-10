<?php

namespace App\Modules\Sales\Models;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SalesCustomer extends Model
{
    use SoftDeletes;

    protected $table =
        'sales_customers';

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'geofence_radius_meters' => 'integer',
            'geofence_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (
                self $model
            ) {
                if (
                    empty(
                        $model->ulid
                    )
                ) {
                    $model->ulid =
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

    public function area(): BelongsTo
    {
        return $this->belongsTo(
            SalesArea::class,
            'area_id'
        );
    }

    public function visitSchedules(): HasMany
    {
        return $this->hasMany(
            SalesVisitSchedule::class,
            'customer_id'
        );
    }
}
