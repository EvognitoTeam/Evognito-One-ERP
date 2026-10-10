<?php

namespace App\Core\Imports\Services;

use App\Core\Imports\Models\MasterDataImport;
use Illuminate\Support\Collection;

class StagedEmployeeImportResolver
{
    /**
     * This service is intentionally reusable by the later Core/HRIS employee importer.
     * Recommended flow:
     * 1) create/update all employees from staged rows;
     * 2) build normalized-name map from created employees;
     * 3) resolve supervisor level 1 and 2 in a second pass;
     * 4) attach product_ids from staged rows to employee products.
     */
    public function rows(
        MasterDataImport $import
    ): Collection {
        return $import->rows()
            ->orderBy('source_row_number')
            ->get();
    }

    public function duplicateNameGroups(
        MasterDataImport $import
    ): Collection {
        return $import->rows()
            ->selectRaw('employee_name_normalized, COUNT(*) as total')
            ->whereNotNull('employee_name_normalized')
            ->groupBy('employee_name_normalized')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('total', 'employee_name_normalized');
    }
}
