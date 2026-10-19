<?php

namespace App\Imports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

/*
|--------------------------------------------------------------------------
| PartReadyImport
|--------------------------------------------------------------------------
| Khusus untuk format "ISD View Case Report" (BEDA dengan Transaction
| Report yang dipakai SplitCaseImport untuk close_case/pending_case):
| - Baris 1-3 = judul & tanggal report (bukan data)
| - Header asli ada di baris ke-4 (dicari otomatis, tidak di-hardcode
|   posisinya, supaya tetap jalan walau ISD ubah 1-2 baris di atasnya)
| - Data mulai baris setelah header
|
| Hanya baris dengan "Case status" mengandung "Part Ready" yang diambil.
| Insert ke tabel BARU `part_ready_case` — TIDAK PERNAH menyentuh
| pending_case / close_case.
|--------------------------------------------------------------------------
*/
class PartReadyImport implements ToCollection
{
    protected string $kodeUpload;

    public int $inserted = 0;
    public int $filteredOut = 0; // baris lain (bukan Part Ready) yang diabaikan
    public int $skippedRows = 0; // baris tanpa Case ID (kosong/rusak)

    public function __construct(string $kodeUpload)
    {
        $this->kodeUpload = $kodeUpload;
    }

    public function collection(Collection $rows)
    {
        // 1) Cari baris header secara dinamis: baris yang mengandung "Case ID"
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
            throw new \Exception('Format file tidak dikenali (header "Case ID" tidak ditemukan). Pastikan file adalah export "ISD View Case Report".');
        }

        // 2) Mapping nama kolom -> index kolom
        $col = [];
        foreach ($header as $idx => $name) {
            if (!is_null($name)) {
                $col[$name] = $idx;
            }
        }

        $required = ['Case ID', 'Case status', 'Customer name', 'Customer email', 'Customer mobile no.'];
        foreach ($required as $r) {
            if (!isset($col[$r])) {
                throw new \Exception("Kolom \"{$r}\" tidak ditemukan di file. Pastikan file export ISD tidak diubah strukturnya.");
            }
        }

        // 3) Proses baris data (setelah header)
        $dataRows = $rows->slice($headerRowIndex + 1);

        foreach ($dataRows as $row) {

            $caseId = $row[$col['Case ID']] ?? null;

            if (empty($caseId)) {
                continue; // baris kosong / footer (mis. "0.02s")
            }

            $caseStatus = $row[$col['Case status']] ?? null;

            if (!$caseStatus || stripos($caseStatus, 'Part Ready') === false) {
                $this->filteredOut++;
                continue; // bukan status Part Ready, lewati
            }

            DB::table('part_ready_case')->updateOrInsert(
                ['case_id' => $caseId], // kunci anti-duplikat
                [
                    'kode_upload'    => $this->kodeUpload,
                    'case_id_manual' => $row[$col['Case ID manual']] ?? null,
                    'reference_case' => $row[$col['Reference case']] ?? null,
                    'case_status'    => $caseStatus,
                    'product_name'   => $row[$col['Product name']] ?? null,
                    'customer_name'  => $row[$col['Customer name']] ?? null,
                    'customer_email' => $row[$col['Customer email']] ?? null,
                    'mobile_no'      => $row[$col['Customer mobile no.']] ?? null,
                    'phone_no'       => $row[$col['Customer phone no.']] ?? null,
                    'city'           => $row[$col['City']] ?? null,
                    'status'         => 'pending',
                    'updated_at'     => now(),
                    'created_at'     => now(),
                ]
            );

            $this->inserted++;
        }
    }
}
