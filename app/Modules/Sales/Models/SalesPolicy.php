<?php

namespace App\Modules\Sales\Models;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SalesPolicy extends Model
{
    protected $table = 'sales_policies';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'visit_geofence_required' => 'boolean',
            'default_customer_radius_meters' => 'integer',
            'allow_outside_geofence' => 'boolean',
            'visit_photo_required' => 'boolean',
            'receipt_upload_required' => 'boolean',
            'tracking_enabled' => 'boolean',
            'tracking_moving_interval_seconds' => 'integer',
            'tracking_active_interval_seconds' => 'integer',
            'tracking_idle_interval_seconds' => 'integer',
            'tracking_min_distance_meters' => 'integer',
            'tracking_accuracy_limit_meters' => 'integer',
            'tracking_retention_days' => 'integer',
            'prevent_visit_after_work_end' => 'boolean',
            'require_daily_attendance_before_visit' => 'boolean',
            'settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }
        });
    }

    public static function resolveFor(Organization $organization): self
    {
        return static::firstOrCreate(
            ['organization_id' => $organization->id],
            ['ulid' => (string) Str::ulid()]
        );
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
