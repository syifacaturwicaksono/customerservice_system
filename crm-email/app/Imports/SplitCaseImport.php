<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

/*
|--------------------------------------------------------------------------
| SplitCaseImport
|--------------------------------------------------------------------------
| Import Excel "ISD Transaction Report" yang MASIH CAMPUR antara Close Case
| dan Pending Case dalam satu file (satu baris = satu case, dibedakan lewat
| kolom "Case status"). User tetap PILIH salah satu Jenis Monitoring saat
| upload (Close Case / Pending Case) lewat parameter $targetType — class ini
| lalu MENYARING file tsb, HANYA mengambil baris yang cocok dengan pilihan
| itu; baris jenis lain di file yang sama otomatis DIABAIKAN (dihitung di
| $filteredOut, bukan error).
|
| - Baris Case status "Close Repair (Cust pick up)" / "Close Cancel (Cust
|   pick up)" dianggap jenis "close_case".
| - SEMUA case status lainnya (termasuk jarang/tidak dikenal atau kosong)
|   dianggap jenis "pending_case".
|
| Kalau $targetType = 'close_case'  -> cuma baris close yang di-insert ke
|   close_case (mode APPEND, insertOrIgnore supaya Case ID duplikat dari
|   upload sebelumnya otomatis dilewati).
| Kalau $targetType = 'pending_case' -> cuma baris pending yang di-insert ke
|   pending_case (mode REPLACE — tabelnya WAJIB sudah di-TRUNCATE oleh
|   controller SEBELUM import ini jalan, karena harus jadi snapshot TERKINI).
|
| Mapping posisi kolom & cara parsing tanggal SAMA PERSIS dengan
| BaseCaseImport (lihat class itu untuk detail & alasan pemilihan
| pendekatan posisi-kolom, bukan cocokkan nama header).
|--------------------------------------------------------------------------
*/

class SplitCaseImport implements ToCollection, WithStartRow
{
    protected string $kodeUpload;

    /**
     * Jenis yang mau DIAMBIL dari file (close_case / pending_case). Baris
     * dengan jenis LAIN dari yang dipilih akan otomatis DIABAIKAN — TIDAK
     * masuk ke tabel manapun (lihat $filteredOut).
     */
    protected string $targetType;

    /** Jumlah baris yang berhasil masuk ke close_case pada import ini. */
    public int $closeCaseInserted = 0;

    /** Jumlah baris yang berhasil masuk ke pending_case pada import ini. */
    public int $pendingCaseInserted = 0;

    /**
     * Jumlah baris valid tapi BUKAN jenis yang dipilih ($targetType) —
     * sengaja diabaikan, bukan error. Misal upload Pending Case tapi file-
     * nya campur, baris Close Case di file itu masuk hitungan ini.
     */
    public int $filteredOut = 0;

    /** Baris yang di-skip karena bukan data valid (header terulang, dsb). */
    public int $skippedRows = 0;

    /** Baris close_case yang di-skip karena Case ID sudah ada di DB (upload sebelumnya). */
    public int $duplicateSkipped = 0;

    /** Baris yang di-skip karena Case ID-nya sudah muncul sebelumnya di FILE yang sama. */
    public int $duplicateInFileSkipped = 0;

    /**
     * ★★★ BARU: Jumlah baris valid & jenisnya cocok ($targetType), TAPI
     * prefix Case ID-nya TIDAK COCOK dengan cabang yang dipilih user saat
     * upload — sengaja diabaikan (bukan error). Ini validasi utama supaya
     * kalau user salah pilih dropdown Cabang, data cabang lain tidak ikut
     * kecampur masuk. ★★★
     */
    public int $cabangMismatchSkipped = 0;

    /**
     * Cabang yang DIPILIH user saat upload ("Semarang" atau "Cirebon").
     * SEMUA baris yang lolos akan ditandai dengan nilai ini di kolom
     * `cabang`.
     */
    protected string $cabang;

    /**
     * ★★★ Prefix 3 digit awal Case ID per cabang — INI validasi UTAMA
     * (paling akurat, dari sumbernya langsung), bukan kolom city yang
     * berupa teks bebas & rawan beda-beda penulisan.
     * Semarang selalu diawali "510", Cirebon selalu diawali "795".
     */
    protected array $cabangCaseIdPrefix = [
        'Semarang' => '510',
        'Cirebon'  => '795',
    ];

    protected array $seenCaseIds = [];

    /**
     * Case status yang dianggap "Close Case". Selain daftar ini (termasuk
     * status yang jarang muncul atau kosong) otomatis dianggap "Pending
     * Case", karena artinya case tsb belum resmi ditutup.
     */
    protected array $closeStatuses = [
        'Close Repair (Cust pick up)',
        'Close Cancel (Cust pick up)',
    ];

    /**
     * Urutan HARUS sama persis dengan urutan kolom di file Excel
     * "Transaction Report" (kolom A s/d AZ, header di baris 4) — sama
     * seperti $dbColumns di BaseCaseImport.
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

    public function __construct(string $kodeUpload, string $targetType, string $cabang)
    {
        $this->kodeUpload = $kodeUpload;
        $this->targetType = $targetType; // 'close_case' atau 'pending_case'
        $this->cabang     = $cabang;     // 'Semarang' atau 'Cirebon'
    }

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
        $closeInsertData   = [];
        $pendingInsertData = [];

        foreach ($rows as $row) {

            $row = $row->values();

            // Kolom ke-2 di Excel (index 1) = Case ID. Kalau kosong, anggap
            // baris kosong / akhir data, skip saja.
            $caseId = $row[1] ?? null;

            if ($caseId === null || $caseId === '') {
                continue;
            }

            // Kolom ke-1 (index 0) = "No", seharusnya selalu angka. Kalau
            // bukan angka, berarti ini baris HEADER YANG TERULANG di tengah
            // data (biasa terjadi di file export ISD yang punya header di
            // tiap "halaman"). Skip, jangan dipaksa insert.
            $noValue = $row[0] ?? null;

            if (!is_numeric($noValue)) {
                $this->skippedRows++;
                continue;
            }

            // Cek Case ID sudah muncul sebelumnya di FILE yang sama (file
            // export ISD kadang mengulang sebagian data di tiap "halaman"
            // laporan) — supaya tidak dihitung dua kali.
            $caseIdKey = trim((string) $caseId);

            if (isset($this->seenCaseIds[$caseIdKey])) {
                $this->duplicateInFileSkipped++;
                continue;
            }

            $this->seenCaseIds[$caseIdKey] = true;

            // ★★★ BARU: validasi UTAMA — cocokkan prefix Case ID dengan
            // cabang yang dipilih user. Semarang selalu "510...", Cirebon
            // selalu "795...". Kalau tidak cocok, baris ini PASTI bukan
            // milik cabang yang dipilih — skip di sini juga, sebelum buang
            // waktu memproses kolom lainnya. ★★★
            $expectedPrefix = $this->cabangCaseIdPrefix[$this->cabang] ?? null;

            if ($expectedPrefix !== null && !str_starts_with($caseIdKey, $expectedPrefix)) {
                $this->cabangMismatchSkipped++;
                continue;
            }

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

            // ★★★ Cek jenis case_status baris ini, lalu cocokkan dengan
            // $targetType yang dipilih user. Kalau TIDAK cocok, baris ini
            // diabaikan (bukan error, cuma bukan yang dicari). ★★★
            $rowIsClose = in_array($rowData['case_status'], $this->closeStatuses, true);
            $rowType    = $rowIsClose ? 'close_case' : 'pending_case';

            if ($rowType !== $this->targetType) {
                $this->filteredOut++;
                continue;
            }

            // ★★★ BARU: tandai baris ini dengan cabang yang DIPILIH user
            // (bukan dari kolom city Excel), supaya konsisten & bisa dipakai
            // untuk mode Replace per-cabang di controller. ★★★
            $rowData['cabang'] = $this->cabang;

            if ($rowIsClose) {
                $closeInsertData[] = $rowData;
            } else {
                $pendingInsertData[] = $rowData;
            }
        }

        // close_case: mode APPEND, pakai insertOrIgnore supaya Case ID yang
        // sudah ada dari upload BULAN SEBELUMNYA otomatis dilewati (bukan
        // bikin seluruh chunk gagal).
        foreach (array_chunk($closeInsertData, 500) as $chunk) {
            $insertedCount = DB::table('close_case')->insertOrIgnore($chunk);
            $this->duplicateSkipped   += (count($chunk) - $insertedCount);
            $this->closeCaseInserted += $insertedCount;
        }

        // pending_case: tabelnya SUDAH di-TRUNCATE oleh controller sebelum
        // import ini jalan, jadi tidak mungkin ada duplikat dari upload
        // sebelumnya — insert biasa saja.
        foreach (array_chunk($pendingInsertData, 500) as $chunk) {
            DB::table('pending_case')->insert($chunk);
            $this->pendingCaseInserted += count($chunk);
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