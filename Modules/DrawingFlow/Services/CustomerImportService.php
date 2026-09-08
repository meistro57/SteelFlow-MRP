<?php

namespace Modules\DrawingFlow\Services;

use App\Models\Customer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CustomerImportService
{
    /**
     * @return array{created:int,updated:int,skipped:int,errors:array<int,string>}
     */
    public function import(UploadedFile $file): array
    {
        $parsed = $this->readRows($file);
        $rows = $parsed['rows'];
        $errors = $parsed['errors'];

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $rowData) {
            $payload = $this->normalizeRow($rowData['row']);
            $line = $rowData['line'];

            if (($payload['name'] ?? null) === null) {
                $skipped++;
                $errors[] = "Line {$line}: missing required value for name.";

                continue;
            }

            $customer = $this->resolveCustomerForImport($payload);

            if ($customer) {
                $customer->update($payload);
                $updated++;
            } else {
                Customer::create($payload);
                $created++;
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * @return array{rows:array<int,array{line:int,row:array<string,string|null>}>,errors:array<int,string>}
     */
    private function readRows(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return [
                'rows' => [],
                'errors' => ['Could not open the uploaded file.'],
            ];
        }

        $headers = fgetcsv($handle);

        if (! is_array($headers)) {
            fclose($handle);

            return [
                'rows' => [],
                'errors' => ['CSV file is missing a header row.'],
            ];
        }

        $headers = array_map(function ($header): string {
            $value = (string) $header;
            $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;

            return strtolower(trim($value));
        }, $headers);

        $requiredHeaders = ['name'];
        $missingHeaders = array_values(array_diff($requiredHeaders, $headers));

        if ($missingHeaders !== []) {
            fclose($handle);

            return [
                'rows' => [],
                'errors' => [
                    'Missing required header(s): '.implode(', ', $missingHeaders).'.',
                ],
            ];
        }

        $rows = [];
        $line = 1;

        while (($values = fgetcsv($handle)) !== false) {
            $line++;

            if ($this->isEmptyRow($values)) {
                continue;
            }

            $row = [];

            foreach ($headers as $index => $header) {
                $row[$header] = isset($values[$index]) ? trim((string) $values[$index]) : null;
            }

            $rows[] = [
                'line' => $line,
                'row' => $row,
            ];
        }

        fclose($handle);

        return [
            'rows' => $rows,
            'errors' => [],
        ];
    }

    /**
     * @param  array<int, string|null>  $values
     */
    private function isEmptyRow(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array{name:?string,email:?string,phone:?string,address:?string,city:?string,state:?string,zip:?string,country:string,notes:?string,active:bool}
     */
    private function normalizeRow(array $row): array
    {
        $activeValue = strtolower((string) ($row['active'] ?? ''));

        return [
            'name' => $this->nullableValue($row['name'] ?? null),
            'email' => $this->nullableValue($row['email'] ?? null),
            'phone' => $this->normalizePhone($row['phone'] ?? null),
            'address' => $this->nullableValue($row['address'] ?? null),
            'city' => $this->nullableValue($row['city'] ?? null),
            'state' => $this->nullableValue($row['state'] ?? null),
            'zip' => $this->nullableValue($row['zip'] ?? null),
            'country' => $this->nullableValue($row['country'] ?? null) ?? 'USA',
            'notes' => $this->nullableValue($row['notes'] ?? null),
            'active' => in_array($activeValue, ['0', 'false', 'no', 'n'], true) ? false : true,
        ];
    }

    private function nullableValue(?string $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizePhone(?string $value): ?string
    {
        $phoneValue = $this->nullableValue($value);

        if ($phoneValue === null) {
            return null;
        }

        $phoneLines = preg_split('/\r\n|\r|\n/', $phoneValue) ?: [];

        foreach ($phoneLines as $line) {
            $trimmedLine = trim($line);

            if ($trimmedLine !== '') {
                return mb_substr($trimmedLine, 0, 50);
            }
        }

        return null;
    }

    /**
     * @param  array{name:?string,email:?string,phone:?string,address:?string,city:?string,state:?string,zip:?string,country:string,notes:?string,active:bool}  $payload
     */
    private function resolveCustomerForImport(array $payload): ?Customer
    {
        return Customer::whereRaw('lower(name) = ?', [Str::lower((string) $payload['name'])])->first();
    }
}
