<?php

namespace App\Modules\Performance\Models;

use App\Models\Organization;
use App\Modules\Performance\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceKpi extends Model
{
    use HasUlid, SoftDeletes;

    protected $guarded = ['id'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function productAssignments(): HasMany
    {
        return $this->hasMany(PerformanceKpiProductAssignment::class, 'kpi_id');
    }
}
