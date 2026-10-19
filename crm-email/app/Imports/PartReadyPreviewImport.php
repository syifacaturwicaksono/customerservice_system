<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

/*
|--------------------------------------------------------------------------
| PartReadyPreviewImport
|--------------------------------------------------------------------------
| Versi "dry run" dari PartReadyImport — logic klasifikasinya SAMA PERSIS,
| TAPI TIDAK PERNAH menulis ke database sama sekali. Dipakai khusus oleh
| tombol "Preview Dulu" supaya user bisa cek dulu isi file ISD sebelum
| benar-benar upload.
|--------------------------------------------------------------------------
*/
class PartReadyPreviewImport implements ToCollection
{
    public int $matchedCount = 0;
    public array $matchedSample = [];
    public int $filteredOut = 0;
    public int $skippedRows = 0;

    protected int $sampleLimit;

    public function __construct(int $sampleLimit = 100)
    {
        $this->sampleLimit = $sampleLimit;
    }

    public function collection(Collection $rows)
    {
        $headerRowIndex = null;
        $header = [];

        foreach ($rows as $i => $row) {
            if (in_array('Case ID', $row->toArray(), true)) {
                $headerRowIndex = $i;
                $header = $row->toArray();
                break;
            }
        }

        if ($headerRowIndex === null) {
            throw new \Exception('Format file tidak dikenali (header "Case ID" tidak ditemukan). Pastikan file adalah export "ISD View Case Report", bukan Transaction Report.');
        }

        $col = [];
        foreach ($header as $idx => $name) {
            if (!is_null($name)) {
                $col[$name] = $idx;
            }
        }

        $required = ['Case ID', 'Case status', 'Customer name', 'Customer email', 'Customer mobile no.'];
        foreach ($required as $r) {
            if (!isset($col[$r])) {
                throw new \Exception("Kolom \"{$r}\" tidak ditemukan di file. File ini kemungkinan Transaction Report, bukan ISD View Case Report.");
            }
        }

        $dataRows = $rows->slice($headerRowIndex + 1);

        foreach ($dataRows as $row) {

            $caseId = $row[$col['Case ID']] ?? null;

            if (empty($caseId)) {
                continue;
            }

            $caseStatus = $row[$col['Case status']] ?? null;

            if (!$caseStatus || stripos($caseStatus, 'Part Ready') === false) {
                $this->filteredOut++;
                continue;
            }

            $this->matchedCount++;

            if (count($this->matchedSample) < $this->sampleLimit) {
                $this->matchedSample[] = [
                    'case_id'         => $caseId,
                    'customer_name'   => $row[$col['Customer name']] ?? '-',
                    'customer_email'  => $row[$col['Customer email']] ?? '-',
                    'product_name'    => $row[$col['Product name']] ?? '-',
                    'case_status'     => $caseStatus,
                ];
            }
        }
    }
}
