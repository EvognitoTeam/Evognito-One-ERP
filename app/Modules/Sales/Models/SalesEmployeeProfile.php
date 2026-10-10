<?php

namespace App\Modules\Sales\Models;

use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SalesEmployeeProfile extends Model
{
    protected $table =
        'sales_employee_profiles';

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'is_field_sales' => 'boolean',
            'is_active' => 'boolean',
            'daily_visit_target' => 'integer',
            'settings' => 'array',
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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class
        );
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            'supervisor_employee_id'
        );
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(
            SalesArea::class,
            'area_id'
        );
    }
}
