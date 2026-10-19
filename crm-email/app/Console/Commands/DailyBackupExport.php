<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * ✅ Backup otomatis harian ke file Excel (CSV).
 *
 * Meng-export 4 tabel: email_logs, wa_logs, pending_case, close_case
 * ke file .csv, disimpan di storage/app/backups/<tanggal>/.
 *
 * Kolom diambil OTOMATIS dari struktur tabel (Schema::getColumnListing),
 * jadi tetap ikut lengkap walau kolomnya nanti berubah/bertambah.
 *
 * ★★★ BARU: tabel yang punya kolom `cabang` (pending_case, close_case)
 * di-export jadi BEBERAPA file terpisah, 1 file per cabang (misal
 * pending_case-Semarang-2026-09-12_170003.csv,
 * pending_case-Cirebon-2026-09-12_170003.csv). Baris lama yang belum
 * punya cabang (cabang kosong/NULL) tetap dibackup, dikelompokkan
 * sebagai file "...-TanpaCabang-...csv". Tabel tanpa kolom cabang
 * (email_logs, wa_logs) tetap 1 file gabungan seperti semula.
 *
 * Cara jalanin manual (tes): php artisan backup:export-daily
 * Otomatis tiap hari: didaftarkan di app/Console/Kernel.php (lihat catatan
 * di file kernel-schedule-snippet.txt).
 */
class DailyBackupExport extends Command
{
    protected $signature = 'backup:export-daily';

    protected $description = 'Export harian email_logs, wa_logs, pending_case, close_case ke file Excel (CSV) untuk backup otomatis';

    /**
     * Backup lebih lama dari sekian hari otomatis dihapus supaya disk
     * server tidak penuh. Ubah angka ini kalau mau retensi lebih lama/pendek.
     */
    protected $retentionDays = 30;

    protected $tables = ['email_logs', 'wa_logs', 'pending_case', 'close_case'];

    public function handle()
    {
        $now = Carbon::now();
        $dateFolder = $now->format('Y-m-d');
        $timeSuffix = $now->format('His');

        $this->info("Mulai backup harian ({$dateFolder})...");

        foreach ($this->tables as $table) {
            $this->exportTable($table, $dateFolder, $timeSuffix);
        }

        $this->cleanupOldBackups();

        $this->info('Backup harian selesai.');
    }

    protected function exportTable($table, $dateFolder, $timeSuffix)
    {
        if (!Schema::hasTable($table)) {
            $this->warn("⚠ Tabel `{$table}` tidak ditemukan, dilewati.");
            return;
        }

        $columns = Schema::getColumnListing($table);
        $folder = "backups/{$dateFolder}";

        // ★★★ BARU: kalau tabel ini punya kolom `cabang` (pending_case,
        // close_case), backup dipecah jadi 1 file PER CABANG — supaya
        // halaman Backup History bisa difilter per cabang langsung dari
        // nama file, tanpa perlu buka & saring isi file gabungan. Tabel
        // yang tidak punya kolom cabang (email_logs, wa_logs) tetap 1
        // file gabungan seperti semula, tidak berubah.
        if (in_array('cabang', $columns, true)) {
            $this->exportTableByCabang($table, $columns, $folder, $dateFolder, $timeSuffix);
            return;
        }

        $fileName = "{$table}-{$dateFolder}_{$timeSuffix}.csv";
        $path = "{$folder}/{$fileName}";

        $this->writeCsvForTable($columns, $path, function () use ($table) {
            return DB::table($table)->orderBy('id');
        });

        $rowCount = DB::table($table)->count();
        $this->info("  ✓ {$table} ({$rowCount} baris) → storage/app/{$path}");
    }

    /**
     * ★★★ BARU: Export 1 tabel jadi BEBERAPA file CSV, 1 file per nilai
     * `cabang` yang ADA datanya di tabel itu (misal Semarang, Cirebon).
     * Baris dengan cabang kosong/NULL (data lama dari sebelum fitur
     * filter cabang ada) TETAP dibackup, dikelompokkan sebagai
     * "TanpaCabang" — supaya tidak ada data yang diam-diam hilang dari
     * backup cuma karena belum ditandai cabangnya.
     */
    protected function exportTableByCabang($table, $columns, $folder, $dateFolder, $timeSuffix)
    {
        $cabangList = DB::table($table)
            ->select('cabang')
            ->distinct()
            ->pluck('cabang')
            ->map(function ($c) {
                return ($c === null || trim((string) $c) === '') ? null : $c;
            })
            ->unique()
            ->values();

        if ($cabangList->isEmpty()) {
            $cabangList = collect([null]);
        }

        foreach ($cabangList as $cabang) {
            $label = $cabang ?: 'TanpaCabang';

            // Nama file cuma boleh huruf/angka/strip/underscore, supaya
            // aman dipakai di URL & tidak tergantung karakter aneh dari
            // isi kolom cabang.
            $safeLabel = preg_replace('/[^A-Za-z0-9_-]/', '', $label) ?: 'TanpaCabang';

            $fileName = "{$table}-{$safeLabel}-{$dateFolder}_{$timeSuffix}.csv";
            $path = "{$folder}/{$fileName}";

            $buildQuery = function () use ($table, $cabang) {
                $query = DB::table($table)->orderBy('id');

                if ($cabang === null) {
                    $query->where(function ($q) {
                        $q->whereNull('cabang')->orWhere('cabang', '');
                    });
                } else {
                    $query->where('cabang', $cabang);
                }

                return $query;
            };

            $this->writeCsvForTable($columns, $path, $buildQuery);

            $rowCount = $buildQuery()->count();
            $this->info("  ✓ {$table} [{$label}] ({$rowCount} baris) → storage/app/{$path}");
        }
    }

    /**
     * Helper bersama: tulis hasil query ($buildQuery dipanggil FRESH tiap
     * kali dibutuhkan, supaya tidak ada state chunk() yang "nyangkut" dan
     * mengacaukan query builder yang sama dipakai ulang).
     */
    protected function writeCsvForTable($columns, $path, callable $buildQuery)
    {
        $handle = fopen('php://temp', 'w+');

        // ✅ BOM UTF-8 supaya Excel baca karakter dengan benar
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $columns);

        // ✅ CHUNK supaya aman untuk tabel besar (pending_case/close_case
        // bisa berisi ribuan baris) tanpa menghabiskan memory server.
        $buildQuery()->chunk(500, function ($rows) use ($handle, $columns) {
            foreach ($rows as $row) {
                $rowArr = (array) $row;
                $line = [];
                foreach ($columns as $col) {
                    $line[] = $this->sanitizeForCsv($rowArr[$col] ?? '');
                }
                fputcsv($handle, $line);
            }
        });

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        Storage::disk('local')->put($path, $csvContent);
    }

    /**
     * ✅ BARU: Bersihkan baris baru (enter) dalam isi kolom, ganti jadi
     * spasi. Kalau ada baris baru dibiarkan mentah, saat file .csv dibuka
     * langsung (double-click) di Excel, 1 baris data database bisa "pecah"
     * jadi beberapa baris terpisah dan kolomnya jadi geser/berantakan.
     * Dengan ini, 1 baris database SELALU jadi 1 baris Excel.
     */
    protected function sanitizeForCsv($value)
    {
        if ($value === null) {
            return '';
        }

        $value = str_replace(["\r\n", "\r", "\n"], ' ', (string) $value);

        // rapikan spasi ganda hasil penggantian, biar tidak "kata    kata"
        return trim(preg_replace('/\s+/', ' ', $value));
    }

    /**
     * Hapus folder backup yang lebih tua dari $retentionDays hari.
     */
    protected function cleanupOldBackups()
    {
        $cutoff = Carbon::now()->subDays($this->retentionDays);

        $folders = Storage::disk('local')->directories('backups');

        foreach ($folders as $folder) {
            $folderDate = basename($folder);

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $folderDate)) {
                continue;
            }

            if (Carbon::parse($folderDate)->lt($cutoff)) {
                Storage::disk('local')->deleteDirectory($folder);
                $this->info("  🗑 Backup lama dihapus: {$folder}");
            }
        }
    }
}