<?php

namespace App\Core\Imports\Models;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterDataImportRow extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'source_row_number' => 'integer',
            'source_no' => 'integer',
            'product_names' => 'array',
            'product_ids' => 'array',
            'join_date' => 'date',
            'birth_date' => 'date',
            'raw_data' => 'array',
            'warnings' => 'array',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(MasterDataImport::class, 'import_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
