<?php

namespace App\Modules\Performance\Models;

use App\Models\Employee;
use App\Models\Organization;
use App\Modules\Performance\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceProduct extends Model
{
    use HasUlid, SoftDeletes;

    protected $guarded = ['id'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'performance_employee_products', 'product_id', 'employee_id')
            ->withPivot(['organization_id', 'is_primary', 'effective_from', 'effective_until', 'is_active'])
            ->withTimestamps();
    }
    public function kpiAssignments(): HasMany
    {
        return $this->hasMany(PerformanceKpiProductAssignment::class, 'product_id');
    }
}
