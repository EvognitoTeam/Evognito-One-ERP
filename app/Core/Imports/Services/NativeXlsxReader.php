<?php

namespace App\Core\Imports\Services;

use PharData;
use RuntimeException;
use Throwable;
use ZipArchive;

class NativeXlsxReader
{
    /**
     * @return array<int, string>
     */
    public function sheetNames(string $path): array
    {
        [$archive, $mustClose] = $this->openArchive($path);

        try {
            $workbookXml = $this->archiveGet($archive, 'xl/workbook.xml');

            if ($workbookXml === null) {
                throw new RuntimeException('Invalid XLSX workbook structure.');
            }

            preg_match_all(
                '~<sheet\b([^>]*)/?>~si',
                $workbookXml,
                $sheetMatches,
                PREG_SET_ORDER
            );

            $names = [];

            foreach ($sheetMatches as $sheetMatch) {
                $name = $this->xmlDecode(
                    $this->attribute($sheetMatch[1], 'name') ?? ''
                );

                if ($name !== '') {
                    $names[] = $name;
                }
            }

            return $names;
        } finally {
            if ($mustClose && $archive instanceof ZipArchive) {
                $archive->close();
            }
        }
    }

    /**
     * Read up to $maxColumns columns from one XLSX worksheet.
     *
     * @return array<int, array<int, mixed>> Keyed by Excel row number.
     */
    public function readSheet(
        string $path,
        string $sheetName,
        int $maxColumns = 160,
        ?int $maxRows = null
    ): array {
        [$archive, $mustClose] = $this->openArchive($path);

        try {
            $sharedStrings = $this->readSharedStrings($archive);
            $worksheetPath = $this->resolveWorksheetPath($archive, $sheetName);
            $worksheetXml = $this->archiveGet($archive, $worksheetPath);

            if ($worksheetXml === null) {
                throw new RuntimeException(
                    "Worksheet XML not found for sheet [{$sheetName}]."
                );
            }

            $rows = [];

            preg_match_all(
                '~<row\b([^>]*)>(.*?)</row>~si',
                $worksheetXml,
                $rowMatches,
                PREG_SET_ORDER
            );

            foreach ($rowMatches as $rowMatch) {
                $rowNumber = (int) ($this->attribute($rowMatch[1], 'r') ?? 0);

                if ($rowNumber <= 0) {
                    continue;
                }

                if ($maxRows !== null && $rowNumber > $maxRows) {
                    break;
                }

                $row = array_fill(0, $maxColumns, null);

                preg_match_all(
                    '~<c\b([^>]*?)(?:/>|>(.*?)</c>)~si',
                    $rowMatch[2],
                    $cellMatches,
                    PREG_SET_ORDER
                );

                foreach ($cellMatches as $cellMatch) {
                    $reference = $this->attribute($cellMatch[1], 'r') ?? '';
                    $columnIndex = $this->columnIndexFromReference($reference);

                    if ($columnIndex < 0 || $columnIndex >= $maxColumns) {
                        continue;
                    }

                    $type = $this->attribute($cellMatch[1], 't') ?? '';
                    $cellBody = $cellMatch[2] ?? '';

                    $row[$columnIndex] = $this->cellValue(
                        $cellBody,
                        $type,
                        $sharedStrings
                    );
                }

                $rows[$rowNumber] = $row;
            }

            return $rows;
        } finally {
            if ($mustClose && $archive instanceof ZipArchive) {
                $archive->close();
            }
        }
    }

    /** @return array{0:mixed,1:bool} */
    private function openArchive(string $path): array
    {
        if (class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();

            if ($zip->open($path) === true) {
                return [$zip, true];
            }
        }

        if (class_exists(PharData::class)) {
            try {
                return [new PharData($path), false];
            } catch (Throwable) {
                // Continue to clear error below.
            }
        }

        throw new RuntimeException(
            'Unable to read XLSX. PHP needs ZipArchive or Phar support.'
        );
    }

    private function archiveGet(mixed $archive, string $name): ?string
    {
        if ($archive instanceof ZipArchive) {
            $content = $archive->getFromName($name);
            return $content === false ? null : $content;
        }

        if ($archive instanceof PharData) {
            if (!isset($archive[$name])) {
                return null;
            }

            return $archive[$name]->getContent();
        }

        return null;
    }

    /** @return array<int, string> */
    private function readSharedStrings(mixed $archive): array
    {
        $xml = $this->archiveGet($archive, 'xl/sharedStrings.xml');

        if ($xml === null) {
            return [];
        }

        $strings = [];

        preg_match_all(
            '~<si\b[^>]*>(.*?)</si>~si',
            $xml,
            $items,
            PREG_SET_ORDER
        );

        foreach ($items as $item) {
            $value = '';

            preg_match_all(
                '~<t\b[^>]*>(.*?)</t>~si',
                $item[1],
                $texts,
                PREG_SET_ORDER
            );

            foreach ($texts as $text) {
                $value .= $this->xmlDecode($text[1]);
            }

            $strings[] = $value;
        }

        return $strings;
    }

    private function resolveWorksheetPath(
        mixed $archive,
        string $sheetName
    ): string {
        $workbookXml = $this->archiveGet($archive, 'xl/workbook.xml');
        $relsXml = $this->archiveGet($archive, 'xl/_rels/workbook.xml.rels');

        if ($workbookXml === null || $relsXml === null) {
            throw new RuntimeException('Invalid XLSX workbook structure.');
        }

        $relationshipId = null;

        preg_match_all(
            '~<sheet\b([^>]*)/?>~si',
            $workbookXml,
            $sheetMatches,
            PREG_SET_ORDER
        );

        foreach ($sheetMatches as $sheetMatch) {
            $name = $this->xmlDecode(
                $this->attribute($sheetMatch[1], 'name') ?? ''
            );

            if ($name !== $sheetName) {
                continue;
            }

            $relationshipId =
                $this->attribute($sheetMatch[1], 'r:id')
                ??
                $this->attribute($sheetMatch[1], 'id');

            break;
        }

        if (!$relationshipId) {
            throw new RuntimeException(
                "Sheet [{$sheetName}] was not found."
            );
        }

        preg_match_all(
            '~<Relationship\b([^>]*)/?>~si',
            $relsXml,
            $relMatches,
            PREG_SET_ORDER
        );

        foreach ($relMatches as $relMatch) {
            if (($this->attribute($relMatch[1], 'Id') ?? '') !== $relationshipId) {
                continue;
            }

            $target = ltrim(
                $this->xmlDecode(
                    $this->attribute($relMatch[1], 'Target') ?? ''
                ),
                '/'
            );

            if ($target === '') {
                break;
            }

            if (str_starts_with($target, 'xl/')) {
                return $target;
            }

            return 'xl/'.ltrim($target, '/');
        }

        throw new RuntimeException(
            "Relationship for sheet [{$sheetName}] was not found."
        );
    }

    /** @param array<int, string> $sharedStrings */
    private function cellValue(
        string $cellXml,
        string $type,
        array $sharedStrings
    ): mixed {
        if ($type === 'inlineStr') {
            $value = '';

            preg_match_all(
                '~<t\b[^>]*>(.*?)</t>~si',
                $cellXml,
                $texts,
                PREG_SET_ORDER
            );

            foreach ($texts as $text) {
                $value .= $this->xmlDecode($text[1]);
            }

            return $value === '' ? null : $value;
        }

        if (!preg_match('~<v\b[^>]*>(.*?)</v>~si', $cellXml, $valueMatch)) {
            return null;
        }

        $raw = $this->xmlDecode($valueMatch[1]);

        if ($raw === '') {
            return null;
        }

        return match ($type) {
            's' => $sharedStrings[(int) $raw] ?? null,
            'b' => $raw === '1',
            'str' => $raw,
            default => $raw,
        };
    }

    private function attribute(string $attributes, string $name): ?string
    {
        $pattern = '~(?:^|\s)'.preg_quote($name, '~').'\s*=\s*(["\'])(.*?)\1~si';

        if (!preg_match($pattern, $attributes, $match)) {
            return null;
        }

        return $match[2];
    }

    private function xmlDecode(string $value): string
    {
        return html_entity_decode(
            strip_tags($value),
            ENT_QUOTES | ENT_XML1,
            'UTF-8'
        );
    }

    private function columnIndexFromReference(string $reference): int
    {
        if (!preg_match('/^([A-Z]+)/i', $reference, $matches)) {
            return -1;
        }

        $letters = strtoupper($matches[1]);
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }
}
