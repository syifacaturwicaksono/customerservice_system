<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

/*
|--------------------------------------------------------------------------
| PreviewCaseImport
|--------------------------------------------------------------------------
| Sama persis logic-nya dengan SplitCaseImport (mapping kolom, parsing
| tanggal, cara memisah Close Case vs Pending Case, deteksi baris tidak
| valid / duplikat DALAM FILE) — TAPI TIDAK PERNAH menulis apapun ke
| database. Hasilnya cuma dikumpulkan ke properti public untuk ditampilkan
| sebagai preview ke user SEBELUM mereka klik "Upload & Proses" beneran.
|
| ★★★ PENTING: kalau mapping kolom (dbColumns) atau logic pemisahan di
| SplitCaseImport diubah, class ini WAJIB diubah juga secara sinkron,
| supaya preview yang ditampilkan benar-benar mencerminkan apa yang bakal
| terjadi saat upload sungguhan. ★★★
|--------------------------------------------------------------------------
*/

class PreviewCaseImport implements ToCollection, WithStartRow
{
    /** Jenis yang mau di-preview (close_case / pending_case). */
    protected string $targetType;

    /** Maksimal baris contoh yang disimpan untuk ditampilkan (biar response tidak kebesaran). */
    protected int $maxSample;

    /** Total baris yang COCOK dengan targetType (ini yang bakal benar-benar ter-upload). */
    public int $matchedCount = 0;

    /** Contoh baris yang cocok (dibatasi $maxSample), sudah dalam bentuk array sederhana untuk ditampilkan. */
    public array $matchedSample = [];

    /** Total baris valid tapi jenisnya BUKAN targetType (bakal diabaikan saat upload). */
    public int $filteredOut = 0;

    /** Total baris yang di-skip karena bukan data valid (header terulang, dsb). */
    public int $skippedRows = 0;

    /** Total baris yang di-skip karena Case ID-nya terulang di FILE yang sama. */
    public int $duplicateInFileSkipped = 0;

    protected array $seenCaseIds = [];

    protected array $closeStatuses = [
        'Close Repair (Cust pick up)',
        'Close Cancel (Cust pick up)',
    ];

    /**
     * Urutan HARUS sama persis dengan SplitCaseImport / BaseCaseImport.
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

    public function __construct(string $targetType, int $maxSample = 100)
    {
        $this->targetType = $targetType;
        $this->maxSample  = $maxSample;
    }

    public function startRow(): int
    {
        return 5;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $row = $row->values();

            $caseId = $row[1] ?? null;

            if ($caseId === null || $caseId === '') {
                continue;
            }

            $noValue = $row[0] ?? null;

            if (!is_numeric($noValue)) {
                $this->skippedRows++;
                continue;
            }

            $caseIdKey = trim((string) $caseId);

            if (isset($this->seenCaseIds[$caseIdKey])) {
                $this->duplicateInFileSkipped++;
                continue;
            }

            $this->seenCaseIds[$caseIdKey] = true;

            $rowData = [];

            foreach ($this->dbColumns as $i => $col) {

                $value = $row[$i] ?? null;
                $value = is_string($value) ? trim($value) : $value;

                if ($value === '' || $value === null) {
                    $rowData[$col] = null;
                    continue;
                }

                if (in_array($col, $this->dateColumns, true)) {
                    $rowData[$col] = $this->parseDate($value);
                } else {
                    $rowData[$col] = (string) $value;
                }
            }

            $rowIsClose = in_array($rowData['case_status'], $this->closeStatuses, true);
            $rowType    = $rowIsClose ? 'close_case' : 'pending_case';

            if ($rowType !== $this->targetType) {
                $this->filteredOut++;
                continue;
            }

            $this->matchedCount++;

            if (count($this->matchedSample) < $this->maxSample) {
                $this->matchedSample[] = [
                    'case_id'         => $rowData['case_id'],
                    'company_name'    => $rowData['company_name'],
                    'customer_name'   => $rowData['customer_name'] ?? $rowData['customer_company'],
                    'ce_name'         => $rowData['ce_name'],
                    'product_name'    => $rowData['product_name'],
                    'case_status'     => $rowData['case_status'],
                    'warranty_status' => $rowData['warranty_status'],
                    'received_date'   => $rowData['received_date'],
                ];
            }
        }
    }

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
