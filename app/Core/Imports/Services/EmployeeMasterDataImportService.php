<?php

namespace App\Core\Imports\Services;

use App\Core\Imports\Models\MasterDataImport;
use App\Core\Imports\Models\MasterDataImportRow;
use App\Models\Organization;
use App\Modules\Performance\Models\PerformanceProduct;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class EmployeeMasterDataImportService
{
    private const MAX_SCAN_ROWS = 50;
    private const MAX_COLUMNS = 160;

    /**
     * Required semantic headers. Sheet name is deliberately irrelevant.
     * A worksheet is accepted only when all of these can be mapped.
     *
     * @var array<string, array<int, string>>
     */
    private const REQUIRED_HEADERS = [
        'employee_number' => [
            'nik (nomor induk karyawan)',
            'nik karyawan',
            'employee number',
            'employee no',
        ],
        'employee_name' => [
            'nama',
            'nama karyawan',
            'employee name',
        ],
        'company_name' => [
            'nama perusahaan',
            'perusahaan',
            'company name',
        ],
        'company_code' => [
            'kode perusahaan',
            'company code',
        ],
        'position_name' => [
            'jabatan',
            'position',
        ],
        'level_name' => [
            'level',
        ],
        'supervisor_level1' => [
            'atasan level 1',
            'atasan level1',
            'supervisor level 1',
        ],
        'supervisor_level2' => [
            'atasan level 2',
            'atasan level2',
            'supervisor level 2',
        ],
        'product' => [
            'produk',
            'product',
        ],
        'home_based' => [
            'home based',
            'homebase',
            'home base',
        ],
        'employment_status' => [
            'status karyawan',
            'employment status',
        ],
    ];

    /** @var array<string, array<int, string>> */
    private const OPTIONAL_HEADERS = [
        'source_no' => ['no', 'nomor'],
        'operation_type' => [
            'operation / non operation',
            'operation/non operation',
            'operation',
        ],
        'area' => ['area'],
        'assigned_companies' => [
            'mengerjakan job untuk pt mana saja?',
            'mengerjakan job untuk pt mana saja',
            'assigned companies',
        ],
        'join_date' => [
            'tgl. masuk perusahaan',
            'tgl masuk perusahaan',
            'tanggal masuk perusahaan',
            'join date',
        ],
        'tenure_text' => ['masa kerja', 'tenure'],
        'suspension_status' => ['status skorsing', 'suspension status'],
        'birth_place' => ['tempat lahir', 'birth place'],
        'birth_date' => ['tanggal lahir', 'birth date'],
        'age_text' => ['usia', 'age'],
        'gender' => ['jenis kelamin', 'gender'],
        'religion' => ['agama', 'religion'],
        'phone' => ['ponsel', 'no hp', 'nomor hp', 'phone'],
        'email' => ['e-mail', 'email'],
    ];

    public function __construct(
        private readonly NativeXlsxReader $xlsxReader
    ) {
    }

    public function import(
        Organization $organization,
        UploadedFile $file,
        ?int $uploadedBy = null
    ): MasterDataImport {
        $import = MasterDataImport::create([
            'organization_id' => $organization->id,
            'type' => 'employee_master',
            'original_filename' => $file->getClientOriginalName(),
            'sheet_name' => null,
            'file_hash' => hash_file('sha256', $file->getRealPath()),
            'status' => 'processing',
            'uploaded_by' => $uploadedBy,
        ]);

        try {
            $match = $this->detectSourceSheet($file->getRealPath());
            $sheetName = $match['sheet_name'];
            $headerRowNumber = $match['header_row'];
            $columnMap = $match['column_map'];

            $rows = $this->xlsxReader->readSheet(
                $file->getRealPath(),
                $sheetName,
                self::MAX_COLUMNS
            );

            $headers = $rows[$headerRowNumber] ?? [];

            $existingProducts = PerformanceProduct::query()
                ->where('organization_id', $organization->id)
                ->get();

            $productMap = [];

            foreach ($existingProducts as $product) {
                $productMap[$this->normalizeKey($product->name)] = $product;
            }

            $sourceNameMap = $this->buildSourceNameMap(
                $rows,
                $headerRowNumber,
                $columnMap['employee_name']
            );

            $summary = [
                'source_sheet' => $sheetName,
                'header_row' => $headerRowNumber,
                'sheet_detection' => 'header-based',
                'matched_required_headers' => array_keys(self::REQUIRED_HEADERS),
                'matched_optional_headers' => $match['matched_optional_headers'],
                'scanned_sheets' => $match['scanned_sheets'],
                'column_map' => $columnMap,
                'product_alias_policy' => 'case-and-space-only',
                'supervisor_resolution_policy' => 'staged-normalized-name',
                'raw_columns_preserved' => self::MAX_COLUMNS,
            ];

            $totalSourceRows = 0;
            $validEmployeeRows = 0;
            $blankRows = 0;
            $productsCreated = 0;
            $rowsWithProduct = 0;
            $rowsWithoutProduct = 0;
            $unresolvedLevel1 = 0;
            $unresolvedLevel2 = 0;
            $productsFound = [];
            $insertRows = [];
            $now = now();

            DB::beginTransaction();

            foreach ($rows as $rowNumber => $row) {
                if ($rowNumber <= $headerRowNumber) {
                    continue;
                }

                $totalSourceRows++;

                $employeeNumber = $this->value($row, $columnMap, 'employee_number');
                $employeeName = $this->value($row, $columnMap, 'employee_name');

                if (!$employeeNumber && !$employeeName) {
                    $blankRows++;
                    continue;
                }

                $validEmployeeRows++;

                $productRaw = $this->value($row, $columnMap, 'product');
                $productNames = [];
                $productIds = [];

                if ($productRaw) {
                    $rowsWithProduct++;

                    foreach ($this->splitProducts($productRaw) as $productName) {
                        $key = $this->normalizeKey($productName);

                        if ($key === '') {
                            continue;
                        }

                        if (!isset($productMap[$key])) {
                            $productMap[$key] = PerformanceProduct::create([
                                'organization_id' => $organization->id,
                                'code' => null,
                                'name' => $productName,
                                'description' => null,
                                'is_active' => true,
                            ]);

                            $productsCreated++;
                        }

                        $product = $productMap[$key];
                        $productsFound[$key] = $product->name;
                        $productNames[] = $product->name;
                        $productIds[] = $product->id;
                    }
                } else {
                    $rowsWithoutProduct++;
                }

                $supervisorLevel1Raw = $this->value($row, $columnMap, 'supervisor_level1');
                $supervisorLevel2Raw = $this->value($row, $columnMap, 'supervisor_level2');

                $supervisorLevel1Normalized = $this->normalizePersonName($supervisorLevel1Raw);
                $supervisorLevel2Normalized = $this->normalizePersonName($supervisorLevel2Raw);

                $warnings = [];

                if ($supervisorLevel1Raw && !$supervisorLevel1Normalized) {
                    $warnings[] = 'supervisor_level1_not_linkable';
                } elseif (
                    $supervisorLevel1Normalized
                    && !isset($sourceNameMap[$supervisorLevel1Normalized])
                ) {
                    $warnings[] = 'supervisor_level1_not_found_in_source';
                    $unresolvedLevel1++;
                }

                if ($supervisorLevel2Raw && !$supervisorLevel2Normalized) {
                    $warnings[] = 'supervisor_level2_not_linkable';
                } elseif (
                    $supervisorLevel2Normalized
                    && !isset($sourceNameMap[$supervisorLevel2Normalized])
                ) {
                    $warnings[] = 'supervisor_level2_not_found_in_source';
                    $unresolvedLevel2++;
                }

                $insertRows[] = [
                    'import_id' => $import->id,
                    'organization_id' => $organization->id,
                    'source_row_number' => $rowNumber,
                    'source_no' => $this->nullableInteger(
                        $this->rawValue($row, $columnMap, 'source_no')
                    ),
                    'employee_number' => $employeeNumber,
                    'employee_name' => $employeeName,
                    'employee_name_normalized' => $this->normalizePersonName($employeeName),
                    'company_name' => $this->value($row, $columnMap, 'company_name'),
                    'company_code' => $this->value($row, $columnMap, 'company_code'),
                    'position_name' => $this->value($row, $columnMap, 'position_name'),
                    'operation_type' => $this->value($row, $columnMap, 'operation_type'),
                    'level_name' => $this->value($row, $columnMap, 'level_name'),
                    'supervisor_level1_raw' => $supervisorLevel1Raw,
                    'supervisor_level1_normalized' => $supervisorLevel1Normalized,
                    'supervisor_level2_raw' => $supervisorLevel2Raw,
                    'supervisor_level2_normalized' => $supervisorLevel2Normalized,
                    'product_raw' => $productRaw,
                    'product_names' => json_encode(array_values(array_unique($productNames)), JSON_UNESCAPED_UNICODE),
                    'product_ids' => json_encode(array_values(array_unique($productIds)), JSON_UNESCAPED_UNICODE),
                    'home_based' => $this->value($row, $columnMap, 'home_based'),
                    'area' => $this->value($row, $columnMap, 'area'),
                    'assigned_companies' => $this->value($row, $columnMap, 'assigned_companies'),
                    'join_date' => $this->excelDate($this->rawValue($row, $columnMap, 'join_date')),
                    'tenure_text' => $this->value($row, $columnMap, 'tenure_text'),
                    'employment_status' => $this->value($row, $columnMap, 'employment_status'),
                    'suspension_status' => $this->value($row, $columnMap, 'suspension_status'),
                    'birth_place' => $this->value($row, $columnMap, 'birth_place'),
                    'birth_date' => $this->excelDate($this->rawValue($row, $columnMap, 'birth_date')),
                    'age_text' => $this->value($row, $columnMap, 'age_text'),
                    'gender' => $this->value($row, $columnMap, 'gender'),
                    'religion' => $this->value($row, $columnMap, 'religion'),
                    'phone' => $this->value($row, $columnMap, 'phone'),
                    'email' => $this->value($row, $columnMap, 'email'),
                    'raw_data' => json_encode($this->rawData($headers, $row), JSON_UNESCAPED_UNICODE),
                    'warnings' => json_encode($warnings, JSON_UNESCAPED_UNICODE),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($insertRows) >= 200) {
                    MasterDataImportRow::query()->insert($insertRows);
                    $insertRows = [];
                }
            }

            if ($insertRows) {
                MasterDataImportRow::query()->insert($insertRows);
            }

            $summary['products'] = array_values($productsFound);
            $summary['note'] = 'Employee master data is staged for later Core/HRIS employee, supervisor Level 1/2, and product assignment imports.';

            $import->update([
                'sheet_name' => $sheetName,
                'status' => 'completed',
                'total_source_rows' => $totalSourceRows,
                'valid_employee_rows' => $validEmployeeRows,
                'blank_rows' => $blankRows,
                'products_found' => count($productsFound),
                'products_created' => $productsCreated,
                'rows_with_product' => $rowsWithProduct,
                'rows_without_product' => $rowsWithoutProduct,
                'unresolved_supervisor_level1' => $unresolvedLevel1,
                'unresolved_supervisor_level2' => $unresolvedLevel2,
                'summary' => $summary,
            ]);

            DB::commit();

            return $import->fresh();
        } catch (Throwable $exception) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            $import->update([
                'status' => 'failed',
                'error_message' => Str::limit($exception->getMessage(), 5000, ''),
            ]);

            throw $exception;
        }
    }

    /**
     * @return array{
     *   sheet_name:string,
     *   header_row:int,
     *   column_map:array<string,int>,
     *   matched_optional_headers:array<int,string>,
     *   scanned_sheets:array<int,array<string,mixed>>
     * }
     */
    private function detectSourceSheet(string $path): array
    {
        $sheetNames = $this->xlsxReader->sheetNames($path);
        $candidates = [];
        $scannedSheets = [];

        foreach ($sheetNames as $sheetName) {
            $previewRows = $this->xlsxReader->readSheet(
                $path,
                $sheetName,
                self::MAX_COLUMNS,
                self::MAX_SCAN_ROWS
            );

            $bestForSheet = null;

            foreach ($previewRows as $rowNumber => $row) {
                $map = $this->buildColumnMap($row);
                $missing = array_values(
                    array_diff(
                        array_keys(self::REQUIRED_HEADERS),
                        array_keys($map)
                    )
                );

                $requiredMatched = count(self::REQUIRED_HEADERS) - count($missing);
                $optionalMatched = count(
                    array_intersect(
                        array_keys(self::OPTIONAL_HEADERS),
                        array_keys($map)
                    )
                );

                $score = ($requiredMatched * 100) + $optionalMatched;

                if ($bestForSheet === null || $score > $bestForSheet['score']) {
                    $bestForSheet = [
                        'row' => $rowNumber,
                        'score' => $score,
                        'required_matched' => $requiredMatched,
                        'optional_matched' => $optionalMatched,
                        'missing' => $missing,
                        'map' => $map,
                    ];
                }
            }

            $scannedSheets[] = [
                'sheet' => $sheetName,
                'best_header_row' => $bestForSheet['row'] ?? null,
                'required_matched' => $bestForSheet['required_matched'] ?? 0,
                'required_total' => count(self::REQUIRED_HEADERS),
                'optional_matched' => $bestForSheet['optional_matched'] ?? 0,
                'missing_required' => $bestForSheet['missing'] ?? array_keys(self::REQUIRED_HEADERS),
            ];

            if (
                $bestForSheet
                && empty($bestForSheet['missing'])
            ) {
                $candidates[] = [
                    'sheet_name' => $sheetName,
                    'header_row' => $bestForSheet['row'],
                    'column_map' => $bestForSheet['map'],
                    'score' => $bestForSheet['score'],
                    'matched_optional_headers' => array_values(
                        array_intersect(
                            array_keys(self::OPTIONAL_HEADERS),
                            array_keys($bestForSheet['map'])
                        )
                    ),
                ];
            }
        }

        if (!$candidates) {
            throw new RuntimeException(
                'No worksheet contains the required employee-master headers. Required headers include NIK Karyawan, Nama, Nama Perusahaan, Kode Perusahaan, Jabatan, Level, Atasan Level 1, Atasan Level 2, Produk, Home Based, and Status Karyawan.'
            );
        }

        usort(
            $candidates,
            fn (array $a, array $b) => $b['score'] <=> $a['score']
        );

        $selected = $candidates[0];
        $selected['scanned_sheets'] = $scannedSheets;

        return $selected;
    }

    /** @return array<string,int> */
    private function buildColumnMap(array $headerRow): array
    {
        $normalizedHeaders = [];

        foreach ($headerRow as $index => $header) {
            $normalized = $this->normalizeHeader($header);

            if ($normalized === '') {
                continue;
            }

            $normalizedHeaders[$index] = $normalized;
        }

        $map = [];

        foreach (
            array_merge(self::REQUIRED_HEADERS, self::OPTIONAL_HEADERS)
            as $field => $aliases
        ) {
            foreach ($normalizedHeaders as $index => $normalizedHeader) {
                foreach ($aliases as $alias) {
                    if ($normalizedHeader === $this->normalizeHeader($alias)) {
                        $map[$field] = $index;
                        break 2;
                    }
                }
            }
        }

        return $map;
    }

    /** @return array<string, array<int, int>> */
    private function buildSourceNameMap(
        array $rows,
        int $headerRowNumber,
        int $nameColumn
    ): array {
        $map = [];

        foreach ($rows as $rowNumber => $row) {
            if ($rowNumber <= $headerRowNumber) {
                continue;
            }

            $name = $this->normalizePersonName($row[$nameColumn] ?? null);

            if (!$name) {
                continue;
            }

            $map[$name] ??= [];
            $map[$name][] = $rowNumber;
        }

        return $map;
    }

    private function value(
        array $row,
        array $columnMap,
        string $field
    ): ?string {
        return $this->clean(
            $this->rawValue($row, $columnMap, $field)
        );
    }

    private function rawValue(
        array $row,
        array $columnMap,
        string $field
    ): mixed {
        if (!array_key_exists($field, $columnMap)) {
            return null;
        }

        return $row[$columnMap[$field]] ?? null;
    }

    /** @return array<string, mixed> */
    private function rawData(array $headers, array $row): array
    {
        $result = [];

        foreach ($headers as $index => $header) {
            $header = $this->clean($header);

            if (!$header) {
                $header = 'Column '.($index + 1);
            }

            if (array_key_exists($header, $result)) {
                $header .= ' #'.($index + 1);
            }

            $result[$header] = $row[$index] ?? null;
        }

        return $result;
    }

    /** @return array<int, string> */
    private function splitProducts(string $raw): array
    {
        $parts = preg_split('/\s*(?:,|&)\s*/u', $raw) ?: [];
        $result = [];

        foreach ($parts as $part) {
            $part = $this->clean($part);

            if (!$part) {
                continue;
            }

            $key = $this->normalizeKey($part);
            $result[$key] ??= $part;
        }

        return array_values($result);
    }

    private function normalizePersonName(mixed $value): ?string
    {
        $value = $this->clean($value);

        if (!$value) {
            return null;
        }

        $value = preg_replace('/\*+$/u', '', $value) ?? $value;
        $value = preg_replace('/\s*\((?:PTM|SBB|PAS|MBP)\)\s*$/iu', '', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
        $key = $this->normalizeKey($value);

        if (in_array($key, ['resign', '-', 'n/a', 'na'], true)) {
            return null;
        }

        return $key;
    }

    private function normalizeHeader(mixed $value): string
    {
        $value = $this->normalizeKey($value);
        $value = str_replace(['\n', '\r'], ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private function normalizeKey(mixed $value): string
    {
        $value = $this->clean($value) ?? '';
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return function_exists('mb_strtolower')
            ? mb_strtolower(trim($value), 'UTF-8')
            : strtolower(trim($value));
    }

    private function clean(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        $value = trim((string) $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return $value === '' ? null : $value;
    }

    private function nullableInteger(mixed $value): ?int
    {
        $value = $this->clean($value);

        if ($value === null || !is_numeric($value)) {
            return null;
        }

        return (int) round((float) $value);
    }

    private function excelDate(mixed $value): ?string
    {
        $value = $this->clean($value);

        if (!$value) {
            return null;
        }

        if (is_numeric($value)) {
            $serial = (float) $value;

            if ($serial > 0) {
                return CarbonImmutable::create(1899, 12, 30)
                    ->addDays((int) floor($serial))
                    ->toDateString();
            }
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd F Y', 'd M Y'] as $format) {
            try {
                $date = CarbonImmutable::createFromFormat($format, $value);

                if ($date !== false) {
                    return $date->toDateString();
                }
            } catch (Throwable) {
                // Continue.
            }
        }

        try {
            return CarbonImmutable::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
