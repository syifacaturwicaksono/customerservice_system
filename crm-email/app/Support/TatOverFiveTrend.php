<?php

namespace App\Support;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| TatOverFiveTrend  (v10)
|--------------------------------------------------------------------------
| SATU-SATUNYA sumber data grafik "TAT > 5 hari" (Case Masuk & Case Close).
| Dipakai bersama oleh:
|   - AuthController@loginForm            (halaman depan sebelum login)
|   - partials/tat-over5-chart.blade.php  (Dashboard CE, Semarang, Cirebon,
|                                          Master — via technician-dashboard
|                                          & master-dashboard)
| Rumus cukup diubah di file ini, semua halaman ikut berubah.
|
| DEFINISI TAT: waktu servis dari barang DITERIMA (received_date) sampai
| SELESAI DIPERBAIKI (finish_date). Jam berhenti di finish_date — waktu
| menunggu pelanggan mengambil barang (closed_date) BUKAN bagian TAT.
|
| SATUAN: HARI KERJA = Senin-SABTU, di luar hari Minggu dan tanggal merah
| (daftar tanggal merah: konstanta HOLIDAYS di bawah). Kolom "TAT case" di
| Excel TIDAK dipakai karena tidak menghitung Sabtu. Rumus:
|     TAT = jumlah hari kerja setelah received_date sampai (dan termasuk)
|           tanggal akhir  ->  received Senin, finish Rabu = 2
|
| Jendela 4 tanggal: hari ini + 3 hari sebelumnya, hari MINGGU DILOMPATI
| (upload data Senin-Sabtu). Mis. hari ini Rabu 30 Sep -> 26, 28, 29, 30.
| Kalau ada hari libur di jendela, tanggalnya tetap tampil dengan data 0.
|
| BENTUK DATA (v10): tiap grafik = batang IW dan batang OW per tanggal,
| dan tiap batang dipecah jadi 2 segmen:
|     over   = case dengan TAT LEBIH DARI 5 hari kerja   (segmen atas)
|     within = case dengan TAT 5 hari kerja atau kurang  (segmen bawah)
| Persen tiap segmen = jumlah segmen / total batang itu (over + within).
|
| PENDING CASE (kunci 'masuk') pada tanggal D
|   = SEMUA case yang jam TAT-nya MASIH BERJALAN pada D (sudah diterima,
|     dan belum finish atau finish_date-nya sesudah D). TAT berjalan =
|     received_date -> D. Sumber: pending_case yang belum finish pada D  +
|     close_case yang baru finish sesudah D. Ini STOK, jadi case yang sama
|     bisa muncul di beberapa tanggal berturut-turut.
|
| CLOSE CASE (kunci 'close') pada tanggal D
|   = SEMUA case yang SELESAI DIPERBAIKI pada D (finish_date = D).
|     TAT akhir = received_date -> finish_date. Sumber: close_case  +
|     pending_case yang sudah finish tapi belum diambil pelanggan.
|
| Case yang warranty_status-nya bukan In Warranty / Out Warranty tidak
| ikut dihitung (tidak masuk batang IW maupun OW).
|--------------------------------------------------------------------------
*/
class TatOverFiveTrend
{
    /** Jumlah tanggal yang tampil: hari ini + 3 hari sebelumnya. */
    public const WINDOW_DAYS = 4;

    /** Batas TAT: hanya case dengan TAT LEBIH DARI angka ini (hari kerja). */
    public const MIN_TAT_DAYS = 5;

    /** true = tanggal hari Minggu tidak ditampilkan di sumbu-X. */
    public const SKIP_SUNDAY = true;

    /**
     * Tanggal merah / cuti bersama (format Y-m-d) yang TIDAK dihitung
     * sebagai hari kerja di rumus TAT. Isi manual tiap tahun, contoh:
     *     '2026-12-25',
     * Dibiarkan kosong = semua hari Senin-Sabtu dihitung hari kerja.
     * (Tanggal merah yang jatuh di hari Minggu tidak perlu diisi.)
     */
    public const HOLIDAYS = [];

    /**
     * @param  string|null $cabang  'Semarang' / 'Cirebon' / null = semua cabang
     */
    public static function build(?string $cabang = null, int $days = self::WINDOW_DAYS, int $minTat = self::MIN_TAT_DAYS): array
    {
        $dates    = self::windowDates($days);
        $firstDay = $dates[0]->toDateString();

        // ---- Ambil data SEKALI, hitung per tanggal di PHP ----------------
        // pending_case: seluruh isi tabel (snapshot terkini, jumlahnya kecil)
        $pendingRows = self::fetch(DB::table('pending_case')->whereNotNull('received_date'), $cabang);

        // close_case: cukup yang finish_date-nya >= tanggal pertama jendela
        // (finish = D untuk Case Close, finish > D untuk Case Masuk).
        $closeRows = self::fetch(
            DB::table('close_case')
                ->whereNotNull('received_date')
                ->whereNotNull('finish_date')
                ->whereDate('finish_date', '>=', $firstDay),
            $cabang
        );

        $dateLabels = [];
        $isoDates   = [];
        $masuk      = self::emptySeries();
        $close      = self::emptySeries();

        foreach ($dates as $date) {
            $d = $date->toDateString();

            $dateLabels[] = $date->translatedFormat('d M');
            $isoDates[]   = $d;

            $m = self::emptyBucket();
            $c = self::emptyBucket();

            // ----- Pending Case: jam TAT masih berjalan di $d -----
            foreach ([$pendingRows, $closeRows] as $set) {
                foreach ($set as $row) {
                    if ($row->rd > $d) {
                        continue;                       // belum diterima pada $d
                    }
                    if ($row->fd !== null && $row->fd <= $d) {
                        continue;                       // sudah selesai diperbaiki pada/sebelum $d
                    }

                    self::tally($m, $row->warranty_status, self::workingDays($row->rd, $d) > $minTat);
                }
            }

            // ----- Close Case: selesai diperbaiki di $d (TAT akhir) -----
            foreach ([$pendingRows, $closeRows] as $set) {
                foreach ($set as $row) {
                    if ($row->fd !== $d) {
                        continue;
                    }

                    self::tally($c, $row->warranty_status, self::workingDays($row->rd, $row->fd) > $minTat);
                }
            }

            self::push($masuk, $m);
            self::push($close, $c);
        }

        return [
            'dateLabels' => $dateLabels,
            'dates'      => $isoDates,
            'windowDays' => $days,
            'minTat'     => $minTat,
            'masuk'      => $masuk,   // ['iw' => ['over' => [...], 'within' => [...]], 'ow' => [...]]
            'close'      => $close,
        ];
    }

    /**
     * TAT dalam HARI KERJA = jumlah hari kerja SETELAH $from sampai dan
     * TERMASUK $to (hari Minggu & tanggal merah tidak dihitung; Sabtu
     * dihitung). Minimal 0. $from & $to berformat Y-m-d.
     *
     * Contoh: Senin -> Rabu = 2;  Jumat -> Senin = 2 (Sabtu, Senin).
     */
    public static function workingDays(string $from, string $to): int
    {
        $a = new DateTimeImmutable($from);
        $b = new DateTimeImmutable($to);

        if ($b <= $a) {
            return 0;
        }

        $n = $a->diff($b)->days;               // jumlah hari di (a, b]
        $w = ((int) $a->format('N') % 7) + 1;  // hari-ke berapa (1=Senin..7=Minggu) dari hari pertama = a+1

        // jumlah hari Minggu di antara $n hari berurutan yang dimulai dari hari ke-$w
        $sundays = intdiv($n, 7) + ((7 - $w) < ($n % 7) ? 1 : 0);
        $count   = $n - $sundays;

        // kurangi tanggal merah yang jatuh di (a, b] dan bukan hari Minggu
        foreach (array_unique(self::HOLIDAYS) as $h) {
            $hd = new DateTimeImmutable($h);

            if ($hd > $a && $hd <= $b && (int) $hd->format('N') !== 7) {
                $count--;
            }
        }

        return max(0, $count);
    }

    /** @return \Carbon\Carbon[] urut LAMA -> BARU, berakhir di hari ini */
    private static function windowDates(int $days): array
    {
        $dates  = [];
        $cursor = now()->startOfDay();

        while (count($dates) < $days) {
            if (! self::SKIP_SUNDAY || ! $cursor->isSunday()) {
                $dates[] = $cursor->copy();
            }
            $cursor->subDay();
        }

        return array_reverse($dates);
    }

    /** rd/fd = tanggal saja (Y-m-d), fd null kalau belum finish. */
    private static function fetch($query, ?string $cabang): array
    {
        if ($cabang) {
            $query->where('cabang', $cabang);
        }

        return $query
            ->selectRaw('DATE(received_date) as rd, DATE(finish_date) as fd, warranty_status')
            ->get()
            ->all();
    }

    private static function emptyBucket(): array
    {
        return ['iw' => ['over' => 0, 'within' => 0], 'ow' => ['over' => 0, 'within' => 0]];
    }

    private static function emptySeries(): array
    {
        return ['iw' => ['over' => [], 'within' => []], 'ow' => ['over' => [], 'within' => []]];
    }

    /** Masukkan hitungan satu tanggal ke deret per-tanggal. */
    private static function push(array &$series, array $bucket): void
    {
        foreach (['iw', 'ow'] as $w) {
            $series[$w]['over'][]   = $bucket[$w]['over'];
            $series[$w]['within'][] = $bucket[$w]['within'];
        }
    }

    private static function tally(array &$bucket, ?string $warranty, bool $over): void
    {
        $key = $warranty === 'In Warranty' ? 'iw' : ($warranty === 'Out Warranty' ? 'ow' : null);

        if ($key !== null) {
            $bucket[$key][$over ? 'over' : 'within']++;
        }
    }
}