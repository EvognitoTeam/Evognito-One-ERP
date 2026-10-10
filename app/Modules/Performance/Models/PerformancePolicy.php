<?php

namespace App\Modules\Performance\Models;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformancePolicy extends Model
{
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return [
            'competency_weight' => 'decimal:2', 'kpi_weight' => 'decimal:2',
            'require_level1_approval' => 'boolean', 'require_level2_approval' => 'boolean',
            'require_employee_acknowledgement' => 'boolean', 'settings' => 'array',
        ];
    }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public static function resolveFor(Organization $organization): self
    {
        return static::firstOrCreate(
            ['organization_id' => $organization->id],
            [
                'competency_weight' => 40,
                'kpi_weight' => 60,
                'require_level1_approval' => true,
                'require_level2_approval' => true,
                'require_employee_acknowledgement' => true,
            ]
        );
    }
}
