<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Support\TatOverFiveTrend;

class AuthController extends Controller
{

    public function loginForm()
    {
        // ★★★ REVISI (v5): grafik login diganti jadi COMBO BAR + GARIS:
        // - Case Masuk & Case Close: bar DITUMPUK (stacked) jadi 2 segmen
        //   IW (In Warranty) & OW (Out Warranty), tiap segmen dikasih label
        //   "jumlah (persentase%)" dari total hari itu. Garis (line) di
        //   atasnya nyambungin TOTAL tiap hari (IW+OW), dengan angka total
        //   di atas titik, plus indikator naik/turun (▲/▼ persen)
        //   dibanding hari sebelumnya yang tampil di grafik.
        // - Part Ready: TIDAK ada breakdown IW/OW (tabel case_parts tidak
        //   punya kolom warranty_status), jadi cuma bar tunggal + garis +
        //   total & naik/turun, tanpa segmen.
        // - Sumbu-X sekarang pakai TANGGAL ASLI (mis. "23 Sep"), bukan lagi
        //   kata "Hari ini".
        // - Jendela tanggal disederhanakan jadi 3 hari kerja TERMASUK hari
        //   ini (H-2, H-1, hari ini), Minggu tetap dilompati.
        $trend = $this->buildTrendByDay();

        // ★★★ REVISI (v10): grafik Pending Case & Close Case (TAT > 5 hari)
        // pakai helper App\Support\TatOverFiveTrend — SATU sumber rumus yang
        // sama dengan Dashboard CE/Semarang/Cirebon/Master.
        // - 4 tanggal: hari ini + 3 hari sebelumnya, hari Minggu dilompati.
        // - Tiap tanggal: batang IW & OW, dipecah TAT > 5 hari vs <= 5 hari
        //   (hari kerja Senin-Sabtu) beserta persennya.
        // Detail rumus ada di komentar bagian atas TatOverFiveTrend.php.
        $trendTat = TatOverFiveTrend::build(null); // null = semua cabang

        return view('login', [
            'trendDateLabels' => $trend['dateLabels'],
            'trendMasukIw'    => $trend['masuk']['iw'],
            'trendMasukOw'    => $trend['masuk']['ow'],
            'trendMasukTotal' => $trend['masuk']['total'],
            'trendCloseIw'    => $trend['close']['iw'],
            'trendCloseOw'    => $trend['close']['ow'],
            'trendCloseTotal' => $trend['close']['total'],
            'trendPartReady'  => $trend['partReady']['total'],

            // ---- data grafik TAT > 5 hari (v10): satu paket, dibaca langsung oleh
            // CaseStatusChart.tatShare() di login.blade.php ----
            'trendTat' => $trendTat,
        ]);
    }

   public function login(Request $request)
{

    $credentials = $request->only('username','password');

    if(Auth::attempt($credentials))
    {
        $request->session()->regenerate();
        return redirect('/dashboard');
    }

    return back()->with('error','Username atau password salah');

}


    public function logout(Request $request)
    {

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');

    }

    /**
     * Hitung breakdown IW/OW/total di $table yang $dateColumn-nya jatuh
     * pada $date. Sama persis pola $countIwOwTotal di
     * MasterDashboardController.
     */
    private function countIwOwTotal(string $table, string $dateColumn, $date): array
    {
        $row = DB::table($table)
            ->whereDate($dateColumn, $date)
            ->selectRaw("
                SUM(CASE WHEN warranty_status = 'In Warranty' THEN 1 ELSE 0 END) as iw,
                SUM(CASE WHEN warranty_status = 'Out Warranty' THEN 1 ELSE 0 END) as ow,
                COUNT(*) as total
            ")
            ->first();

        return [
            'iw'    => (int) ($row->iw ?? 0),
            'ow'    => (int) ($row->ow ?? 0),
            'total' => (int) ($row->total ?? 0),
        ];
    }

    /**
     * Hitung total baris di $table yang $dateColumn-nya jatuh pada $date,
     * TANPA breakdown IW/OW — dipakai untuk Part Ready karena case_parts
     * tidak punya kolom warranty_status.
     */
    private function countByDate(string $table, string $dateColumn, $date): int
    {
        return (int) DB::table($table)->whereDate($dateColumn, $date)->count();
    }

    /**
     * Ambil N hari KERJA terakhir, TERMASUK hari ini, lompati hari Minggu
     * — pola sama dengan lastWorkingDays() di MasterDashboardController.
     *
     * @return array<int, \Carbon\Carbon> urut LAMA -> BARU
     */
    private function lastWorkingDays(int $n): array
    {
        $dates  = [];
        $cursor = now()->copy();

        while (count($dates) < $n) {
            if (! $cursor->isSunday()) {
                $dates[] = $cursor->copy();
            }
            $cursor->subDay();
        }

        return array_reverse($dates);
    }

    /**
     * Susun data 3 grafik (Case Masuk, Case Close, Part Ready) untuk 3
     * hari kerja terakhir termasuk hari ini. Case Masuk = gabungan
     * pending_case + close_case (received_date). Case Close = close_case
     * (closed_date) saja. Part Ready = case_parts (created_at), total saja.
     */
    private function buildTrendByDay(): array
    {
        $dates = $this->lastWorkingDays(3);

        $dateLabels  = [];
        $masukIw     = [];
        $masukOw     = [];
        $masukTotal  = [];
        $closeIw     = [];
        $closeOw     = [];
        $closeTotal  = [];
        $partReady   = [];

        foreach ($dates as $date) {
            $dateLabels[] = $date->translatedFormat('d M');

            $pendingDay       = $this->countIwOwTotal('pending_case', 'received_date', $date);
            $closeReceivedDay = $this->countIwOwTotal('close_case', 'received_date', $date);
            $closeFinishedDay = $this->countIwOwTotal('close_case', 'closed_date', $date);

            $masukIw[]    = $pendingDay['iw'] + $closeReceivedDay['iw'];
            $masukOw[]    = $pendingDay['ow'] + $closeReceivedDay['ow'];
            $masukTotal[] = $pendingDay['total'] + $closeReceivedDay['total'];

            $closeIw[]    = $closeFinishedDay['iw'];
            $closeOw[]    = $closeFinishedDay['ow'];
            $closeTotal[] = $closeFinishedDay['total'];

            $partReady[] = $this->countByDate('case_parts', 'created_at', $date);
        }

        return [
            'dateLabels' => $dateLabels,
            'masuk'      => ['iw' => $masukIw, 'ow' => $masukOw, 'total' => $masukTotal],
            'close'      => ['iw' => $closeIw, 'ow' => $closeOw, 'total' => $closeTotal],
            'partReady'  => ['total' => $partReady],
        ];
    }

}