<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

/*
|--------------------------------------------------------------------------
| BaseCaseImport
|--------------------------------------------------------------------------
| Import Excel "ISD Transaction Report" ke tabel close_case / pending_case.
| Mapping dilakukan berdasarkan POSISI kolom (bukan cocokkan nama header),
| karena header aslinya (baris ke-4 di file Excel) ada beberapa typo
| (misal "Quotation amouont") — posisi kolom jauh lebih bisa diandalkan.
|
| Urutan 52 kolom Excel INI SUDAH DIVERIFIKASI cocok 1:1 secara berurutan
| dengan kolom tabel close_case/pending_case di database (kecuali id,
| kode_upload, created_at, updated_at yang memang bukan bagian dari Excel).
|--------------------------------------------------------------------------
*/

abstract class BaseCaseImport implements ToCollection, WithStartRow
{
    protected string $kodeUpload;

    /**
     * Jumlah baris yang di-skip karena dianggap bukan data valid (misal
     * baris header yang terulang di tengah file, atau kolom "No" bukan
     * angka). Bisa dicek dari controller setelah import selesai.
     */
    public int $skippedRows = 0;

    /**
     * Jumlah baris yang di-skip karena Case ID-nya SUDAH ADA di database
     * (dari upload sebelumnya) — cuma relevan untuk Close Case (mode APPEND),
     * karena Pending Case selalu di-truncate dulu sebelum insert.
     */
    public int $duplicateSkipped = 0;

    /**
     * Jumlah baris yang di-skip karena Case ID-nya SUDAH MUNCUL sebelumnya
     * di FILE YANG SAMA (bukan dari database) — misal karena file export
     * mengulang sebagian data di tiap "halaman" laporan.
     */
    public int $duplicateInFileSkipped = 0;

    /**
     * Menyimpan Case ID yang sudah diproses dalam 1 kali import, supaya
     * baris dengan Case ID yang sama tidak dihitung dua kali kalau memang
     * terulang di file sumbernya.
     */
    protected array $seenCaseIds = [];

    /**
     * Urutan HARUS sama persis dengan urutan kolom di file Excel
     * "Transaction Report" (kolom A s/d AZ, header di baris 4).
     */
    protected array $dbColumns = [
        'no', 'case_id', 'case_id_manual', 'reference_case', 'company_name',
        'received_date', 'logged_by', 'closed_by', 'warranty_status', 'serial_no',
        'product_tower', 'product_group', 'product_type', 'product_line', 'product_no',
        'product_name', 'awb_no_pickup', 'pickup_date', 'pickup_received_date',
        'awb_no_return', 'return_date', 'return_received_date', 'case_type',
        'case_status', 'problem_desc', 'defect_desc', 'repair_action', 'delay_code',
        'ce_name', 'customer_company', 'customer_name', 'pic_name', 'city',
        'phone_no', 'mobile_no', 'marketing_preference', 'start_repair_date',
        'tat_open_case', 'tat_case', 'tat_opt', 'quotation_no', 'quotation_amount',
        'quotation_date', 'quotation_approved_date', 'finish_onsite_date',
        'finish_date', 'closed_date', 'opt_log_date', 'opt_sub_case_close_date',
        'opt_main_case_close_date', 'void_cancel_reason', 'erf',
    ];

    protected array $dateColumns = [
        'received_date', 'pickup_date', 'pickup_received_date', 'return_date',
        'return_received_date', 'start_repair_date', 'quotation_date',
        'quotation_approved_date', 'finish_onsite_date', 'finish_date',
        'closed_date', 'opt_log_date', 'opt_sub_case_close_date',
        'opt_main_case_close_date',
    ];

    protected array $intColumns = ['tat_open_case', 'tat_case', 'tat_opt'];

    public function __construct(string $kodeUpload)
    {
        $this->kodeUpload = $kodeUpload;
    }

    abstract protected function tableName(): string;

    /**
     * Header ada di baris ke-4 pada sheet "Transaction Report", jadi data
     * baris sesungguhnya baru mulai di baris ke-5.
     */
    public function startRow(): int
    {
        return 5;
    }

    public function collection(Collection $rows)
    {
        $insertData = [];

        foreach ($rows as $row) {

            $row = $row->values();

            // Kolom ke-2 di Excel (index 1) = Case ID. Kalau kosong, anggap
            // baris kosong / akhir data, skip saja.
            $caseId = $row[1] ?? null;

            if ($caseId === null || $caseId === '') {
                continue;
            }

            // ★★★ BARU: kolom ke-1 (index 0) = "No", seharusnya selalu angka.
            // Kalau ternyata bukan angka (misal isinya teks "No." atau nama
            // header lain), berarti ini baris HEADER YANG TERULANG di tengah
            // data (biasa terjadi di file export ISD yang punya header di
            // tiap "halaman"). Baris seperti ini di-skip, BUKAN dipaksa
            // insert — supaya tidak bikin seluruh upload gagal cuma karena
            // 1 baris nyasar. ★★★
            $noValue = $row[0] ?? null;

            if (!is_numeric($noValue)) {
                $this->skippedRows++;
                continue;
            }

            // ★★★ BARU: cek apakah Case ID ini SUDAH MUNCUL sebelumnya di file
            // yang sama. File export ISD kadang mengulang sebagian data case di
            // tiap "halaman" laporan (bukan cuma header yang terulang, tapi baris
            // datanya juga ikut kecopy) — kalau tidak dicek, Case ID yang sama
            // akan dihitung berkali-kali sebagai case terpisah, apalagi untuk
            // Pending Case yang TIDAK punya aturan unique di database (beda
            // dengan Close Case). ★★★
            $caseIdKey = trim((string) $caseId);

            if (isset($this->seenCaseIds[$caseIdKey])) {
                $this->duplicateInFileSkipped++;
                continue;
            }

            $this->seenCaseIds[$caseIdKey] = true;

            $rowData = ['kode_upload' => $this->kodeUpload];

            foreach ($this->dbColumns as $i => $col) {

                $value = $row[$i] ?? null;
                $value = is_string($value) ? trim($value) : $value;

                if ($value === '' || $value === null) {
                    $rowData[$col] = null;
                    continue;
                }

                if (in_array($col, $this->dateColumns, true)) {
                    $rowData[$col] = $this->parseDate($value);
                } elseif (in_array($col, $this->intColumns, true)) {
                    $rowData[$col] = is_numeric($value) ? (int) $value : null;
                } elseif ($col === 'quotation_amount') {
                    $rowData[$col] = is_numeric($value) ? (float) $value : null;
                } else {
                    $rowData[$col] = (string) $value;
                }
            }

            $rowData['created_at'] = now();
            $rowData['updated_at'] = now();

            $insertData[] = $rowData;
        }

        if (empty($insertData)) {
            return;
        }

        // ★★★ BARU: pakai insertOrIgnore() bukan insert() biasa — supaya kalau
        // ada Case ID yang SUDAH ADA di database (duplikat, misal karena laporan
        // bulanan tumpang-tindih beberapa hari dengan laporan sebelumnya), baris
        // itu otomatis DILEWATI saja, TIDAK bikin seluruh batch/chunk gagal total
        // seperti sebelumnya (insert() biasa: satu baris duplikat = SELURUH 500
        // baris dalam chunk itu ikut gagal, karena satu statement INSERT dengan
        // banyak values bersifat atomik). ★★★
        foreach (array_chunk($insertData, 500) as $chunk) {
            $insertedCount = DB::table($this->tableName())->insertOrIgnore($chunk);
            $this->duplicateSkipped += (count($chunk) - $insertedCount);
        }
    }

    /**
     * Parsing tanggal dari format Excel "01 Oct 2025 08:53" (ada juga yang
     * cuma tanggal tanpa jam). Kalau tidak ada format yang cocok atau memang
     * kosong, hasilnya NULL — TIDAK menghentikan proses upload baris lain.
     */
    protected function parseDate($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                    ->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $value = trim((string) $value);

        foreach (['d M Y H:i', 'd M Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }
}