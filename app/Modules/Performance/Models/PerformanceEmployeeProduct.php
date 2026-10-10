<?php

namespace App\Modules\Performance\Models;

use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceEmployeeProduct extends Model
{
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function product(): BelongsTo { return $this->belongsTo(PerformanceProduct::class); }
}
