<?php

namespace App\Modules\Sales\Models;

use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesLocationTrack extends Model
{
    protected $table = 'sales_location_tracks';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'accuracy_meters' => 'decimal:2',
            'speed_mps' => 'decimal:2',
            'heading_degrees' => 'decimal:2',
            'battery_level' => 'integer',
            'is_moving' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function visitSchedule(): BelongsTo
    {
        return $this->belongsTo(SalesVisitSchedule::class, 'visit_schedule_id');
    }
}
