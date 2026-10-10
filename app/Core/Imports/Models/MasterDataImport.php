<?php

namespace App\Core\Imports\Models;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MasterDataImport extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'summary' => 'array',
            'total_source_rows' => 'integer',
            'valid_employee_rows' => 'integer',
            'blank_rows' => 'integer',
            'products_found' => 'integer',
            'products_created' => 'integer',
            'rows_with_product' => 'integer',
            'rows_without_product' => 'integer',
            'unresolved_supervisor_level1' => 'integer',
            'unresolved_supervisor_level2' => 'integer',
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

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(MasterDataImportRow::class, 'import_id');
    }
}
