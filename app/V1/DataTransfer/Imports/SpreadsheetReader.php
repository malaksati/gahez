<?php

namespace App\V1\DataTransfer\Imports;

use App\V1\DataTransfer\Support\SpreadsheetHeaderNormalizer;
use Maatwebsite\Excel\Facades\Excel;

final class SpreadsheetReader
{
    /**
     * @return list<array<string, mixed>>
     */
    public function readRows(string $absolutePath): array
    {
        $sheets = Excel::toArray(new EmptyArrayImport, $absolutePath);

        if ($sheets === [] || $sheets[0] === []) {
            return [];
        }

        $sheet = $sheets[0];
        $headerRow = array_shift($sheet);
        $headers = $this->normalizeHeaders($headerRow);

        $rows = [];

        foreach ($sheet as $index => $cells) {
            if ($this->isEmptyRow($cells)) {
                continue;
            }

            $row = [];

            foreach ($headers as $columnIndex => $key) {
                if ($key === '') {
                    continue;
                }

                $row[$key] = $cells[$columnIndex] ?? null;
            }

            $row['_spreadsheet_row'] = $index + 2;
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param  list<mixed>  $headerRow
     * @return list<string>
     */
    private function normalizeHeaders(array $headerRow): array
    {
        return array_map(
            fn ($header) => SpreadsheetHeaderNormalizer::normalize((string) $header),
            $headerRow,
        );
    }

    /**
     * @param  list<mixed>  $cells
     */
    private function isEmptyRow(array $cells): bool
    {
        foreach ($cells as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }
}
