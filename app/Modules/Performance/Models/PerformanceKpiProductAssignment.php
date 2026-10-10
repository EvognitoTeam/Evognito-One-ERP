<?php

namespace App\Modules\Performance\Models;

use App\Models\Organization;
use App\Modules\Performance\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceKpiProductAssignment extends Model
{
    use HasUlid;

    protected $guarded = ['id'];
    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2', 'target' => 'decimal:2', 'target_time' => 'decimal:2',
            'quantity_weight' => 'decimal:2', 'time_weight' => 'decimal:2',
            'max_achievement' => 'decimal:2', 'is_active' => 'boolean',
        ];
    }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function kpi(): BelongsTo { return $this->belongsTo(PerformanceKpi::class, 'kpi_id'); }
    public function product(): BelongsTo { return $this->belongsTo(PerformanceProduct::class, 'product_id'); }
}
