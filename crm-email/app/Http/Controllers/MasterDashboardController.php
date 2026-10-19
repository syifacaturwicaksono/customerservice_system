<?php

namespace App\Http\Controllers;

use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| MasterDashboardController
|--------------------------------------------------------------------------
| Dashboard Master — Notulen Meeting Project Dashboard,
| 9 September 2026, poin 2. Ada 10 sub-poin, dikerjakan BERTAHAP (bukan
| sekaligus, sesuai roadmap):
|
|   a) Total Case yang masuk (IW & OW)              <- SELESAI
|   b) Total Case yang close (IW & OW)               <- SELESAI
|   c) Aging case CE 7 hari, 14 hari, >14 hari        <- SELESAI
|   d) Case Part Order                                <- SELESAI
|   e) Case Part Request Logistic                     <- SELESAI
|   f) Case WIP                                       <- SELESAI
|   g) Case IPG & PSG                                 <- SELESAI
|   h) Close repair — yang handle siapa               <- SELESAI
|   i) Buka case yang masih berjalan di hari sebelumnya
|      (fondasi tabel pending_case_history sudah ada) <- SELESAI
|   j) Case non-part juga bisa ditampilkan            <- SELESAI (kartu
|      tersendiri "Case Non-Part per CE", pakai data yang sama dengan
|      kolom "tanpa Part" di sub-poin f — tidak perlu query baru.)
|
| DEFINISI angka di tahap ini:
| - "Total Case MASUK"  = SEMUA case yang pernah diterima = yang sudah
|                          close (close_case, mode APPEND/histori penuh)
|                          + yang masih pending (pending_case, snapshot
|                          TERKINI). Ini representasi total workload.
| - "Total Case CLOSE"  = subset yang sudah close saja (dari close_case).
|
| Sama seperti Dashboard CE, ada filter Cabang (Semarang / Cirebon /
| kosong = Semua Cabang).
|--------------------------------------------------------------------------
*/
class MasterDashboardController extends Controller
{
    public function index(Request $request)
    {
        $cabang = $request->input('cabang');

        // ★★★ BARU: Jenis Monitoring (Pending Case / Close Case) — sama
        // seperti Dashboard CE. Cuma memengaruhi kartu-kartu DETAIL (Aging,
        // Part Order, Part Request Logistic, WIP, IPG&PSG, Close Repair,
        // Riwayat Pending), karena masing-masing memang cuma relevan untuk
        // salah satu jenis. Kartu "Total Case Masuk" & "Total Case Close"
        // di atas TETAP tampil apa adanya (gabungan/rekap), tidak
        // dipengaruhi filter ini.
        $jenisMonitoring = $request->input('jenis_monitoring', 'pending_case');

        // ★★★ BARU: Filter Bulan — cuma muncul & berlaku saat Jenis
        // Monitoring = Close Case, karena close_case sifatnya AKUMULATIF
        // (data lama tidak pernah dihapus, terus menumpuk tiap upload).
        // Tanpa filter bulan, kartu-kartu Close (IPG&PSG versi Close,
        // Close Repair per CE) akan menjumlahkan SEMUA bulan jadi satu
        // angka yang tidak representatif — pola & alasan ini SAMA PERSIS
        // seperti filter Bulan di Dashboard CE. Kartu "Total Case Masuk" &
        // "Total Case Close" di atas TETAP tidak difilter bulan (tetap
        // rekap keseluruhan), supaya konsisten dengan penjelasan yang
        // sudah ditampilkan ke user bahwa kartu itu tidak ikut berubah.
        $availableMonths = [];
        $bulanTerpilih   = null;

        if ($jenisMonitoring === 'close_case') {

            $availableMonthsQuery = DB::table('close_case')
                ->whereNotNull('received_date');

            if ($cabang) {
                $availableMonthsQuery->where('cabang', $cabang);
            }

            $availableMonths = $availableMonthsQuery
                ->selectRaw("DATE_FORMAT(received_date, '%Y-%m') as ym")
                ->distinct()
                ->orderByDesc('ym')
                ->pluck('ym')
                ->all();

            $bulanTerpilih = $request->input('bulan');

            if (!$bulanTerpilih || !in_array($bulanTerpilih, $availableMonths)) {
                $bulanTerpilih = $availableMonths[0] ?? null;
            }
        }

        $bulanLabel = $bulanTerpilih
            ? \Carbon\Carbon::createFromFormat('Y-m', $bulanTerpilih)->translatedFormat('F Y')
            : null;

        // ----- Total Case CLOSE (IW & OW), dari close_case -----
        $closeQuery = DB::table('close_case');

        if ($cabang) {
            $closeQuery->where('cabang', $cabang);
        }

        $closeTotals = (clone $closeQuery)
            ->selectRaw("
                SUM(CASE WHEN warranty_status = 'In Warranty' THEN 1 ELSE 0 END) as iw,
                SUM(CASE WHEN warranty_status = 'Out Warranty' THEN 1 ELSE 0 END) as ow,
                COUNT(*) as total
            ")
            ->first();

        // ----- Case yang MASIH PENDING (IW & OW), snapshot terkini dari pending_case -----
        $pendingQuery = DB::table('pending_case');

        if ($cabang) {
            $pendingQuery->where('cabang', $cabang);
        }

        $pendingTotals = (clone $pendingQuery)
            ->selectRaw("
                SUM(CASE WHEN warranty_status = 'In Warranty' THEN 1 ELSE 0 END) as iw,
                SUM(CASE WHEN warranty_status = 'Out Warranty' THEN 1 ELSE 0 END) as ow,
                COUNT(*) as total
            ")
            ->first();

        // ----- Total Case MASUK = close + pending -----
        $closeIw    = (int) ($closeTotals->iw ?? 0);
        $closeOw    = (int) ($closeTotals->ow ?? 0);
        $closeTotal = (int) ($closeTotals->total ?? 0);

        $pendingIw    = (int) ($pendingTotals->iw ?? 0);
        $pendingOw    = (int) ($pendingTotals->ow ?? 0);
        $pendingTotal = (int) ($pendingTotals->total ?? 0);

        $masukIw    = $closeIw + $pendingIw;
        $masukOw    = $closeOw + $pendingOw;
        $masukTotal = $closeTotal + $pendingTotal;

        $lastUpdateClose   = (clone $closeQuery)->max('created_at');
        $lastUpdatePending = (clone $pendingQuery)->max('created_at');

        // ★★★ DIUBAH: kartu "Case masuk" & "Case close" di atas dulu SELALU
        // menampilkan rekap sepanjang waktu (lihat komentar lama di bagian
        // atas file ini). Sekarang dibatasi jadi jendela 4 hari kalender —
        // hari ini + 3 hari sebelumnya — dan bisa digeser lewat tab, sama
        // seperti pola tab di grafik Aging/Part Order/dst. Definisi tanggal
        // per kartu:
        // - "Case masuk" per hari = received_date jatuh di hari itu
        //   (gabungan pending_case yang masih berjalan + close_case yang
        //   sudah selesai — definisinya sama seperti $masukTotal di atas,
        //   cuma difilter per hari, bukan sepanjang waktu).
        // - "Case close" per hari = closed_date (kolom "Closed date" di
        //   sheet Transaction Report Excel, disimpan sebagai `closed_date`
        //   oleh SplitCaseImport) jatuh di hari itu — tanggal case BENAR-
        //   BENAR ditutup (status Close Repair / Close Cancel, cust sudah
        //   pick up), bukan tanggal diterima, tanggal repair selesai
        //   (`finish_date`, bisa beda hari dengan closed_date — repair bisa
        //   kelar duluan sebelum unit di-pickup), ataupun tanggal upload
        //   filenya. ★★★ DIUBAH dari `finish_date` ke `closed_date` — lihat
        //   diskusi: 478 dari 549 case close di sample Excel ternyata beda
        //   hari antara finish_date & closed_date, jadi kartu ini sempat
        //   salah hitung "case close per hari". ★★★ Case close yang
        //   closed_date-nya kosong (data lama / tidak terisi di Excel)
        //   tidak akan muncul di kartu per-hari ini, walau tetap terhitung
        //   di kartu-kartu lain yang tidak difilter tanggal.
        $countIwOwTotal = function (string $table, string $dateColumn, string $date, ?string $cabangFilter) {
            $q = DB::table($table)->whereDate($dateColumn, $date);

            if ($cabangFilter) {
                $q->where('cabang', $cabangFilter);
            }

            $row = $q->selectRaw("
                SUM(CASE WHEN warranty_status = 'In Warranty' THEN 1 ELSE 0 END) as iw,
                SUM(CASE WHEN warranty_status = 'Out Warranty' THEN 1 ELSE 0 END) as ow,
                COUNT(*) as total
            ")->first();

            return [
                'iw'    => (int) ($row->iw ?? 0),
                'ow'    => (int) ($row->ow ?? 0),
                'total' => (int) ($row->total ?? 0),
            ];
        };

        $todayLedger = now()->toDateString();

        // ★★★ BARU: dulu selalu 4 hari KALENDER terakhir (hari ini + 3
        // sebelumnya), termasuk Minggu walau Minggu libur kerja (tidak ada
        // aktivitas case masuk/close). Sekarang pakai lastWorkingDays() —
        // jalan mundur dari hari ini, LOMPATI Minggu, sampai dapat 4 hari
        // kerja. Kalau hari ini kebetulan Minggu, frame "Hari ini" otomatis
        // tidak muncul (diganti hari kerja terakhir), karena memang tidak
        // ada aktivitas yang perlu dibandingkan di hari libur.
        $ledgerFrames = collect($this->lastWorkingDays(4))
            ->map(function ($date) use ($countIwOwTotal, $cabang, $todayLedger) {
                $pendingDay       = $countIwOwTotal('pending_case', 'received_date', $date, $cabang);
                $closeReceivedDay = $countIwOwTotal('close_case', 'received_date', $date, $cabang);
                $closeFinishedDay = $countIwOwTotal('close_case', 'closed_date', $date, $cabang);

                $masukDay = [
                    'iw'    => $pendingDay['iw'] + $closeReceivedDay['iw'],
                    'ow'    => $pendingDay['ow'] + $closeReceivedDay['ow'],
                    'total' => $pendingDay['total'] + $closeReceivedDay['total'],
                ];

                return [
                    'date'           => $date,
                    'label'          => $date === $todayLedger
                        ? 'Hari ini'
                        : \Carbon\Carbon::parse($date)->translatedFormat('l, d M'),
                    'masuk'          => $masukDay,
                    'pending'        => $pendingDay,
                    'closeFromMasuk' => $closeReceivedDay,
                    'close'          => $closeFinishedDay,
                ];
            })
            ->values()
            ->all();

        // ★★★ BARU sub-poin (c): Aging case per CE — 0-7 hari, 8-14 hari,
        // >14 hari. Aging cuma relevan untuk case yang MASIH PENDING (case
        // yang sudah close tidak "menua" lagi), jadi dihitung dari
        // pending_case saja, dibandingkan received_date terhadap NOW().
        // Daftar CE diambil dari tabel technicians (Master Data), sama
        // persis seperti pola yang dipakai Dashboard CE — teknisi "Agung"
        // dikecualikan karena sudah tidak aktif.
        // ★★★ DIUBAH: sekarang teknisi milik cabang tertentu (kolom `cabang`
        // di tabel technicians) — kalau ada filter Cabang aktif, cuma
        // teknisi cabang itu yang dimunculkan. Ada juga nama yang SAMA di
        // 2 cabang berbeda (mis. "Bayu" Semarang & Cirebon) — supaya tidak
        // ketuker, tiap teknisi dikasih `display_name` yang otomatis
        // ditambah "(Cabang)" KALAU memang ada nama kembar. Pola ini sama
        // persis dengan yang dipakai di Dashboard CE.
        $technicians = Technician::where('name', '!=', 'Agung')
            ->when($cabang, function ($q) use ($cabang) {
                $q->where('cabang', $cabang);
            })
            ->orderBy('name')
            ->get();

        $nameCounts = $technicians->groupBy(fn ($t) => strtolower(trim($t->name)))->map->count();

        $technicians = $technicians->map(function ($tech) use ($nameCounts) {
            $isDuplicateName = ($nameCounts[strtolower(trim($tech->name))] ?? 1) > 1;
            $tech->display_name = $isDuplicateName && $tech->cabang
                ? "{$tech->name} ({$tech->cabang})"
                : $tech->name;
            return $tech;
        });

        // ★★★ DIUBAH: sub-poin (c)-(g) sekarang dihitung lewat method
        // computePendingBreakdown() yang bisa dipakai ULANG — baik untuk
        // data LIVE (pending_case, di bawah ini) maupun data HISTORIS
        // (pending_case_history per snapshot_date, di sub-poin i lebih
        // bawah) — supaya "Riwayat Pending per Tanggal" bisa menampilkan
        // SEMUA tabel breakdown yang sama, bukan cuma daftar mentah.
        $liveBreakdown = $this->computePendingBreakdown($technicians, 'pending_case', $cabang);

        $agingRows                = $liveBreakdown['agingRows'];
        $agingGrandTotal          = $liveBreakdown['agingGrandTotal'];
        $partOrderRows            = $liveBreakdown['partOrderRows'];
        $partOrderGrandTotal      = $liveBreakdown['partOrderGrandTotal'];
        $partReqLogisticRows      = $liveBreakdown['partReqLogisticRows'];
        $partReqLogisticGrandTotal = $liveBreakdown['partReqLogisticGrandTotal'];
        $wipRows                  = $liveBreakdown['wipRows'];
        $wipGrandTotal            = $liveBreakdown['wipGrandTotal'];
        $towerRows                = $liveBreakdown['towerRows'];
        $towerGrandTotal          = $liveBreakdown['towerGrandTotal'];

        // ★★★ BARU: versi IPG & PSG dari close_case (dipakai saat Jenis
        // Monitoring = Close Case). Pola sama persis dengan $towerRows di
        // atas, cuma beda tabel sumber.
        $closeTowerRows = [];
        $closeTowerGrandTotal = ['ipg' => 0, 'psg' => 0];

        foreach ($technicians as $tech) {
            $closeTowerQuery = DB::table('close_case')
                ->where('ce_name', $tech->name);

            if ($tech->cabang) {
                $closeTowerQuery->where('cabang', $tech->cabang);
            } elseif ($cabang) {
                $closeTowerQuery->where('cabang', $cabang);
            }

            if ($bulanTerpilih) {
                $closeTowerQuery->whereRaw("DATE_FORMAT(received_date, '%Y-%m') = ?", [$bulanTerpilih]);
            }

            $closeTowerRow = $closeTowerQuery
                ->selectRaw("
                    SUM(CASE WHEN product_tower = 'IPG' THEN 1 ELSE 0 END) as ipg,
                    SUM(CASE WHEN product_tower = 'PSG' THEN 1 ELSE 0 END) as psg
                ")
                ->first();

            $ipg = (int) ($closeTowerRow->ipg ?? 0);
            $psg = (int) ($closeTowerRow->psg ?? 0);

            $closeTowerRows[] = [
                'name'  => $tech->display_name,
                'ipg'   => $ipg,
                'psg'   => $psg,
                'total' => $ipg + $psg,
            ];

            $closeTowerGrandTotal['ipg'] += $ipg;
            $closeTowerGrandTotal['psg'] += $psg;
        }

        $closeTowerGrandTotal['total'] = $closeTowerGrandTotal['ipg'] + $closeTowerGrandTotal['psg'];

        // ★★★ BARU sub-poin (h): Close Repair — siapa yang handle, per CE.
        // close_case menyimpan 2 jenis status "close": "Close Repair (Cust
        // pick up)" (case benar-benar diperbaiki) dan "Close Cancel (Cust
        // pick up)" (case dibatalkan customer). Poin ini secara spesifik
        // minta "Close REPAIR", jadi Close Cancel ditampilkan terpisah
        // sebagai info tambahan, bukan digabung ke angka utama.
        $closeRepairRows = [];
        $closeRepairGrandTotal = ['repair' => 0, 'cancel' => 0];

        foreach ($technicians as $tech) {
            $closeStatusQuery = DB::table('close_case')
                ->where('ce_name', $tech->name);

            if ($tech->cabang) {
                $closeStatusQuery->where('cabang', $tech->cabang);
            } elseif ($cabang) {
                $closeStatusQuery->where('cabang', $cabang);
            }

            if ($bulanTerpilih) {
                $closeStatusQuery->whereRaw("DATE_FORMAT(received_date, '%Y-%m') = ?", [$bulanTerpilih]);
            }

            $closeStatusRow = $closeStatusQuery
                ->selectRaw("
                    SUM(CASE WHEN case_status = 'Close Repair (Cust pick up)' THEN 1 ELSE 0 END) as repair,
                    SUM(CASE WHEN case_status = 'Close Cancel (Cust pick up)' THEN 1 ELSE 0 END) as cancel
                ")
                ->first();

            $repair = (int) ($closeStatusRow->repair ?? 0);
            $cancel = (int) ($closeStatusRow->cancel ?? 0);

            $closeRepairRows[] = [
                'name'   => $tech->display_name,
                'repair' => $repair,
                'cancel' => $cancel,
                'total'  => $repair + $cancel,
            ];

            $closeRepairGrandTotal['repair'] += $repair;
            $closeRepairGrandTotal['cancel'] += $cancel;
        }

        $closeRepairGrandTotal['total'] = $closeRepairGrandTotal['repair'] + $closeRepairGrandTotal['cancel'];

        // ★★★ BARU: Rekap "Logged By & Closed By" — siapa yang nge-LOG case
        // ke sistem vs siapa yang nge-CLOSE, per orang. Kolom logged_by &
        // closed_by SUDAH otomatis tersimpan di close_case sejak awal
        // (bagian dari sheet Transaction Report), ini cuma bikin rekapnya —
        // tidak perlu tabel baru sama sekali.
        //
        // Nama di sini BUKAN dari Master Data Teknisi (logged_by/closed_by
        // bisa jadi staf admin, beda dari CE yang benar-benar mengerjakan
        // perbaikan), jadi daftar namanya diambil LANGSUNG dari nilai unik
        // yang ada di kedua kolom itu.
        //
        // ★★★ BARU: whitelist — staf admin yang BENERAN nge-log/nge-close
        // case cuma 4 orang ini (dikonfirmasi user). Nilai lain yang
        // kadang nyelip di kolom logged_by/closed_by (misal nama CE, atau
        // sisa "adm-hpscrb") diabaikan/tidak ditampilkan di tabel ini.
        // Dibuat case-insensitive (LOWER()) karena data Excel kadang beda
        // kapitalisasi (mis. "Sisca K" vs "sisca k").
        $loggedClosedWhitelist = ['Heny', 'Ulin', 'Cs2', 'Sisca K'];
        $loggedClosedWhitelistLower = array_map('strtolower', $loggedClosedWhitelist);

        $loggedClosedQuery = DB::table('close_case');

        if ($cabang) {
            $loggedClosedQuery->where('cabang', $cabang);
        }

        if ($bulanTerpilih) {
            $loggedClosedQuery->whereRaw("DATE_FORMAT(received_date, '%Y-%m') = ?", [$bulanTerpilih]);
        }

        $loggedCountsRaw = (clone $loggedClosedQuery)
            ->whereNotNull('logged_by')
            ->whereRaw('LOWER(TRIM(logged_by)) IN (' . implode(',', array_fill(0, count($loggedClosedWhitelistLower), '?')) . ')', $loggedClosedWhitelistLower)
            ->selectRaw('logged_by as name, COUNT(*) as total')
            ->groupBy('logged_by')
            ->get();

        $closedCountsRaw = (clone $loggedClosedQuery)
            ->whereNotNull('closed_by')
            ->whereRaw('LOWER(TRIM(closed_by)) IN (' . implode(',', array_fill(0, count($loggedClosedWhitelistLower), '?')) . ')', $loggedClosedWhitelistLower)
            ->selectRaw('closed_by as name, COUNT(*) as total')
            ->groupBy('closed_by')
            ->get();

        // Gabungkan varian kapitalisasi (mis. "Sisca K" & "sisca k") ke nama
        // KANONIK di whitelist, supaya tidak jadi 2 baris terpisah.
        $normalizeToCanonical = function ($rows) use ($loggedClosedWhitelist, $loggedClosedWhitelistLower) {
            $result = collect();

            foreach ($rows as $row) {
                $idx = array_search(strtolower(trim($row->name)), $loggedClosedWhitelistLower, true);

                if ($idx === false) {
                    continue;
                }

                $canonical = $loggedClosedWhitelist[$idx];
                $result[$canonical] = ($result[$canonical] ?? 0) + $row->total;
            }

            return $result;
        };

        $loggedCounts = $normalizeToCanonical($loggedCountsRaw);
        $closedCounts = $normalizeToCanonical($closedCountsRaw);

        $loggedClosedNames = $loggedCounts->keys()
            ->merge($closedCounts->keys())
            ->unique()
            ->sort()
            ->values();

        $loggedClosedRows = $loggedClosedNames->map(function ($name) use ($loggedCounts, $closedCounts) {
            return [
                'name'   => $name,
                'logged' => (int) ($loggedCounts[$name] ?? 0),
                'closed' => (int) ($closedCounts[$name] ?? 0),
            ];
        })->all();

        $loggedClosedGrandTotal = [
            'logged' => (int) $loggedCounts->sum(),
            'closed' => (int) $closedCounts->sum(),
        ];

        // ★★★ BARU sub-poin (i): Buka case yang masih berjalan di hari
        // sebelumnya. Ambil daftar tanggal yang TERSEDIA di
        // pending_case_history (kalau cabang dipilih, cuma tanggal yang
        // punya snapshot untuk cabang itu), lalu kalau user pilih salah
        // satu tanggal, tampilkan detail case-nya (bukan cuma angka rekap
        // seperti kartu-kartu lain di atas — poin ini secara spesifik minta
        // bisa "BUKA" case-nya).
        $availDatesQuery = DB::table('pending_case_history');

        if ($cabang) {
            $availDatesQuery->where('cabang', $cabang);
        }

        $availableSnapshotDates = $availDatesQuery
            ->select('snapshot_date')
            ->distinct()
            ->orderByDesc('snapshot_date')
            ->pluck('snapshot_date');

        // ★★★ DIUBAH: dulu user pilih SATU tanggal snapshot lewat dropdown
        // ("Riwayat Pending per Tanggal"), lalu semua tabel breakdown
        // ditampilkan ulang untuk tanggal itu. Sekarang diganti otomatis:
        // grafik detail (Aging, Part Order, Part Request Logistic, WIP,
        // IPG&PSG) langsung membawa data 3 hari kalender TERAKHIR yang
        // punya snapshot + hari ini, supaya bisa dibandingkan lewat tab
        // tanpa perlu memilih tanggal manual.
        // Dipakai computePendingBreakdown() YANG SAMA seperti data live di
        // atas, cuma sumbernya pending_case_history + filter snapshot_date.
        // Aging tetap dihitung relatif ke TANGGAL SNAPSHOT-nya sendiri
        // (bukan hari ini), supaya akurat menggambarkan kondisi
        // "seandainya dilihat di tanggal itu".
        $recentDays = [];

        if ($jenisMonitoring === 'pending_case') {
            $today = now()->toDateString();

            $recentPastDates = $availableSnapshotDates
                ->filter(fn ($d) => \Carbon\Carbon::parse($d)->toDateString() !== $today)
                ->filter(fn ($d) => ! \Carbon\Carbon::parse($d)->isSunday()) // ★★★ BARU: libur kerja, dikecualikan
                ->take(3)
                ->values();

            foreach ($recentPastDates as $date) {
                $recentDays[] = [
                    'date'      => $date,
                    'label'     => \Carbon\Carbon::parse($date)->translatedFormat('l, d M'),
                    'breakdown' => $this->computePendingBreakdown(
                        $technicians,
                        'pending_case_history',
                        $cabang,
                        function ($q) use ($date) {
                            $q->where('snapshot_date', $date);
                        },
                        $date
                    ),
                ];
            }

            // Urutkan dari paling lama ke paling baru (kiri = lama, kanan =
            // baru), lalu "Hari ini" ditaruh paling kanan sebagai default.
            $recentDays = array_reverse($recentDays);

            $recentDays[] = [
                'date'      => $today,
                'label'     => 'Hari ini',
                'breakdown' => $liveBreakdown,
            ];
        }

        // ★★★ BARU: pie chart komposisi 6 status "menunggu part /
        // quotation" — data LIVE dari pending_case (bukan snapshot),
        // dijumlah lintas semua CE, cuma dipakai untuk Pending Case
        // karena status ini tidak ada di alur Close Case. Query berdiri
        // sendiri (bukan lewat computePendingBreakdown()) karena yang
        // dibutuhkan cuma total per status, bukan pecahan per CE.
        // Asumsi sama seperti Dashboard CE: "Part Order" = "Part Order
        // Logistic", "Part Available" = "Part Available (Part Ready)"
        // (dipendekkan biar rapi), dan "Part Request" dihitung sebagai
        // status tersendiri, terpisah dari "Part Request Logistic".
        $statusPieMap = [
            'Part Request'        => 'Part Request',
            'Part Order Logistic' => 'Part Order Logistic',
            'Part Available'      => 'Part Available (Part Ready)',
            'Quotation Request'   => 'Quotation Request',
            'Quotation Pending'   => 'Quotation Pending',
            'Quotation Approved'  => 'Quotation Approved',
        ];

        $statusPieData = [];

        if ($jenisMonitoring === 'pending_case') {
            $statusPieQuery = DB::table('pending_case')->whereIn('case_status', array_values($statusPieMap));

            if ($cabang) {
                $statusPieQuery->where('cabang', $cabang);
            }

            $statusPieCounts = $statusPieQuery
                ->select('case_status')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('case_status')
                ->pluck('total', 'case_status');

            foreach ($statusPieMap as $label => $statusValue) {
                $statusPieData[$label] = (int) ($statusPieCounts[$statusValue] ?? 0);
            }
        }

        return view('master-dashboard', compact(
            'cabang',
            'jenisMonitoring',
            'availableMonths',
            'bulanTerpilih',
            'bulanLabel',
            'masukIw',
            'masukOw',
            'masukTotal',
            'closeIw',
            'closeOw',
            'closeTotal',
            'pendingIw',
            'pendingOw',
            'pendingTotal',
            'lastUpdateClose',
            'lastUpdatePending',
            'ledgerFrames',
            'agingRows',
            'agingGrandTotal',
            'partOrderRows',
            'partOrderGrandTotal',
            'partReqLogisticRows',
            'partReqLogisticGrandTotal',
            'wipRows',
            'wipGrandTotal',
            'towerRows',
            'towerGrandTotal',
            'closeTowerRows',
            'closeTowerGrandTotal',
            'closeRepairRows',
            'closeRepairGrandTotal',
            'loggedClosedRows',
            'loggedClosedGrandTotal',
            'recentDays',
            'statusPieData'
        ));
    }

    /**
     * ★★★ BARU: jalan mundur dari hari ini, LOMPATI hari Minggu (libur
     * kerja — tidak ada case masuk/close yang perlu dibandingkan), sampai
     * terkumpul $n hari kerja. Dipakai di semua jendela "N hari terakhir"
     * pada dashboard monitoring (kartu Case masuk/close, grafik
     * perbandingannya, dan breakdown per CE), supaya konsisten.
     *
     * @return array<int, string> tanggal (Y-m-d), urut LAMA -> BARU
     */
    private function lastWorkingDays(int $n): array
    {
        $dates  = [];
        $cursor = now()->copy();

        while (count($dates) < $n) {
            if (! $cursor->isSunday()) {
                $dates[] = $cursor->toDateString();
            }
            $cursor->subDay();
        }

        return array_reverse($dates);
    }

    /**
     * ★★★ BARU: Hitung 5 breakdown per-CE (Aging, Part Order, Part Request
     * Logistic, WIP, IPG&PSG) — bisa dipakai untuk data LIVE (pending_case)
     * maupun data HISTORIS (pending_case_history + filter snapshot_date),
     * supaya logic-nya tidak dobel ditulis di 2 tempat.
     *
     * @param  \Illuminate\Support\Collection $technicians   Daftar teknisi (sudah ada display_name)
     * @param  string   $tableName        'pending_case' atau 'pending_case_history'
     * @param  string|null $cabangFilter  Nilai filter dropdown Cabang (fallback kalau teknisi belum ditandai cabangnya)
     * @param  callable|null $extraWhere  Closure tambahan diterapkan ke SETIAP query (mis. where snapshot_date)
     * @param  string|null $agingReferenceDate  Tanggal acuan buat hitung aging (DATEDIFF). Null = NOW() (data live).
     *                                           Untuk data historis, ini WAJIB diisi tanggal snapshot-nya sendiri,
     *                                           supaya aging menggambarkan kondisi "seandainya dilihat di tanggal itu".
     */
    private function computePendingBreakdown($technicians, string $tableName, ?string $cabangFilter, ?callable $extraWhere = null, ?string $agingReferenceDate = null): array
    {
        $agingRows = [];
        $agingGrandTotal = ['d0_7' => 0, 'd8_14' => 0, 'over14' => 0];

        $partOrderRows = [];
        $partOrderGrandTotal = 0;

        $partReqLogisticRows = [];
        $partReqLogisticGrandTotal = 0;

        $wipRows = [];
        $wipGrandTotal = ['with_part' => 0, 'without_part' => 0];

        $towerRows = [];
        $towerGrandTotal = ['ipg' => 0, 'psg' => 0];

        $refDate = $agingReferenceDate ?: now()->toDateTimeString();

        foreach ($technicians as $tech) {

            $scoped = function () use ($tableName, $tech, $cabangFilter, $extraWhere) {
                $q = DB::table($tableName)->where('ce_name', $tech->name);

                if ($tech->cabang) {
                    $q->where('cabang', $tech->cabang);
                } elseif ($cabangFilter) {
                    $q->where('cabang', $cabangFilter);
                }

                if ($extraWhere) {
                    $extraWhere($q);
                }

                return $q;
            };

            // ----- Aging -----
            $agingRow = $scoped()
                ->whereNotNull('received_date')
                ->selectRaw("
                    SUM(CASE WHEN DATEDIFF(?, received_date) <= 7 THEN 1 ELSE 0 END) as d0_7,
                    SUM(CASE WHEN DATEDIFF(?, received_date) BETWEEN 8 AND 14 THEN 1 ELSE 0 END) as d8_14,
                    SUM(CASE WHEN DATEDIFF(?, received_date) > 14 THEN 1 ELSE 0 END) as over14
                ", [$refDate, $refDate, $refDate])
                ->first();

            $d0_7   = (int) ($agingRow->d0_7 ?? 0);
            $d8_14  = (int) ($agingRow->d8_14 ?? 0);
            $over14 = (int) ($agingRow->over14 ?? 0);

            $agingRows[] = [
                'name'   => $tech->display_name,
                'd0_7'   => $d0_7,
                'd8_14'  => $d8_14,
                'over14' => $over14,
                'total'  => $d0_7 + $d8_14 + $over14,
            ];

            $agingGrandTotal['d0_7']   += $d0_7;
            $agingGrandTotal['d8_14']  += $d8_14;
            $agingGrandTotal['over14'] += $over14;

            // ----- Part Order -----
            $countPO = $scoped()->where('case_status', 'Part Order Logistic')->count();

            $partOrderRows[] = ['name' => $tech->display_name, 'total' => $countPO];
            $partOrderGrandTotal += $countPO;

            // ----- Part Request Logistic -----
            $countPRL = $scoped()->where('case_status', 'Part Request Logistic')->count();

            $partReqLogisticRows[] = ['name' => $tech->display_name, 'total' => $countPRL];
            $partReqLogisticGrandTotal += $countPRL;

            // ----- WIP (dengan Part vs tanpa Part, validasi ke case_parts) -----
            $wipBaseQuery = $scoped()->where('case_status', 'Repair Progress');

            $hasValidatedPart = function ($q) use ($tableName) {
                $q->whereNotNull("{$tableName}.case_id_manual")
                    ->where("{$tableName}.case_id_manual", '!=', '')
                    ->whereExists(function ($sub) use ($tableName) {
                        $sub->select(DB::raw(1))
                            ->from('case_parts')
                            ->whereColumn('case_parts.case_id', "{$tableName}.case_id")
                            ->whereColumn('case_parts.cabang', "{$tableName}.cabang");
                    });
            };

            $withPart = (clone $wipBaseQuery)->where($hasValidatedPart)->count();

            $withoutPart = (clone $wipBaseQuery)->where(function ($q) use ($tableName) {
                $q->whereNull("{$tableName}.case_id_manual")
                    ->orWhere("{$tableName}.case_id_manual", '')
                    ->orWhereNotExists(function ($sub) use ($tableName) {
                        $sub->select(DB::raw(1))
                            ->from('case_parts')
                            ->whereColumn('case_parts.case_id', "{$tableName}.case_id")
                            ->whereColumn('case_parts.cabang', "{$tableName}.cabang");
                    });
            })->count();

            $wipRows[] = [
                'name'         => $tech->display_name,
                'with_part'    => $withPart,
                'without_part' => $withoutPart,
                'total'        => $withPart + $withoutPart,
            ];

            $wipGrandTotal['with_part']    += $withPart;
            $wipGrandTotal['without_part'] += $withoutPart;

            // ----- IPG & PSG -----
            $towerRow = $scoped()
                ->selectRaw("
                    SUM(CASE WHEN product_tower = 'IPG' THEN 1 ELSE 0 END) as ipg,
                    SUM(CASE WHEN product_tower = 'PSG' THEN 1 ELSE 0 END) as psg
                ")
                ->first();

            $ipg = (int) ($towerRow->ipg ?? 0);
            $psg = (int) ($towerRow->psg ?? 0);

            $towerRows[] = [
                'name'  => $tech->display_name,
                'ipg'   => $ipg,
                'psg'   => $psg,
                'total' => $ipg + $psg,
            ];

            $towerGrandTotal['ipg'] += $ipg;
            $towerGrandTotal['psg'] += $psg;
        }

        $agingGrandTotal['total'] = $agingGrandTotal['d0_7'] + $agingGrandTotal['d8_14'] + $agingGrandTotal['over14'];
        $wipGrandTotal['total']   = $wipGrandTotal['with_part'] + $wipGrandTotal['without_part'];
        $towerGrandTotal['total'] = $towerGrandTotal['ipg'] + $towerGrandTotal['psg'];

        return compact(
            'agingRows',
            'agingGrandTotal',
            'partOrderRows',
            'partOrderGrandTotal',
            'partReqLogisticRows',
            'partReqLogisticGrandTotal',
            'wipRows',
            'wipGrandTotal',
            'towerRows',
            'towerGrandTotal'
        );
    }
}