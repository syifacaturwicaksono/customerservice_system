<?php

namespace App\Http\Controllers;

use App\Imports\SplitCaseImport;
use App\Models\Technician;
use App\Models\WaReminderLog;
use App\Models\WaReminderSetting;
use App\Services\TatReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| TechnicianReportController
|--------------------------------------------------------------------------
| Halaman "Upload" — menampilkan rekap case per teknisi, berdasarkan jenis
| monitoring yang dipilih (Close Case / Pending Case), DAN menangani upload
| file Excel baru untuk kedua tabel tsb.
|
| Tabel 1: total case per teknisi + tombol Preview (lihat detail case).
| Tabel 2: breakdown per teknisi — jumlah case Warranty, Out Warranty, Bench, Onsite.
|
| Daftar teknisi diambil LANGSUNG dari tabel `technicians` (Master Data yang
| sudah ada), bukan di-hardcode — supaya kalau ada teknisi baru ditambah/
| dihapus lewat menu Teknisi, laporan ini otomatis ikut menyesuaikan.
|
| ★★★ BARU: fitur upload Excel.
| - Close Case  : mode APPEND — data lama TIDAK PERNAH dihapus, cuma nambah.
| - Pending Case: mode REPLACE — tabel di-TRUNCATE dulu sebelum data baru
|   masuk, karena pending case cuma snapshot kondisi TERKINI, bukan riwayat.
|--------------------------------------------------------------------------
*/

class TechnicianReportController extends Controller
{
    /**
     * Halaman "Upload" — HANYA form upload Excel + history upload batch.
     * Rekap case per teknisi & pivot status sudah dipindah ke dashboard().
     */
    public function index(Request $request)
    {
        // ✅ PAGINATION: 15 baris per halaman, supaya History Upload Batch
        // tidak panjang ke bawah.
        $uploadHistory = DB::table('upload_batches')
            ->orderByDesc('id')
            ->paginate(15);

        return view('upload', compact('uploadHistory'));
    }

    /**
     * Halaman "Dashboard" — rekap case per teknisi (Tabel 1) & pivot
     * Case Status x Teknisi (Tabel 2), sesuai filter Jenis Monitoring.
     * Ini konten yang SEBELUMNYA ada di index() / halaman Upload.
     */
    public function dashboard(Request $request)
    {
        // Default jenis monitoring: Pending Case
        $jenisMonitoring = $request->input('jenis_monitoring', 'pending_case');

        // Whitelist supaya tidak bisa dipakai untuk query ke tabel sembarangan
        $tableName = $jenisMonitoring === 'pending_case' ? 'pending_case' : 'close_case';

        // ★★★ BARU: Filter Cabang (Semarang / Cirebon). Kosong/null = Semua
        // Cabang (gabungan Semarang + Cirebon, perilaku lama sebelum filter
        // ini ada). Kolom `cabang` sudah ada di close_case & pending_case
        // sejak fitur upload per-cabang dibuat, jadi cuma perlu diterapkan
        // sebagai kondisi where di query-query di bawah — tidak ada menu
        // baru maupun migration baru.
        $cabang = $request->input('cabang');

        // ★★★ BARU: "Last update" — waktu baris TERBARU masuk ke tabel yang
        // sedang aktif (close_case/pending_case), diambil dari created_at
        // (waktu insert asli, bukan waktu upload_batches dicatat). Untuk
        // Pending Case ini otomatis = waktu upload terakhir (karena seluruh
        // tabel diganti tiap upload). Untuk Close Case ini = waktu baris
        // TERBARU ditambahkan (bisa dari upload bulan ini). ★★★
        $lastUpdateQuery = DB::table($tableName);

        if ($cabang) {
            $lastUpdateQuery->where('cabang', $cabang);
        }

        $lastUpdate = $lastUpdateQuery->max('created_at');

        // ★★★ BARU: kartu "Case masuk" & "Case close" — SAMA PERSIS logikanya
        // dengan Dashboard Master (lihat MasterDashboardController::index()),
        // cuma dipindah ke sini supaya Dashboard CE/Semarang/Cirebon (yang
        // semuanya lewat method ini) ikut punya kartu yang sama. SENGAJA
        // TIDAK ikut filter jenis_monitoring maupun bulan — "case masuk" &
        // "case close" selalu gabungan pending_case + close_case, cuma
        // dibatasi cabang (kalau ada) dan jendela 4 hari (hari ini + 3 hari
        // sebelumnya). Definisi tanggal:
        // - "Case masuk" per hari = received_date jatuh di hari itu.
        // - "Case close" per hari = closed_date jatuh di hari itu (tanggal
        //   case BENAR-BENAR ditutup, status Close Repair / Close Cancel —
        //   ★★★ DIUBAH dari `finish_date`, lihat komentar lebih lengkap di
        //   MasterDashboardController::index(), method yang sama persis. ★★★
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

        // ★★★ BARU: pakai lastWorkingDays() — jalan mundur dari hari ini,
        // LOMPATI Minggu (libur kerja), sampai dapat 4 hari kerja. Sama
        // persis dengan MasterDashboardController::index().
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

        // Backlog pending SAAT INI (bukan per hari) — dipakai di baris "Belum
        // close" pada kartu Case close, sama seperti Dashboard Master.
        $backlogQuery = DB::table('pending_case');

        if ($cabang) {
            $backlogQuery->where('cabang', $cabang);
        }

        $backlogTotals = (clone $backlogQuery)->selectRaw("
            SUM(CASE WHEN warranty_status = 'In Warranty' THEN 1 ELSE 0 END) as iw,
            SUM(CASE WHEN warranty_status = 'Out Warranty' THEN 1 ELSE 0 END) as ow,
            COUNT(*) as total
        ")->first();

        $pendingIw    = (int) ($backlogTotals->iw ?? 0);
        $pendingOw    = (int) ($backlogTotals->ow ?? 0);
        $pendingTotal = (int) ($backlogTotals->total ?? 0);

        // ★★★ DIUBAH: sekarang teknisi TERTENTU milik cabang tertentu
        // (kolom `cabang` di tabel technicians) — jadi kalau ada filter
        // Cabang aktif, cuma teknisi cabang itu yang dimunculkan. Ada juga
        // nama yang SAMA di 2 cabang berbeda (mis. "Bayu" di Semarang &
        // Cirebon) — supaya tidak ketuker, tiap teknisi dikasih
        // `display_name` yang otomatis ditambah "(Cabang)" KALAU memang
        // ada nama kembar, biar jelas dibedakan di tabel/pivot.
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

        // ★★★ FIX: Close Case bersifat AKUMULATIF (upload lama tidak pernah
        // dihapus, cuma ditambah tiap kali ada upload baru), jadi kalau
        // ditampilkan TANPA filter bulan, "Total Case per Teknisi" akan terus
        // menumpuk dari bulan ke bulan (bisa 10 bulan+ jadi satu angka) —
        // bukan cuma bulan berjalan seperti laporan bulanan yang seharusnya
        // (rata-rata cuma 200-300 case/bulan). Makanya Close Case SEKARANG
        // WAJIB difilter ke SATU bulan tertentu (default: bulan terbaru yang
        // ada datanya, tapi bisa dipilih manual lewat dropdown "Bulan").
        //
        // Pending Case TIDAK perlu difilter bulan — tabelnya di-REPLACE total
        // tiap upload (bukan ditambah), jadi otomatis SELALU representasi
        // snapshot case yang PALING BARU (per hari terakhir upload), bukan
        // akumulasi banyak bulan. ★★★
        $availableMonths = [];
        $bulanTerpilih   = null;

        if ($jenisMonitoring !== 'pending_case') {

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

        $summary         = []; // Tabel 1: total case per teknisi + data case (buat modal Preview)
        $warrantySummary = []; // Tabel 2: breakdown Warranty / Out Warranty / Bench / Onsite

        foreach ($technicians as $tech) {

            $query = DB::table($tableName)
                ->where('ce_name', $tech->name);

            // ★★★ DIUBAH: pakai cabang MILIK teknisi ini sendiri (bukan cuma
            // filter dropdown) — supaya kalau ada nama kembar di 2 cabang
            // (mis. "Bayu" Semarang vs Cirebon), datanya tidak ketuker jadi
            // gabungan keduanya. Fallback ke filter dropdown $cabang cuma
            // untuk teknisi lama yang belum sempat ditandai cabangnya.
            if ($tech->cabang) {
                $query->where('cabang', $tech->cabang);
            } elseif ($cabang) {
                $query->where('cabang', $cabang);
            }

            if ($bulanTerpilih) {
                $query->whereRaw("DATE_FORMAT(received_date, '%Y-%m') = ?", [$bulanTerpilih]);
            }

            // ★★★ FIX: "Finish Repair" & "Cancel Repair" sudah sengaja DIKELUARKAN
            // dari pivot "Pending Open Case per CE" (lihat $pendingStatusOrder di
            // atas), tapi query Tabel 1 ini TIDAK ikut mengecualikannya — akibatnya
            // "Total Case" & "Rata-rata TAT" di Tabel 1, serta modal Preview, masih
            // menghitung/menampilkan case dengan status itu, jadi ANGKANYA BEDA dari
            // TOTAL di pivot. Disamakan sekarang: untuk Pending Case, case dengan
            // status berawalan "Finish Repair" atau "Cancel Repair" (termasuk kalau
            // ada akhiran tambahan) DIKECUALIKAN di sini juga, supaya Total Case,
            // Rata-rata TAT, dan isi modal Preview semuanya KONSISTEN dengan pivot. ★★★
            if ($jenisMonitoring === 'pending_case') {
                $query->where('case_status', 'not like', 'Finish Repair%')
                      ->where('case_status', 'not like', 'Cancel Repair%');
            }

            $cases = $query->orderByDesc('received_date')->get();

            // ★★★ BARU: rata-rata TAT per teknisi — pakai KOLOM TAT YANG SAMA
            // seperti di modal Preview (tat_open_case untuk Pending Case yang
            // masih terbuka, tat_case untuk Close Case yang sudah selesai),
            // dijumlah semua lalu dibagi jumlah case. Case dengan nilai TAT
            // NULL tidak ikut dihitung (baik di jumlah maupun pembaginya),
            // supaya data kosong tidak "menurunkan" rata-rata secara keliru.
            $tatField = $jenisMonitoring === 'pending_case' ? 'tat_open_case' : 'tat_case';
            $tatValues = $cases->pluck($tatField)->filter(fn($v) => $v !== null);
            $avgTat = $tatValues->count() > 0 ? round($tatValues->avg(), 1) : null;

            $summary[] = [
                'name'       => $tech->display_name,
                'total_case' => $cases->count(),
                'avg_tat'    => $avgTat,
                'cases'      => $cases,
            ];

            // ★★★ CATATAN: warranty_status (Warranty/Out Warranty), case_type
            // (Bench/Onsite), dan product_group (IPG/PSG) itu 3 DIMENSI
            // BERBEDA — satu case bisa sekaligus In Warranty, Onsite, DAN IPG.
            // Warranty+Out Warranty / IPG+PSG TIDAK selalu = Total Case (ada
            // sedikit case dengan nilai lain/kosong, tidak ditampilkan sebagai
            // kolom terpisah). Tapi Bench+Onsite HAMPIR SELALU = Total Case
            // (case_type cuma 2 kemungkinan utama, sudah diverifikasi lewat
            // data asli). ★★★
            $warrantySummary[] = [
                'name'         => $tech->display_name,
                // ★★★ FIX: dibuat case-insensitive + trim (sama seperti fix Purwanto
                // sebelumnya) — data Excel kadang menyimpan nilai dengan huruf
                // besar/kecil tidak konsisten (mis. 'ipg' vs 'IPG') atau ada spasi
                // nyasar, sehingga perbandingan persis (case-sensitive) sebelumnya
                // gagal mencocokkan & hasilnya selalu 0. ★★★
                'warranty'     => $cases->filter(fn($c) => strcasecmp(trim((string) $c->warranty_status), 'In Warranty') === 0)->count(),
                'out_warranty' => $cases->filter(fn($c) => strcasecmp(trim((string) $c->warranty_status), 'Out Warranty') === 0)->count(),
                'bench'        => $cases->filter(fn($c) => strcasecmp(trim((string) $c->case_type), 'Bench') === 0)->count(),
                'onsite'       => $cases->filter(fn($c) => strcasecmp(trim((string) $c->case_type), 'Onsite') === 0)->count(),
                // ★★★ FIX: field yang benar untuk IPG/PSG adalah `product_tower`,
                // BUKAN `product_group` (yang ternyata isinya "Consumer"/
                // "Commercial" — dua dimensi berbeda). Ketahuan setelah cek
                // langsung ke database lewat phpMyAdmin. Tetap dibuat
                // case-insensitive + trim untuk jaga-jaga variasi data. ★★★
                'ipg'          => $cases->filter(fn($c) => strcasecmp(trim((string) $c->product_tower), 'IPG') === 0)->count(),
                'psg'          => $cases->filter(fn($c) => strcasecmp(trim((string) $c->product_tower), 'PSG') === 0)->count(),
            ];
        }

        // ★★★ DIUBAH CLAUDE: pivot Case Status x Teknisi SEKARANG IKUT FILTER
        // "Jenis Monitoring" (sama seperti Tabel 1) — bukan cuma Pending Case
        // terus. Masing-masing jenis punya daftar status sendiri (beda sama
        // sekali di database), jadi disiapkan 2 peta terpisah lalu dipilih
        // sesuai $jenisMonitoring.
        //
        // Key = nilai case_status ASLI yang tersimpan di database (dipakai untuk
        // pencocokan data), Value = label yang ditampilkan di tabel.
        //
        // - Pending Case: DIBATASI jadi 12 status ini saja (10 status alur kerja
        //   + "Quotation Request" + "Cancel Repair" sebagai status akhir/
        //   dibatalkan) — status lain (misal "Void") TIDAK ditampilkan lagi
        //   meskipun ada datanya. 12 baris ini SELALU tampil (walau kosong/0),
        //   supaya konsisten untuk data sekarang maupun yang akan datang.
        // - Close Case: cuma ada 2 status di data asli (Close Repair / Close
        //   Cancel), sudah diverifikasi dari database. ★★★
        $pendingStatusOrder = [
            'New(Assign to CE)'           => 'New (Assign to CE)',
            'Repair Progress'             => 'Repair Progress',
            'Escalation'                  => 'Escalation',
            'Part Request'                => 'Part Request',
            'Part Request Logistic'       => 'Part Request Logistic',
            'Part Order Logistic'         => 'Part Order Logistic',
            'Part Available (Part Ready)' => 'Part Available',
            'Quotation Request'           => 'Quotation Request',
            'Quotation Pending'           => 'Quotation Pending',
            'Quotation Approved'          => 'Quotation Approved',
            'Quotation Cancel'            => 'Quotation Cancel',
            // ★★★ DIHAPUS ATAS PERMINTAAN: baris "Finish Repair" DAN "Cancel Repair"
            // sengaja TIDAK ditampilkan lagi di pivot ini & TIDAK ikut dihitung di
            // baris TOTAL — akibatnya TOTAL di sini bisa BEDA dari "Total Case per
            // Teknisi" (yang menghitung SEMUA case apapun statusnya). Ini memang
            // disengaja, bukan bug. Berlaku juga untuk data hasil upload Excel baru:
            // case dengan status ini tetap TERSIMPAN di database seperti biasa,
            // cuma tidak ikut ditampilkan/dihitung di pivot & TOTAL halaman ini. ★★★
        ];

        $closeStatusOrder = [
            'Close Repair' => 'Close Repair',
            'Close Cancel' => 'Close Cancel',
        ];

        $statusOrder  = $jenisMonitoring === 'pending_case' ? $pendingStatusOrder : $closeStatusOrder;
        $pivotTitle   = $jenisMonitoring === 'pending_case' ? 'Pending Open Case per CE' : 'Close Case per CE';

        // ★★★ FIX: SEBELUMNYA pakai whereIn('case_status', array_keys($statusOrder))
        // yang butuh MATCH PERSIS di level database — kalau data asli beda 1 spasi
        // saja dari key yang di-hardcode (mis. "New (Assign to CE)" di database vs
        // "New(Assign to CE)" di key), baris itu HILANG TOTAL dari pivot tanpa
        // ketahuan, bikin TOTAL di tabel ini beda dari Tabel 1. Sekarang ambil
        // SEMUA baris dulu (tanpa filter status ketat di SQL), baru dicocokkan di
        // PHP pakai normalisasi (buang semua spasi + lowercase) — jadi variasi
        // spasi/kapitalisasi tidak lagi bikin data hilang diam-diam. ★★★
        $pivotQuery = DB::table($tableName)->whereNotNull('case_status');

        if ($cabang) {
            $pivotQuery->where('cabang', $cabang);
        }

        if ($bulanTerpilih) {
            $pivotQuery->whereRaw("DATE_FORMAT(received_date, '%Y-%m') = ?", [$bulanTerpilih]);
        }

        $pivotCases = $pivotQuery->get(['case_status', 'ce_name', 'cabang', 'received_date']);

        // Normalisasi: buang SEMUA spasi + lowercase, supaya "New(Assign to CE)"
        // dan "New (Assign to CE)" (atau variasi spasi/huruf lain) dianggap SAMA.
        $normalizeStatus = fn ($s) => strtolower(preg_replace('/\s+/', '', trim((string) $s)));

        // Label bulan: untuk Close Case, ambil dari $bulanTerpilih (bulan yang
        // sedang difilter). Untuk Pending Case, tetap dari received_date
        // TERBARU di data saat ini (snapshot terkini), seperti sebelumnya.
        if ($bulanTerpilih) {
            $bulanLabel = \Carbon\Carbon::createFromFormat('Y-m', $bulanTerpilih)->translatedFormat('F Y');
        } else {
            $latestReceivedDate = $pivotCases->max('received_date');
            $bulanLabel = $latestReceivedDate
                ? \Carbon\Carbon::parse($latestReceivedDate)->translatedFormat('F')
                : '-';
        }

        // Nama teknisi yang jadi kolom (sama seperti Tabel 1, exclude Agung).
        // ★★★ DIUBAH: pakai display_name (bukan name mentah), supaya kalau ada
        // nama kembar lintas cabang (mis. "Bayu" Semarang & Cirebon), kolomnya
        // otomatis dibedakan jadi "Bayu (Semarang)" / "Bayu (Cirebon)" — bukan
        // numpuk jadi satu kolom "Bayu" yang datanya campur aduk.
        $technicianNames = $technicians->pluck('display_name')->all();

        $pendingPivot = [];
        $pivotTotals  = array_fill_keys($technicianNames, 0);

        foreach ($statusOrder as $dbValue => $label) {

            // ★★★ FIX: SEBELUMNYA pakai groupBy + exact match hasil normalisasi,
            // yang butuh case_status di database SAMA PERSIS dengan key di
            // $statusOrder. Ternyata data Close Case punya AKHIRAN TAMBAHAN,
            // mis. "Close Repair (Cust pick up)" bukan cuma "Close Repair" —
            // jadi exact match GAGAL TOTAL dan semua angka jadi 0 (data
            // "hilang" padahal sebenarnya cuma tidak ketemu). Sekarang dicocokkan:
            // SAMA PERSIS, ATAU diawali key + langsung diikuti "(" (pola akhiran
            // asli, mis. "(Cust pick up)"). SENGAJA TIDAK pakai starts-with polos,
            // karena itu akan membuat "Part Request" ikut mencocokkan "Part
            // Request Logistic" juga (dianggap sama padahal beda status) —
            // butuh "(" persis di belakang supaya cuma akhiran ber-kurung yang
            // dianggap sama, bukan status lain yang kebetulan berawalan sama. ★★★
            $normalizedDbValue = $normalizeStatus($dbValue);
            $dbValueLen = strlen($normalizedDbValue);

            $rowsForStatus = $pivotCases->filter(function ($row) use ($normalizeStatus, $normalizedDbValue, $dbValueLen) {
                $normalizedRow = $normalizeStatus($row->case_status);
                return $normalizedRow === $normalizedDbValue
                    || (str_starts_with($normalizedRow, $normalizedDbValue) && ($normalizedRow[$dbValueLen] ?? '') === '(');
            });

            $rowCounts = [];

            // ★★★ DIUBAH: loop pakai OBJEK $technicians (bukan cuma
            // $technicianNames string) — supaya bisa cek cabang MILIK
            // teknisi itu sendiri saat mencocokkan, bukan cuma nama.
            // Ini krusial sekarang ada nama kembar lintas cabang (mis.
            // "Bayu" Semarang vs Cirebon) — tanpa ini, kedua "Bayu" akan
            // kehitung gabungan (data cabang lain ikut kecampur).
            foreach ($technicians as $tech) {
                // ★★★ FIX: dibuat case-insensitive (strcasecmp) — data Excel kadang
                // menyimpan nama teknisi dengan huruf besar/kecil tidak konsisten
                // (mis. 'purwanto' vs 'Purwanto' di Master Data), sehingga sebelumnya
                // baris dengan huruf kecil tidak ikut terhitung di tabel pivot ini. ★★★
                $count = $rowsForStatus->filter(function ($row) use ($tech) {
                    $nameMatch = strcasecmp(trim($row->ce_name ?? ''), trim($tech->name)) === 0;

                    // Kalau teknisi ini sudah ditandai cabangnya, WAJIB cabang
                    // baris data-nya cocok juga. Teknisi lama yang belum
                    // ditandai cabang (cabang = null) tetap dicocokkan cuma
                    // dari nama saja, seperti perilaku sebelumnya.
                    $cabangMatch = !$tech->cabang
                        || strcasecmp(trim($row->cabang ?? ''), trim($tech->cabang)) === 0;

                    return $nameMatch && $cabangMatch;
                })->count();

                $name = $tech->display_name;
                $rowCounts[$name] = $count;
                $pivotTotals[$name] += $count;
            }

            $pendingPivot[] = [
                'status' => $label,
                'counts' => $rowCounts,
            ];
        }

        return view('technician-dashboard', compact(
            'summary',
            'warrantySummary',
            'jenisMonitoring',
            'technicianNames',
            'pendingPivot',
            'pivotTotals',
            'bulanLabel',
            'pivotTitle',
            'availableMonths',
            'bulanTerpilih',
            'lastUpdate',
            'cabang',
            'ledgerFrames',
            'pendingIw',
            'pendingOw',
            'pendingTotal'
        ));
    }

    /**
     * Halaman "BC Email" — DIALIHFUNGSIKAN jadi arsip & pencarian Close
     * Case. Karena close_case bersifat APPEND (tidak pernah dihapus), tabel
     * ini SUDAH JADI penyimpanan permanen — halaman ini cuma cara untuk
     * MELIHAT & MENCARI isinya dari web, tanpa perlu buka phpMyAdmin.
     *
     * Pencarian ($search) mencakup HAMPIR SEMUA kolom teks yang relevan di
     * close_case (Case ID, nama perusahaan, customer, produk, dsb) — sesuai
     * permintaan "bisa dicari berdasarkan semua yang ada di close case".
     * Kolom sangat teknis (delay_code, void_cancel_reason, dsb) sengaja
     * TIDAK diikutkan supaya pencarian tetap relevan & tidak melambat.
     */
    public function archive(Request $request)
    {
        $search  = trim((string) $request->input('search', ''));
        $dari    = $request->input('dari');    // format: Y-m-d
        $sampai  = $request->input('sampai');  // format: Y-m-d
        $teknisi = $request->input('teknisi');
        $status  = $request->input('status');

        $query = DB::table('close_case');

        if ($search !== '') {
            $like = '%' . $search . '%';

        $query->where(function ($q) use ($like) {
                $q->where('case_id', 'like', $like)
                ->orWhere('case_id_manual', 'like', $like)
                ->orWhere('reference_case', 'like', $like)
                ->orWhere('company_name', 'like', $like)
                ->orWhere('customer_company', 'like', $like)
                ->orWhere('customer_name', 'like', $like)
                ->orWhere('pic_name', 'like', $like)
                ->orWhere('ce_name', 'like', $like)
                ->orWhere('product_name', 'like', $like)
                ->orWhere('product_no', 'like', $like)
                ->orWhere('product_type', 'like', $like)
                ->orWhere('product_group', 'like', $like)
                ->orWhere('serial_no', 'like', $like)
                ->orWhere('phone_no', 'like', $like)
                ->orWhere('mobile_no', 'like', $like)
                ->orWhere('city', 'like', $like)
                ->orWhere('problem_desc', 'like', $like)
                ->orWhere('repair_action', 'like', $like)
                ->orWhere('quotation_no', 'like', $like)
                ->orWhere('case_status', 'like', $like)
                ->orWhere('warranty_status', 'like', $like);
            });
        }

        if ($dari) {
            $query->whereDate('received_date', '>=', $dari);
        }

        if ($sampai) {
            $query->whereDate('received_date', '<=', $sampai);
        }

        if ($teknisi) {
            $query->where('ce_name', $teknisi);
        }

        if ($status) {
            $query->where('case_status', $status);
        }

        $cases = $query->orderByDesc('received_date')
            ->paginate(25)
            ->withQueryString();

        $technicianNames = Technician::orderBy('name')->pluck('name');

        // Daftar status yang ADA di data close_case (bukan hardcode), supaya
        // dropdown filter selalu sinkron dengan data asli.
        $statusOptions = DB::table('close_case')
            ->whereNotNull('case_status')
            ->distinct()
            ->orderBy('case_status')
            ->pluck('case_status');

        // ★★★ BARU: data buat panel "Reminder TAT Otomatis" di halaman ini
        // (BC Email sekarang bukan cuma arsip, tapi juga pusat pengingat).
        $reminderSetting = WaReminderSetting::first();

        $reminderLogsToday = WaReminderLog::whereDate('sent_date', now()->toDateString())
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('ce_name');

        return view('case-archive', compact(
            'cases',
            'technicianNames',
            'statusOptions',
            'search',
            'dari',
            'sampai',
            'teknisi',
            'status',
            'reminderSetting',
            'reminderLogsToday'
        ));
    }

    /**
     * Nyala/matikan fitur Reminder TAT Otomatis (disimpan di
     * wa_reminder_settings, cuma 1 baris).
     */
    public function toggleReminder(Request $request)
    {
        $setting = WaReminderSetting::first();

        if (!$setting) {
            $setting = WaReminderSetting::create([
                'is_active'      => true,
                'threshold_days' => 3,
            ]);
        }

        $setting->is_active = !$setting->is_active;
        $setting->save();

        return back()->with('success', 'Reminder TAT ' . ($setting->is_active ? 'DIAKTIFKAN ✅' : 'DINONAKTIFKAN'));
    }

    /**
     * Trigger manual: jalankan pengecekan & pengiriman reminder TAT SEKARANG
     * juga (tidak perlu nunggu jadwal cron) — dipakai buat uji coba fitur.
     */
    public function sendReminderNow(TatReminderService $service)
    {
        $result = $service->run();

        if ($result['status'] === 'nonaktif') {
            return back()->with('error', $result['detail']);
        }

        // ★★★ FIX: SEBELUMNYA selalu tampil sbg pesan "success" (hijau) walau
        // sebenarnya SEMUA case gagal terkirim (mis. WA gateway tidak bisa
        // dihubungi) — bikin salah kira semuanya sukses padahal tidak ada
        // satupun yang benar-benar terkirim. Sekarang: kalau sent = 0 TAPI
        // ada case yang tertahan (skipped > 0), tampilkan sbg warning. ★★★
        if ($result['sent'] === 0 && $result['skipped'] > 0) {
            return back()->with('error', "Tidak ada WA yang berhasil terkirim ({$result['skipped']} case dilewati/gagal). Kemungkinan WA gateway tidak bisa dihubungi — cek log Laravel untuk detail.");
        }

        return back()->with('success', "Reminder selesai diproses — {$result['sent']} case direminder, {$result['skipped']} dilewati.");
    }

    /**
     * Proses upload file Excel baru. File Excel BOLEH berisi campuran Close
     * Case & Pending Case sekaligus (satu baris = satu case, dibedakan lewat
     * kolom "Case status") — tapi user tetap PILIH salah satu Jenis
     * Monitoring yang mau diambil. SplitCaseImport akan MENYARING file itu,
     * cuma ambil baris yang cocok dengan pilihan; baris jenis lain di file
     * yang sama otomatis diabaikan (tidak ikut masuk kemanapun).
     * - close_case   -> APPEND (data lama tidak dihapus)
     * - pending_case -> REPLACE (data lama di-TRUNCATE dulu)
     */
    public function store(Request $request)
    {
        $request->validate([
            'jenis_monitoring' => 'required|in:close_case,pending_case',
            'kode_upload'      => 'required|string|max:100',
            'cabang'           => 'required|in:Semarang,Cirebon',
            'file'             => 'required|file|mimes:xlsx,xls|max:20480', // maks 20MB
        ]);

        $jenis      = $request->jenis_monitoring;
        $kodeUpload = $request->kode_upload;
        $cabang     = $request->cabang;
        $file       = $request->file('file');

        // BARU: dibungkus try/catch — kalau ada error saat proses (file
        // corrupt, format Excel tidak dikenali, koneksi DB putus, dsb), user
        // tetap dapat pesan yang rapi & bisa dianimasikan (bukan halaman
        // error mentah dari Laravel). Detail teknisnya tetap dicatat di log
        // Laravel (storage/logs/laravel.log) untuk keperluan debugging.
        try {
            if ($jenis === 'pending_case') {
                // ★★★ BARU: sebelum data pending_case cabang ini DIHAPUS/
                // diganti oleh upload baru, simpan dulu salinannya ke
                // pending_case_history — supaya Dashboard Master tetap
                // bisa "buka case yang masih berjalan di hari sebelumnya"
                // (Notulen Meeting Project Dashboard 9 Sept 2026, poin 2i),
                // walaupun pending_case sendiri memang didesain cuma
                // menyimpan data TERKINI (mode REPLACE).
                //
                // Kalau di HARI YANG SAMA sudah ada snapshot untuk cabang
                // ini (misal upload dilakukan 2x dalam sehari), snapshot
                // lama hari itu ditimpa dulu — supaya per (cabang, tanggal)
                // cuma ada SATU snapshot: kondisi terakhir sebelum upload
                // berikutnya, bukan numpuk beberapa kali per hari.
                $todaySnapshot = now()->toDateString();

                $oldPendingRows = DB::table('pending_case')
                    ->where('cabang', $cabang)
                    ->get();

                if ($oldPendingRows->isNotEmpty()) {
                    DB::table('pending_case_history')
                        ->where('cabang', $cabang)
                        ->where('snapshot_date', $todaySnapshot)
                        ->delete();

                    $historyRows = $oldPendingRows->map(function ($row) use ($todaySnapshot) {
                        $row = (array) $row;
                        unset($row['id']); // id lama dari pending_case tidak relevan, biar auto_increment sendiri di history
                        $row['snapshot_date'] = $todaySnapshot;
                        $row['created_at']    = now();
                        $row['updated_at']    = now();
                        return $row;
                    })->toArray();

                    foreach (array_chunk($historyRows, 500) as $chunk) {
                        DB::table('pending_case_history')->insert($chunk);
                    }
                }

                // ★★★ DIUBAH: sebelumnya TRUNCATE seluruh tabel (menghapus
                // data SEMUA cabang). Sekarang cuma hapus data cabang yang
                // SEDANG diupload ini — data cabang lain (misal Semarang saat
                // yang upload Cirebon) TETAP AMAN tidak ikut terhapus. ★★★
                DB::table('pending_case')->where('cabang', $cabang)->delete();
            }

            $import = new SplitCaseImport($kodeUpload, $jenis, $cabang);
            Excel::import($import, $file);

            // ★★★ BARU: sheet "Parts" dari file Excel yang SAMA (kalau ada)
            // ikut dibaca — dipakai Dashboard Master untuk validasi WIP
            // dengan Part vs Non-Part. SplitCaseImport di atas cuma baca
            // sheet "Transaction Report", jadi sheet "Parts" perlu dibaca
            // terpisah. Kalau file tidak punya sheet "Parts" (misal file
            // lama sebelum fitur ini ada), fungsi ini otomatis dilewati
            // tanpa mengganggu proses upload utama sama sekali.
            $this->importPartsSheet($file, $cabang, $kodeUpload);

            $totalRow = $jenis === 'pending_case' ? $import->pendingCaseInserted : $import->closeCaseInserted;

            DB::table('upload_batches')->insert([
                'jenis_upload' => $jenis,
                'cabang'       => $cabang,
                'kode_upload'  => $kodeUpload,
                'file_name'    => $file->getClientOriginalName(),
                'total_row'    => $totalRow,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $message = "File berhasil diupload & diproses ke "
                . ($jenis === 'pending_case' ? "Pending Case cabang {$cabang} (data lama cabang ini diganti)" : "Close Case cabang {$cabang} (ditambahkan ke data lama)")
                . ". Total {$totalRow} baris masuk ke database.";

            // ★★★ Kalau file-nya campuran, baris jenis LAIN dari yang dipilih
            // otomatis diabaikan — dikasih tahu di sini supaya tidak diam-diam
            // "hilang" tanpa penjelasan. ★★★
            if ($import->filteredOut > 0) {
                $jenisLain = $jenis === 'pending_case' ? 'Close Case' : 'Pending Case';
                $message .= " ℹ️ {$import->filteredOut} baris lain terdeteksi sebagai {$jenisLain} di file yang sama & sengaja diabaikan (tidak diupload sesuai pilihan Jenis Monitoring).";
            }

            if ($import->skippedRows > 0) {
                $message .= " ⚠️ {$import->skippedRows} baris dilewati karena terdeteksi bukan data valid (kemungkinan baris header yang terulang di tengah file).";
            }

            if ($import->duplicateSkipped > 0) {
                $message .= " ℹ️ {$import->duplicateSkipped} baris dilewati karena Case ID-nya sudah pernah ada di database (duplikat dari upload sebelumnya).";
            }

            if ($import->duplicateInFileSkipped > 0) {
                $message .= " ℹ️ {$import->duplicateInFileSkipped} baris dilewati karena Case ID-nya terulang di dalam file yang sama (kemungkinan data terduplikasi di file sumbernya).";
            }

            // ★★★ BARU: info baris yang diabaikan karena prefix Case ID-nya
            // tidak cocok dengan cabang yang dipilih (kemungkinan salah
            // pilih dropdown, atau memang bukan data cabang ini). ★★★
            if ($import->cabangMismatchSkipped > 0) {
                $message .= " 🏢 {$import->cabangMismatchSkipped} baris dilewati karena Case ID-nya tidak diawali sesuai cabang \"{$cabang}\" (cek lagi apakah pilihan Cabang sudah benar).";
            }

            return redirect('/upload')->with('success', $message);

        } catch (\Throwable $e) {

            \Log::error('Upload Excel gagal', [
                'kode_upload' => $kodeUpload,
                'jenis'       => $jenis,
                'file'        => $file->getClientOriginalName(),
                'error'       => $e->getMessage(),
            ]);

            return redirect('/upload')->with('error',
                "Upload gagal diproses. Kemungkinan file rusak, format kolom tidak sesuai, atau ada gangguan koneksi database. "
                . "Detail teknis: " . $e->getMessage()
            );
        }
    }

    /**
     * ★★★ BARU: Baca sheet "Parts" dari file Excel Transaction Report yang
     * SAMA (kalau sheet itu ada), lalu simpan daftar Case ID-nya ke tabel
     * `case_parts`. Ini SUMBER KEBENARAN untuk validasi "case ini beneran
     * pernah pakai sparepart" di Dashboard Master (WIP dengan Part vs
     * Non-Part) — bukan cuma menebak dari "Case ID manual" terisi/tidak di
     * sheet utama, tapi divalidasi silang ke sheet Parts ini.
     *
     * SplitCaseImport (dipanggil sebelum method ini) cuma baca sheet
     * "Transaction Report", jadi sheet "Parts" perlu dibaca terpisah pakai
     * PhpSpreadsheet langsung (bukan lewat Maatwebsite Excel yang sudah
     * dipakai SplitCaseImport, supaya tidak perlu ubah class import yang
     * sudah jalan).
     *
     * Kalau file tidak punya sheet "Parts" (misal file lama, atau memang
     * cuma sheet Transaction Report saja), method ini berhenti diam-diam
     * tanpa error — proses upload utama TETAP berhasil seperti biasa,
     * cuma validasi WIP/Non-Part-nya nanti fallback jadi "tidak ada part
     * yang tervalidasi" untuk upload ini.
     */
    private function importPartsSheet($file, string $cabang, string $kodeUpload): void
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        } catch (\Throwable $e) {
            \Log::warning('Gagal baca ulang file untuk sheet Parts (upload utama tetap lanjut)', [
                'kode_upload' => $kodeUpload,
                'error'       => $e->getMessage(),
            ]);
            return;
        }

        if (!$spreadsheet->sheetNameExists('Parts')) {
            return;
        }

        $sheet = $spreadsheet->getSheetByName('Parts');
        $highestRow = $sheet->getHighestRow();

        // Header ada di baris 4 (sama seperti sheet Transaction Report),
        // data sesungguhnya mulai baris 5. Kolom B = Case ID.
        $caseIds = [];

        for ($row = 5; $row <= $highestRow; $row++) {
            $caseId = trim((string) $sheet->getCell("B{$row}")->getValue());

            if ($caseId !== '') {
                $caseIds[$caseId] = true; // dedupe Case ID yang terulang dalam sheet yang sama
            }
        }

        if (empty($caseIds)) {
            return;
        }

        $now = now();
        $rows = [];

        foreach (array_keys($caseIds) as $caseId) {
            $rows[] = [
                'case_id'     => $caseId,
                'cabang'      => $cabang,
                'kode_upload' => $kodeUpload,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        // insertOrIgnore + unique key (case_id, cabang) di tabel case_parts
        // supaya Case ID yang sudah tercatat dari upload sebelumnya tidak
        // dobel — sifatnya AKUMULATIF (APPEND), bukan diganti tiap upload.
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('case_parts')->insertOrIgnore($chunk);
        }
    }

    /**
     * ★★★ BARU: sama persis dengan MasterDashboardController::lastWorkingDays()
     * — jalan mundur dari hari ini, LOMPATI hari Minggu (libur kerja),
     * sampai terkumpul $n hari kerja. Dipakai untuk jendela "N hari
     * terakhir" pada kartu Case masuk/close di Dashboard CE/Semarang/Cirebon.
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
}