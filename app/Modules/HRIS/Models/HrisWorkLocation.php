<?php

namespace App\Modules\HRIS\Models;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class HrisWorkLocation extends Model
{
    protected $table = 'hris_work_locations';

    protected $fillable = [
        'ulid',
        'organization_id',
        'branch_id',

        'name',
        'type',
        'address',

        'latitude',
        'longitude',
        'radius_meters',

        'geofence_enabled',
        'is_active',

        'notes',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'radius_meters' => 'integer',
            'geofence_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (HrisWorkLocation $location) {
            if (blank($location->ulid)) {
                $location->ulid = (string) Str::ulid();
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(
            Employee::class,
            'hris_employee_work_locations',
            'work_location_id',
            'employee_id'
        )
            ->withPivot([
                'organization_id',
                'is_primary',
            ])
            ->withTimestamps();
    }
}
