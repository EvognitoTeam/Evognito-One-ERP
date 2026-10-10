<?php

namespace App\Modules\Sales\Models;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SalesArea extends Model
{
    protected $table = 'sales_areas';

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
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

    public function customers(): HasMany
    {
        return $this->hasMany(
            SalesCustomer::class,
            'area_id'
        );
    }

    public function employeeProfiles(): HasMany
    {
        return $this->hasMany(
            SalesEmployeeProfile::class,
            'area_id'
        );
    }
}
