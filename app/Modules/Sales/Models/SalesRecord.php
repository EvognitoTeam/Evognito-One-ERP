<?php

namespace App\Modules\Sales\Models;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SalesRecord extends Model
{
    use SoftDeletes;

    protected $table = 'sales_records';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'unit_1' => 'decimal:4',
            'quantity' => 'decimal:4',
            'ratio_2' => 'decimal:4',
            'customer_tier_price' => 'decimal:2',
            'pl_price' => 'decimal:2',
            'gross_amount' => 'decimal:2',
            'sales_dpp' => 'decimal:2',
            'liters' => 'decimal:4',
            'owing' => 'decimal:2',
            'raw_payload' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
