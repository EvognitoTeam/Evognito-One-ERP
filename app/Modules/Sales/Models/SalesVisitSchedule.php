<?php

namespace App\Modules\Sales\Models;

use App\Models\Employee;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SalesVisitSchedule extends Model
{
    use SoftDeletes;

    protected $table =
        'sales_visit_schedules';

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            SalesCustomer::class,
            'customer_id'
        );
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(
            SalesArea::class,
            'area_id'
        );
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            SalesProduct::class,
            'sales_visit_schedule_products',
            'visit_schedule_id',
            'product_id'
        )
            ->withPivot(
                'organization_id'
            )
            ->withTimestamps();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }
    public function attendance(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(
            SalesVisitAttendance::class,
            'visit_schedule_id'
        );
    }

    public function receipts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            SalesReceipt::class,
            'visit_schedule_id'
        );
    }

    public function tracks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            SalesLocationTrack::class,
            'visit_schedule_id'
        );
    }

}
