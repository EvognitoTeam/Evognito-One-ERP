<?php

namespace App\Modules\Sales\Services;

use App\Models\Organization;
use App\Modules\Sales\Models\SalesRecord;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SalesRecordImportService
{
    public function import(
        Organization $organization,
        UploadedFile $file,
        ?int $userId = null
    ): array {
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = match ($extension) {
            'csv', 'txt' => $this->readCsv($file->getRealPath()),
            'xlsx' => $this->readSpreadsheet($file->getRealPath()),
            'xls' => $this->readLegacySpreadsheet($file->getRealPath()),
            default => throw new RuntimeException('Unsupported import file.'),
        };

        if (count($rows) < 2) {
            throw new RuntimeException('File import tidak memiliki data.');
        }

        $headers = array_map(
            fn ($header) => $this->normalizeHeader((string) $header),
            array_shift($rows)
        );

        $map = $this->headerMap();
        $batchUlid = (string) Str::ulid();
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use (
            $rows,
            $headers,
            $map,
            $organization,
            $batchUlid,
            $userId,
            &$created,
            &$skipped
        ) {
            foreach ($rows as $row) {
                $payload = [];
                $raw = [];

                foreach ($headers as $index => $normalizedHeader) {
                    $value = $row[$index] ?? null;
                    $raw[$normalizedHeader] = $value;

                    if (isset($map[$normalizedHeader])) {
                        $payload[$map[$normalizedHeader]] = $this->cleanValue($value);
                    }
                }

                if (
                    empty(array_filter($payload, fn ($value) => $value !== null && $value !== ''))
                ) {
                    $skipped++;
                    continue;
                }

                $payload['invoice_date'] = $this->parseDate($payload['invoice_date'] ?? null);

                foreach ([
                    'unit_1', 'quantity', 'ratio_2', 'customer_tier_price', 'pl_price',
                    'gross_amount', 'sales_dpp', 'liters', 'owing',
                ] as $numeric) {
                    $payload[$numeric] = $this->parseNumber($payload[$numeric] ?? null);
                }

                SalesRecord::create([
                    ...$payload,
                    'organization_id' => $organization->id,
                    'import_batch_ulid' => $batchUlid,
                    'raw_payload' => $raw,
                    'created_by' => $userId,
                ]);

                $created++;
            }
        });

        return [
            'batch_ulid' => $batchUlid,
            'created' => $created,
            'skipped' => $skipped,
        ];
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if (!$handle) {
            throw new RuntimeException('File CSV tidak dapat dibaca.');
        }

        $firstLine = fgets($handle);
        rewind($handle);

        $delimiter = substr_count((string) $firstLine, ';') > substr_count((string) $firstLine, ',')
            ? ';'
            : ',';

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private function readSpreadsheet(string $path): array
    {
        if (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            return $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        }

        if (!class_exists(\ZipArchive::class)) {
            throw new RuntimeException(
                'Import XLSX membutuhkan ekstensi PHP zip atau PhpSpreadsheet. Gunakan CSV jika keduanya tidak tersedia.'
            );
        }

        return $this->readXlsxWithoutDependency($path);
    }

    private function readLegacySpreadsheet(string $path): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new RuntimeException(
                'Format XLS lama membutuhkan PhpSpreadsheet. Simpan file sebagai XLSX atau CSV, atau install maatwebsite/excel.'
            );
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        return $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
    }

    private function readXlsxWithoutDependency(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('File XLSX tidak dapat dibuka.');
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $xml = simplexml_load_string($sharedXml);
            if ($xml !== false) {
                $namespaces = $xml->getNamespaces(true);
                $root = isset($namespaces['']) ? $xml->children($namespaces['']) : $xml;

                foreach ($root->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } else {
                        $parts = [];
                        foreach ($si->r as $run) {
                            $parts[] = (string) $run->t;
                        }
                        $sharedStrings[] = implode('', $parts);
                    }
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            throw new RuntimeException('Sheet pertama XLSX tidak ditemukan.');
        }

        $xml = simplexml_load_string($sheetXml);
        $zip->close();

        if ($xml === false) {
            throw new RuntimeException('Sheet XLSX tidak valid.');
        }

        $namespaces = $xml->getNamespaces(true);
        $root = isset($namespaces['']) ? $xml->children($namespaces['']) : $xml;

        $rows = [];
        foreach ($root->sheetData->row as $row) {
            $values = [];
            $lastColumn = -1;

            foreach ($row->c as $cell) {
                $reference = (string) $cell['r'];
                preg_match('/^[A-Z]+/', $reference, $match);
                $columnIndex = $this->excelColumnToIndex($match[0] ?? 'A');

                while ($lastColumn + 1 < $columnIndex) {
                    $values[] = null;
                    $lastColumn++;
                }

                $type = (string) $cell['t'];
                $value = isset($cell->v) ? (string) $cell->v : '';

                if ($type === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                } elseif ($type === 'inlineStr' && isset($cell->is->t)) {
                    $value = (string) $cell->is->t;
                }

                $values[] = $value;
                $lastColumn = $columnIndex;
            }

            $rows[] = $values;
        }

        return $rows;
    }

    private function excelColumnToIndex(string $letters): int
    {
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }

    private function normalizeHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
        $header = strtolower(trim($header));
        $header = preg_replace('/\s+/', ' ', $header) ?? $header;
        return $header;
    }

    private function headerMap(): array
    {
        return [
            'product name' => 'product_name',
            'packaging' => 'packaging',
            'principal prod code' => 'principal_product_code',
            'customer name' => 'customer_name',
            'no cust.' => 'customer_number',
            'no cust' => 'customer_number',
            'address' => 'address',
            'area' => 'area',
            'inv.date' => 'invoice_date',
            'inv date' => 'invoice_date',
            'no. surat jalan' => 'delivery_note_no',
            'no surat jalan' => 'delivery_note_no',
            'no. invoice' => 'invoice_no',
            'no invoice' => 'invoice_no',
            'salesman' => 'salesman',
            'unit 1' => 'unit_1',
            'kuantitas' => 'quantity',
            'rasio 2' => 'ratio_2',
            'kemasan' => 'package_unit',
            'harga tier pelanggan' => 'customer_tier_price',
            'harga pl' => 'pl_price',
            'gros amt' => 'gross_amount',
            'dpp penjualan' => 'sales_dpp',
            'ltr' => 'liters',
            'market sektor' => 'market_sector',
            'kategori' => 'category',
            'kode share pelanggan' => 'customer_share_code',
            'owing' => 'owing',
            'nama dept.' => 'department_name',
            'nama dept' => 'department_name',
        ];
    }

    private function cleanValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        return $value === '' ? null : $value;
    }

    private function parseNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = str_replace(['Rp', ' ', '\u{00A0}'], '', (string) $value);

        if (str_contains($value, ',') && str_contains($value, '.')) {
            if (strrpos($value, ',') > strrpos($value, '.')) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        }

        $value = preg_replace('/[^0-9.\-]/', '', $value) ?? '';

        return is_numeric($value) ? (float) $value : null;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            if (class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
                try {
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
                } catch (\Throwable) {
                    // Use the dependency-free Excel serial fallback below.
                }
            }

            $serial = (float) $value;
            if ($serial > 1000 && $serial < 100000) {
                try {
                    return Carbon::create(1899, 12, 30)
                        ->addDays((int) floor($serial))
                        ->format('Y-m-d');
                } catch (\Throwable) {
                    // Continue with normal parser.
                }
            }
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, trim((string) $value));
                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable) {
                // Try next format.
            }
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
