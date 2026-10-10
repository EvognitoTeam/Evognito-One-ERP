<?php

namespace App\Modules\Sales\Models;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SalesProduct extends Model
{
    use SoftDeletes;

    protected $table =
        'sales_products';

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

    public function visitSchedules(): BelongsToMany
    {
        return $this->belongsToMany(
            SalesVisitSchedule::class,
            'sales_visit_schedule_products',
            'product_id',
            'visit_schedule_id'
        )
            ->withPivot(
                'organization_id'
            )
            ->withTimestamps();
    }
}
