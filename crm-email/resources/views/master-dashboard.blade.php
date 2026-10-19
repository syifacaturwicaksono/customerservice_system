@extends('layouts.app')

@section('title', 'Dashboard Master')

@section('content')

@php
    /* -----------------------------------------------------------------
       Nilai turunan dihitung sekali di atas supaya markup di bawah bersih.
       Tidak ada perubahan variabel dari controller — semua nama sama
       persis seperti yang dikirim MasterDashboardController::index().
       ----------------------------------------------------------------- */

    $isPending       = $jenisMonitoring === 'pending_case';
    $labelJenis      = $isPending ? 'Pending case' : 'Close case';
    $activeLastUpdate = $isPending ? $lastUpdatePending : $lastUpdateClose;

    // Komposisi umur case pending, dipakai untuk bar ringkasan di bawah.
    $ageTotal = max((int) ($agingGrandTotal['total'] ?? 0), 0);
    $pct = function ($n) use ($ageTotal) {
        return $ageTotal > 0 ? round(($n / $ageTotal) * 100) : 0;
    };

    /* -----------------------------------------------------------------
       FRAME PER HARI — dulu ada panel terpisah "Riwayat Pending per
       Tanggal" dengan dropdown tanggal manual. Sekarang setiap grafik
       pending langsung membawa data $recentDays (dari controller: 3 hari
       kalender terakhir yang punya snapshot + hari ini), jadi tinggal
       diklik tab-nya, tidak perlu pindah halaman atau pilih tanggal.
       Bentuknya disamakan semua: [{label, rows}, ...], urut lama -> baru,
       "Hari ini" selalu di kanan (dan jadi tampilan default).
       ----------------------------------------------------------------- */
    $toFrames = function (string $key) use ($recentDays) {
        return collect($recentDays ?? [])
            ->map(fn ($d) => ['label' => $d['label'], 'rows' => $d['breakdown'][$key]])
            ->values();
    };

    $agingFrames     = $toFrames('agingRows');
    $partOrderFrames = $toFrames('partOrderRows');
    $partReqFrames   = $toFrames('partReqLogisticRows');
    $wipFrames       = $toFrames('wipRows');

    // ★★★ BARU: kartu "Case masuk" & "Case close" — indeks frame TERAKHIR
    // = "Hari ini", dipakai sebagai tampilan default sebelum tab diklik.
    $ledgerFrames    = $ledgerFrames ?? [];
    $ledgerDefaultIdx = count($ledgerFrames) - 1;
    $ledgerDefault    = $ledgerFrames[$ledgerDefaultIdx] ?? null;

    // IPG & PSG tampil di kedua jenis monitoring: untuk pending pakai frame
    // 4 hari yang sama, untuk close cukup satu frame (data bulan terpilih).
    $towerFrames = $isPending
        ? $toFrames('towerRows')
        : collect([['label' => $bulanLabel ?: 'Bulan ini', 'rows' => $closeTowerRows]]);
@endphp

<div id="dashboardExportArea">

<header class="page-head">
    <div>
        <h1 class="page-title">Dashboard Master</h1>
        <p class="page-sub">
            Rekap Pending Case, case close, dan beban pending per CE untuk seluruh cabang.
        </p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn-quiet" onclick="window.print()">Cetak halaman</button>
        <button type="button" class="btn-quiet" id="btnDownloadDashboardJpg">
            <span id="btnDownloadDashboardJpgLabel">Unduh JPG</span>
        </button>
    </div>
</header>

<form method="GET" action="/dashboard-master" class="toolbar">

    <div class="field">
        <label for="f-jenis">Jenis monitoring</label>
        <select name="jenis_monitoring" id="f-jenis">
            <option value="pending_case" {{ $isPending ? 'selected' : '' }}>Pending case</option>
            <option value="close_case" {{ ! $isPending ? 'selected' : '' }}>Close case</option>
        </select>
    </div>

    <div class="field">
        <label for="f-cabang">Cabang</label>
        <select name="cabang" id="f-cabang">
            <option value="" {{ ! $cabang ? 'selected' : '' }}>Semua cabang</option>
            <option value="Semarang" {{ $cabang == 'Semarang' ? 'selected' : '' }}>Semarang</option>
            <option value="Cirebon" {{ $cabang == 'Cirebon' ? 'selected' : '' }}>Cirebon</option>
        </select>
    </div>

    @if(! $isPending && count($availableMonths))
        <div class="field">
            <label for="f-bulan">Bulan</label>
            <select name="bulan" id="f-bulan">
                @foreach($availableMonths as $m)
                    <option value="{{ $m }}" {{ $bulanTerpilih == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $m)->translatedFormat('F Y') }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="toolbar__actions">
        <button type="submit" class="btn-main">Terapkan filter</button>
        <a href="/dashboard-master" class="btn-quiet">Atur ulang</a>
    </div>

</form>

<div class="context-bar">
    <span class="chip"><span class="chip__dot"></span>{{ $cabang ?: 'Semua cabang' }}</span>
    <span class="chip">{{ $labelJenis }}</span>
    @if(! $isPending && $bulanLabel)
        <span class="chip">{{ $bulanLabel }}</span>
    @endif
    @if($activeLastUpdate)
        <span class="chip chip--time">
            Data per {{ \Carbon\Carbon::parse($activeLastUpdate)->format('d/m/Y H:i') }}
        </span>
    @endif
</div>

<p class="note mb-4">
    Grafik detail per CE mengikuti jenis monitoring yang dipilih, karena tiap grafik hanya
    relevan untuk salah satu jenis. Ringkasan case masuk dan case close di bawah menampilkan
    data per hari — geser tab untuk membandingkan hari ini dengan 3 hari kerja sebelumnya
    (Minggu dikecualikan, karena libur kerja).
</p>

{{-- ------------------------------------------------------------------
     RINGKASAN — dua kelompok angka dipisah garis, bukan enam kartu
     terpisah, supaya hubungan "masuk vs close" terbaca dalam satu tarikan.

     ★★★ DIUBAH: dulu selalu rekap sepanjang waktu. Sekarang dibatasi ke
     jendela 4 hari (hari ini + 3 hari sebelumnya) lewat tab di atas kedua
     kartu — satu tab mengontrol keduanya sekaligus karena "masuk" & "close"
     sama-sama dilihat untuk tanggal yang sama. "Case masuk" pakai
     received_date, "Case close" pakai closed_date (tanggal case BENAR-BENAR
     ditutup — status Close Repair / Close Cancel, cust sudah pick up).
     Nilai default saat halaman dibuka = "Hari ini".
     ------------------------------------------------------------------ --}}
@if(count($ledgerFrames) > 1)
    <div class="tabs" data-ledger-tabs="ledgerCase">
        @foreach($ledgerFrames as $i => $f)
            <button type="button" class="{{ $i === $ledgerDefaultIdx ? 'active' : '' }}" data-idx="{{ $i }}">{{ $f['label'] }}</button>
        @endforeach
    </div>
@endif

<section class="panel ledger">

    <div class="ledger__group">
        <div class="ledger__head">
            <h2>Pending Case</h2>
            <span class="ledger__stamp" id="ledgerDateStamp">{{ $ledgerDefault['label'] ?? '' }}</span>
        </div>

        <div class="metrics">
            <div class="metric">
                <div class="metric__label">In warranty</div>
                <div class="metric__value metric__value--iw" id="ledgerMasukIw">{{ number_format($ledgerDefault['masuk']['iw'] ?? 0) }}</div>
                <div class="metric__note" id="ledgerMasukIwNote">{{ number_format($ledgerDefault['pending']['iw'] ?? 0) }} pending + {{ number_format($ledgerDefault['closeFromMasuk']['iw'] ?? 0) }} close</div>
            </div>
            <div class="metric">
                <div class="metric__label">Out warranty</div>
                <div class="metric__value metric__value--ow" id="ledgerMasukOw">{{ number_format($ledgerDefault['masuk']['ow'] ?? 0) }}</div>
                <div class="metric__note" id="ledgerMasukOwNote">{{ number_format($ledgerDefault['pending']['ow'] ?? 0) }} pending + {{ number_format($ledgerDefault['closeFromMasuk']['ow'] ?? 0) }} close</div>
            </div>
            <div class="metric">
                <div class="metric__label">Total</div>
                <div class="metric__value" id="ledgerMasukTotal">{{ number_format($ledgerDefault['masuk']['total'] ?? 0) }}</div>
                <div class="metric__note" id="ledgerMasukTotalNote">{{ number_format($ledgerDefault['pending']['total'] ?? 0) }} pending + {{ number_format($ledgerDefault['closeFromMasuk']['total'] ?? 0) }} close</div>
            </div>
        </div>
    </div>

    <div class="ledger__group">
        <div class="ledger__head">
            <h2>Close Case</h2>
            <span class="ledger__stamp">Berdasarkan closed date</span>
        </div>

        <div class="metrics">
            <div class="metric">
                <div class="metric__label">In warranty</div>
                <div class="metric__value metric__value--iw" id="ledgerCloseIw">{{ number_format($ledgerDefault['close']['iw'] ?? 0) }}</div>
            </div>
            <div class="metric">
                <div class="metric__label">Out warranty</div>
                <div class="metric__value metric__value--ow" id="ledgerCloseOw">{{ number_format($ledgerDefault['close']['ow'] ?? 0) }}</div>
            </div>
            <div class="metric">
                <div class="metric__label">Total</div>
                <div class="metric__value" id="ledgerCloseTotal">{{ number_format($ledgerDefault['close']['total'] ?? 0) }}</div>
            </div>
        </div>

        <div class="ledger__foot">
            <div><span>Belum close (total backlog saat ini) — IW</span> <b>{{ number_format($pendingIw) }}</b></div>
            <div><span>OW</span> <b>{{ number_format($pendingOw) }}</b></div>
            <div><span>Total</span> <b>{{ number_format($pendingTotal) }}</b></div>
        </div>
    </div>

</section>

{{-- ------------------------------------------------------------------
     GRAFIK PERBANDINGAN — Case masuk vs Case close, 4 hari terakhir
     (3 hari ke belakang + hari ini) DIGAMBAR SEKALIGUS DALAM SATU
     GRAFIK (bukan lewat tab seperti kartu angka di atas), supaya
     perbandingan antar hari langsung kebaca sekali lihat. Dua grafik
     ditaruh bersebelahan: Case masuk (received date) di kiri, Case
     close (closed date — case yang sudah Close Repair / Close Cancel di
     close_case) di kanan. Bentuknya grafik GARIS + titik, dengan garis
     bantu putus-putus dari tiap titik ke sumbu-X & sumbu-Y (gaya grafik
     "berat badan per tahun"). Angka + persentase di atas tiap titik =
     total case hari itu dan porsinya terhadap total 4 hari yang tampil
     di grafik yang sama.
     ------------------------------------------------------------------ --}}
<section class="panel">
    <div class="panel__head">
        <div>
            <h2 class="panel__title">Perbandingan Pending Case &amp; Close Case (3 hari terakhir)</h2>
            <p class="panel__desc">
                Pending Case memakai received date (gabungan pending + yang sudah close).
                Close Case memakai closed date, hanya case dengan status Close Repair atau
                Close Cancel. Bar dipecah IW (bawah) dan OW (atas) dengan persentase
                masing-masing terhadap total hari itu; angka di atas bar adalah total case
                hari itu, disertai naik/turun (%) dibanding hari sebelumnya yang tampil.
                Hari Minggu dikecualikan dari jendela 3 hari ini karena libur kerja.
            </p>
        </div>
        <div class="panel__tools">
            <button type="button" class="btn-link-quiet" data-chart-export="chartLedgerMasuk" data-export-name="case-masuk-3-hari">Unduh CSV masuk</button>
            <button type="button" class="btn-link-quiet" data-chart-export="chartLedgerClose" data-export-name="case-close-3-hari">Unduh CSV close</button>
        </div>
    </div>
    <div class="ledger-compare">
        <div class="ledger-compare__col">
            <h3 class="ledger-compare__title">Pending Case</h3>
            <div class="chart-box"><canvas id="chartLedgerMasuk" height="150"></canvas></div>
            <p class="chart-note" id="chartLedgerMasukTotal"></p>
        </div>
        <div class="ledger-compare__col">
            <h3 class="ledger-compare__title">Close Case</h3>
            <div class="chart-box"><canvas id="chartLedgerClose" height="150"></canvas></div>
            <p class="chart-note" id="chartLedgerCloseTotal"></p>
        </div>
    </div>
</section>

<style>
    .ledger-compare {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        align-items: start;
    }
    .ledger-compare__title {
        margin: 0 0 10px;
        font-size: 13px;
        font-weight: 700;
        color: #33475A;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    @media (max-width: 780px) {
        .ledger-compare {
            grid-template-columns: 1fr;
            gap: 28px;
        }
    }
</style>

{{-- Grafik TAT > 5 hari (case masuk & close) --}}
@include('partials.tat-over5-chart')

@if($isPending)

    {{-- ------------------------------------------------------------------
         PIE KOMPOSISI PART & QUOTATION — sengaja ditaruh paling atas dari
         semua grafik Dashboard Master, sama seperti di Dashboard CE.
         ------------------------------------------------------------------ --}}
    @php
        $statusPieTotal = array_sum($statusPieData ?? []);
        $statusPieColors = [
            'Part Request'        => '#2E86AB',
            'Part Order Logistic' => '#0B6FB8',
            'Part Available'      => '#C0392B',
            'Quotation Request'   => '#C4881F',
            'Quotation Pending'   => '#6B5B95',
            'Quotation Approved'  => '#0B6B4E',
        ];
        $statusPct = fn ($v) => $statusPieTotal > 0 ? round($v / $statusPieTotal * 100) : 0;
    @endphp
    <section class="panel">
        <div class="panel__head">
            <div>
                <h2 class="panel__title">Komposisi part &amp; quotation</h2>
                <p class="panel__desc">
                    Case pending pada 6 status ini, dijumlah dari seluruh CE dan seluruh cabang
                    terpilih — Part Request, Part Order Logistic, Part Available, Quotation
                    Request, Quotation Pending, dan Quotation Approved.
                </p>
            </div>
        </div>
        <div class="panel__body">
            @if($statusPieTotal === 0)
                <div class="empty">Tidak ada case pada keenam status ini untuk filter ini.</div>
            @else
                <div class="ring-grid">
                    @foreach($statusPieColors as $label => $color)
                        @php $v = $statusPieData[$label] ?? 0; @endphp
                        <div class="ring-card">
                            <div class="ring-card__wrap">
                                <canvas id="ring-{{ \Illuminate\Support\Str::slug($label) }}" width="112" height="112"></canvas>
                                <div class="ring-card__center">
                                    <div class="ring-card__num">{{ number_format($v) }}</div>
                                    <div class="ring-card__pct">{{ $statusPct($v) }}%</div>
                                </div>
                            </div>
                            <div class="ring-card__label">{{ $label }}</div>
                        </div>
                    @endforeach
                </div>
                <p class="chart-note" style="text-align:center;">
                    {{ number_format($statusPieTotal) }} case pada keenam status ini.
                </p>
            @endif
        </div>
    </section>

    {{-- ------------------------------------------------------------------
         UMUR CASE PENDING — satu bar komposisi sebagai penanda utama
         halaman. Ini angka yang paling sering ditanya duluan: berapa banyak
         case yang sudah lewat 14 hari.
         ------------------------------------------------------------------ --}}
    <section class="panel">
        <div class="panel__head">
            <div>
                <h2 class="panel__title">Komposisi umur case pending</h2>
                <p class="panel__desc">Dihitung dari received date sampai hari ini.</p>
            </div>
        </div>
        <div class="panel__body">
            @if($ageTotal === 0)
                <div class="empty">Tidak ada case pending pada filter ini.</div>
            @else
                <div class="agebar" role="img"
                    aria-label="Umur case pending: {{ $agingGrandTotal['d0_7'] }}  7 hari, {{ $agingGrandTotal['d8_14'] }} 8 - 14 hari, {{ $agingGrandTotal['over14'] }} lebih dari 14 hari">
                    <div class="agebar__seg agebar__seg--fresh" style="width: {{ $pct($agingGrandTotal['d0_7']) }}%"></div>
                    <div class="agebar__seg agebar__seg--warn"  style="width: {{ $pct($agingGrandTotal['d8_14']) }}%"></div>
                    <div class="agebar__seg agebar__seg--crit"  style="width: {{ $pct($agingGrandTotal['over14']) }}%"></div>
                </div>

                <div class="agelegend">
                    <div class="agelegend__item">
                        <span class="agelegend__key" style="background:#2E8F6E"></span>
                        <span class="agelegend__num">{{ number_format($agingGrandTotal['d0_7']) }}</span>
                        sampai 7 hari <span class="agelegend__pct">{{ $pct($agingGrandTotal['d0_7']) }}%</span>
                    </div>
                    <div class="agelegend__item">
                        <span class="agelegend__key" style="background:#D89A34"></span>
                        <span class="agelegend__num">{{ number_format($agingGrandTotal['d8_14']) }}</span>
                        8 sampai 14 hari <span class="agelegend__pct">{{ $pct($agingGrandTotal['d8_14']) }}%</span>
                    </div>
                    <div class="agelegend__item">
                        <span class="agelegend__key" style="background:#C0483C"></span>
                        <span class="agelegend__num">{{ number_format($agingGrandTotal['over14']) }}</span>
                        lebih dari 14 hari <span class="agelegend__pct">{{ $pct($agingGrandTotal['over14']) }}%</span>
                    </div>
                    <div class="agelegend__item">
                        <span class="agelegend__num">{{ number_format($ageTotal) }}</span> total pending
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- AGING PER CE — grafik batang ditumpuk (0-7 / 8-14 / >14 hari) --}}
    <section class="panel">
        <div class="panel__head">
            <div>
                <h2 class="panel__title">Aging case per CE</h2>
                <p class="panel__desc">
                    Case yang masih pending, dikelompokkan berdasarkan lama sejak diterima.
                    Geser tab untuk bandingkan dengan 3 hari kerja sebelumnya (Minggu dikecualikan).
                </p>
            </div>
            <div class="panel__tools">
                <button type="button" class="btn-link-quiet" data-chart-export="chartAging" data-export-name="aging-per-ce">Unduh CSV</button>
            </div>
        </div>
        <div class="panel__body">
            @if(count($agingFrames) > 1)
                <div class="tabs" data-chart-tabs="chartAging">
                    @foreach($agingFrames as $i => $f)
                        <button type="button" class="{{ $i === count($agingFrames) - 1 ? 'active' : '' }}" data-idx="{{ $i }}">{{ $f['label'] }}</button>
                    @endforeach
                </div>
            @endif
            <div class="chart-box"><canvas id="chartAging" height="130"></canvas></div>
            <p class="chart-note" id="chartAgingTotal"></p>
        </div>
    </section>

    {{-- PART ORDER --}}
    <section class="panel">
        <div class="panel__head">
            <div>
                <h2 class="panel__title">Case part order per CE</h2>
                <p class="panel__desc">Case pending berstatus Part Order Logistic.</p>
            </div>
            <div class="panel__tools">
                <button type="button" class="btn-link-quiet" data-chart-export="chartPartOrder" data-export-name="part-order-per-ce">Unduh CSV</button>
            </div>
        </div>
        <div class="panel__body">
            @if(count($partOrderFrames) > 1)
                <div class="tabs" data-chart-tabs="chartPartOrder">
                    @foreach($partOrderFrames as $i => $f)
                        <button type="button" class="{{ $i === count($partOrderFrames) - 1 ? 'active' : '' }}" data-idx="{{ $i }}">{{ $f['label'] }}</button>
                    @endforeach
                </div>
            @endif
            <div class="chart-box"><canvas id="chartPartOrder" height="90"></canvas></div>
            <p class="chart-note" id="chartPartOrderTotal"></p>
        </div>
    </section>

    {{-- PART REQUEST LOGISTIC --}}
    <section class="panel">
        <div class="panel__head">
            <div>
                <h2 class="panel__title">Case part request logistic per CE</h2>
                <p class="panel__desc">Case pending berstatus Part Request Logistic.</p>
            </div>
            <div class="panel__tools">
                <button type="button" class="btn-link-quiet" data-chart-export="chartPartReq" data-export-name="part-request-per-ce">Unduh CSV</button>
            </div>
        </div>
        <div class="panel__body">
            @if(count($partReqFrames) > 1)
                <div class="tabs" data-chart-tabs="chartPartReq">
                    @foreach($partReqFrames as $i => $f)
                        <button type="button" class="{{ $i === count($partReqFrames) - 1 ? 'active' : '' }}" data-idx="{{ $i }}">{{ $f['label'] }}</button>
                    @endforeach
                </div>
            @endif
            <div class="chart-box"><canvas id="chartPartReq" height="90"></canvas></div>
            <p class="chart-note" id="chartPartReqTotal"></p>
        </div>
    </section>

    {{-- CASE PART READY — dulu digabung jadi satu grafik bertumpuk dengan
         Non-part (label "Ada part"). Sekarang dipisah jadi grafik sendiri
         dan labelnya diganti "Part Ready" — case Repair Progress yang
         sparepart-nya sudah tervalidasi, jadi tinggal dikerjakan begitu
         part di tangan. --}}
    <section class="panel">
        <div class="panel__head">
            <div>
                <h2 class="panel__title">Case Part Ready per CE</h2>
                <p class="panel__desc">
                    Case berstatus Repair Progress yang sparepart-nya sudah tervalidasi —
                    Case ID manual cocok dengan sheet Parts pada file Excel yang diupload.
                </p>
            </div>
            <div class="panel__tools">
                <button type="button" class="btn-link-quiet" data-chart-export="chartWipPart" data-export-name="part-ready-per-ce">Unduh CSV</button>
            </div>
        </div>
        <div class="panel__body">
            @if(count($wipFrames) > 1)
                <div class="tabs" data-chart-tabs="chartWipPart">
                    @foreach($wipFrames as $i => $f)
                        <button type="button" class="{{ $i === count($wipFrames) - 1 ? 'active' : '' }}" data-idx="{{ $i }}">{{ $f['label'] }}</button>
                    @endforeach
                </div>
            @endif
            <div class="chart-box"><canvas id="chartWipPart" height="90"></canvas></div>
            <p class="chart-note" id="chartWipPartTotal"></p>
        </div>
    </section>

    {{-- CASE NON-PART — dipisah dari grafik Part Ready di atas, supaya
         masing-masing kelihatan trennya sendiri per hari tanpa harus
         menguraikan bar bertumpuk. --}}
    <section class="panel">
        <div class="panel__head">
            <div>
                <h2 class="panel__title">Case Non-part per CE</h2>
                <p class="panel__desc">
                    Case berstatus Repair Progress yang sama sekali tidak butuh sparepart,
                    divalidasi dari Case ID manual terhadap sheet Parts pada file Excel yang
                    diupload.
                </p>
            </div>
            <div class="panel__tools">
                <button type="button" class="btn-link-quiet" data-chart-export="chartWipNonPart" data-export-name="non-part-per-ce">Unduh CSV</button>
            </div>
        </div>
        <div class="panel__body">
            @if(count($wipFrames) > 1)
                <div class="tabs" data-chart-tabs="chartWipNonPart">
                    @foreach($wipFrames as $i => $f)
                        <button type="button" class="{{ $i === count($wipFrames) - 1 ? 'active' : '' }}" data-idx="{{ $i }}">{{ $f['label'] }}</button>
                    @endforeach
                </div>
            @endif
            <div class="chart-box"><canvas id="chartWipNonPart" height="90"></canvas></div>
            <p class="chart-note" id="chartWipNonPartTotal"></p>
        </div>
    </section>

@endif

{{-- IPG & PSG — tampil di kedua jenis monitoring. --}}
<section class="panel">
    <div class="panel__head">
        <div>
            <h2 class="panel__title">Case IPG &amp; PSG per CE</h2>
            <p class="panel__desc">
                {{ $isPending ? 'Case yang masih pending' : 'Case yang sudah close' }},
                dikelompokkan berdasarkan product tower: IPG untuk printer dan imaging,
                PSG untuk laptop dan desktop.
                @if(! $isPending && $bulanLabel) Periode {{ $bulanLabel }}. @endif
                @if($isPending) Geser tab untuk bandingkan dengan 3 hari kerja sebelumnya (Minggu dikecualikan). @endif
            </p>
        </div>
        <div class="panel__tools">
            <button type="button" class="btn-link-quiet" data-chart-export="chartTower" data-export-name="ipg-psg-per-ce">Unduh CSV</button>
        </div>
    </div>
    <div class="panel__body">
        @if(count($towerFrames) > 1)
            <div class="tabs" data-chart-tabs="chartTower">
                @foreach($towerFrames as $i => $f)
                    <button type="button" class="{{ $i === count($towerFrames) - 1 ? 'active' : '' }}" data-idx="{{ $i }}">{{ $f['label'] }}</button>
                @endforeach
            </div>
        @endif
        <div class="chart-box"><canvas id="chartTower" height="130"></canvas></div>
        <p class="chart-note" id="chartTowerTotal"></p>
    </div>
</section>

@if(! $isPending)

    {{-- CLOSE REPAIR --}}
    <section class="panel">
        <div class="panel__head">
            <div>
                <h2 class="panel__title">Close repair per CE</h2>
                <p class="panel__desc">
                    Case yang sudah close. Close cancel (dibatalkan customer) ditampilkan terpisah
                    sebagai informasi tambahan, bukan bagian dari close repair.
                    @if($bulanLabel) Periode {{ $bulanLabel }}. @endif
                </p>
            </div>
            <div class="panel__tools">
                <button type="button" class="btn-link-quiet" data-chart-export="chartCloseRepair" data-export-name="close-repair-per-ce">Unduh CSV</button>
            </div>
        </div>
        <div class="panel__body">
            <div class="chart-box"><canvas id="chartCloseRepair" height="130"></canvas></div>
            <p class="chart-note" id="chartCloseRepairTotal"></p>
        </div>
    </section>

    {{-- LOGGED BY & CLOSED BY --}}
    <section class="panel">
        <div class="panel__head">
            <div>
                <h2 class="panel__title">Logged by dan closed by</h2>
                <p class="panel__desc">
                    Siapa yang mencatat case ke sistem dan siapa yang menutupnya, diambil dari kolom
                    Logged by dan Closed by pada file Excel. Nama di sini bisa berbeda dari CE yang
                    mengerjakan case.
                    @if($bulanLabel) Periode {{ $bulanLabel }}. @endif
                </p>
            </div>
            <div class="panel__tools">
                @if(! empty($loggedClosedRows))
                    <button type="button" class="btn-link-quiet" data-chart-export="chartLoggedClosed" data-export-name="logged-closed">Unduh CSV</button>
                @endif
            </div>
        </div>
        <div class="panel__body">
            @if(empty($loggedClosedRows))
                <div class="empty">
                    Belum ada data logged by dan closed by untuk periode ini. Data akan muncul setelah
                    file close case periode tersebut diupload.
                </div>
            @else
                <div class="chart-box"><canvas id="chartLoggedClosed" height="130"></canvas></div>
                <p class="chart-note" id="chartLoggedClosedTotal"></p>
            @endif
        </div>
    </section>

@endif

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
(function () {
    'use strict';
    if (typeof Chart === 'undefined') return;          // CDN gagal dimuat, halaman tetap jalan
    Chart.register(ChartDataLabels);

    Chart.defaults.font.family = "'IBM Plex Sans', system-ui, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#33475A';

    var grid = { color: '#E1E8EF' };
    var charts = {};   // registry: canvasId -> { chart, applyIdx|null }

    function baseOptions(stacked, showLegend) {
        return {
            responsive: true,
            plugins: {
                legend: { display: !!showLegend, position: 'bottom', labels: { boxWidth: 12, padding: 14 } },
                datalabels: {
                    font: { size: 10, weight: '600' },
                    color: stacked ? '#fff' : '#10222F',
                    anchor: stacked ? 'center' : 'end',
                    align: stacked ? 'center' : 'top',
                    formatter: function (v) { return v > 0 ? v : ''; }
                }
            },
            scales: {
                x: { stacked: stacked, grid: { display: false } },
                y: { stacked: stacked, beginAtZero: true, grace: '10%', grid: grid, border: { display: false } }
            }
        };
    }

    // frames: [{label, rows}, ...] — dibuat dari data terkirim controller.
    // buildDatasets(rows) => {labels, datasets}. totalFn(rows) => teks ringkasan.
    function makeFrameChart(canvasId, frames, buildDatasets, options, totalFn) {
        var el = document.getElementById(canvasId);
        if (!el || !frames || !frames.length) return;

        var defaultIdx = frames.length - 1;   // "Hari ini" / satu-satunya frame
        var chart = new Chart(el, { type: 'bar', data: buildDatasets(frames[defaultIdx].rows), options: options });

        function applyIdx(idx) {
            var d = buildDatasets(frames[idx].rows);
            chart.data.labels = d.labels;
            chart.data.datasets = d.datasets;
            chart.update();

            if (totalFn) {
                var noteEl = document.getElementById(canvasId + 'Total');
                if (noteEl) noteEl.textContent = totalFn(frames[idx].rows) + ' — ' + frames[idx].label;
            }
        }

        applyIdx(defaultIdx);
        charts[canvasId] = { chart: chart, applyIdx: frames.length > 1 ? applyIdx : null };
    }

    function names(rows) { return rows.map(function (r) { return r.name; }); }
    function sumBy(rows, key) { return rows.reduce(function (s, r) { return s + (r[key] || 0); }, 0); }

    // ----- Ring komposisi part & quotation — 6 lingkaran terpisah -----
    function makeRing(canvasId, value, total, color) {
        var el = document.getElementById(canvasId);
        if (!el) return;

        var sisa = Math.max(total - value, 0);

        new Chart(el, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [value, sisa],
                    backgroundColor: [color, '#E7ECF1'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false },
                    datalabels: { display: false }
                }
            }
        });
    }

    (function () {
        var pieData = @json($statusPieData ?? []);
        var pieColors = {
            'Part Request': '#2E86AB',
            'Part Order Logistic': '#0B6FB8',
            'Part Available': '#C0392B',
            'Quotation Request': '#C4881F',
            'Quotation Pending': '#6B5B95',
            'Quotation Approved': '#0B6B4E'
        };
        var pieTotal = Object.keys(pieData).reduce(function (s, k) { return s + (pieData[k] || 0); }, 0);

        Object.keys(pieColors).forEach(function (label) {
            var slug = label.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            makeRing('ring-' + slug, pieData[label] || 0, pieTotal, pieColors[label]);
        });
    })();

    // ----- Aging (stacked 3 warna) -----
    makeFrameChart('chartAging', @json($agingFrames ?? []), function (rows) {
        return {
            labels: names(rows),
            datasets: [
                { label: ' 7 hari',   data: rows.map(function (r) { return r.d0_7; }),   backgroundColor: '#237A5C', stack: 's' },
                { label: '8–14 hari',       data: rows.map(function (r) { return r.d8_14; }),  backgroundColor: '#C4881F', stack: 's' },
                { label: 'Di atas 14 hari', data: rows.map(function (r) { return r.over14; }), backgroundColor: '#B03A2E', stack: 's' }
            ]
        };
    }, baseOptions(true, true), function (rows) { return sumBy(rows, 'total') + ' case pending'; });

    // ----- Part order (satu warna) -----
    makeFrameChart('chartPartOrder', @json($partOrderFrames ?? []), function (rows) {
        return { labels: names(rows), datasets: [{ label: 'Part order', data: rows.map(function (r) { return r.total; }), backgroundColor: '#0B6FB8', borderRadius: 4, maxBarThickness: 54 }] };
    }, baseOptions(false, false), function (rows) { return sumBy(rows, 'total') + ' case part order'; });

    // ----- Part request logistic (satu warna) -----
    makeFrameChart('chartPartReq', @json($partReqFrames ?? []), function (rows) {
        return { labels: names(rows), datasets: [{ label: 'Part request logistic', data: rows.map(function (r) { return r.total; }), backgroundColor: '#2E86AB', borderRadius: 4, maxBarThickness: 54 }] };
    }, baseOptions(false, false), function (rows) { return sumBy(rows, 'total') + ' case part request logistic'; });

    // ----- Case Part Ready (satu warna — sebelumnya "Ada part", digabung
    // dengan Non-part dalam satu bar bertumpuk) -----
    makeFrameChart('chartWipPart', @json($wipFrames ?? []), function (rows) {
        return { labels: names(rows), datasets: [{ label: 'Part Ready', data: rows.map(function (r) { return r.with_part; }), backgroundColor: '#0B6FB8', borderRadius: 4, maxBarThickness: 54 }] };
    }, baseOptions(false, false), function (rows) { return sumBy(rows, 'with_part') + ' case Part Ready'; });

    // ----- Case Non-part (satu warna — dipisah dari Part Ready di atas) -----
    makeFrameChart('chartWipNonPart', @json($wipFrames ?? []), function (rows) {
        return { labels: names(rows), datasets: [{ label: 'Non-part', data: rows.map(function (r) { return r.without_part; }), backgroundColor: '#C4881F', borderRadius: 4, maxBarThickness: 54 }] };
    }, baseOptions(false, false), function (rows) { return sumBy(rows, 'without_part') + ' case non-part'; });

    // ----- IPG & PSG (stacked) -----
    makeFrameChart('chartTower', @json($towerFrames ?? []), function (rows) {
        return {
            labels: names(rows),
            datasets: [
                { label: 'IPG', data: rows.map(function (r) { return r.ipg; }), backgroundColor: '#0B6FB8', stack: 't' },
                { label: 'PSG', data: rows.map(function (r) { return r.psg; }), backgroundColor: '#2E86AB', stack: 't' }
            ]
        };
    }, baseOptions(true, true), function (rows) { return sumBy(rows, 'total') + ' case'; });

    // ----- Close repair (stacked, close case saja) -----
    makeFrameChart('chartCloseRepair', @json(! $isPending ? [['label' => $bulanLabel ?: 'Bulan ini', 'rows' => $closeRepairRows]] : []), function (rows) {
        return {
            labels: names(rows),
            datasets: [
                { label: 'Close repair', data: rows.map(function (r) { return r.repair; }), backgroundColor: '#0B6B4E', stack: 'c' },
                { label: 'Close cancel', data: rows.map(function (r) { return r.cancel; }), backgroundColor: '#5B7285', stack: 'c' }
            ]
        };
    }, baseOptions(true, true), function (rows) { return sumBy(rows, 'repair') + ' close repair, ' + sumBy(rows, 'cancel') + ' close cancel'; });

    // ----- Logged by / closed by (grouped, bukan stacked — dua peran beda) -----
    makeFrameChart('chartLoggedClosed', @json(! $isPending && ! empty($loggedClosedRows) ? [['label' => $bulanLabel ?: 'Bulan ini', 'rows' => $loggedClosedRows]] : []), function (rows) {
        return {
            labels: names(rows),
            datasets: [
                { label: 'Logged', data: rows.map(function (r) { return r.logged; }), backgroundColor: '#0B6FB8', maxBarThickness: 44 },
                { label: 'Closed', data: rows.map(function (r) { return r.closed; }), backgroundColor: '#0B6B4E', maxBarThickness: 44 }
            ]
        };
    }, baseOptions(false, true), function (rows) { return sumBy(rows, 'logged') + ' logged, ' + sumBy(rows, 'closed') + ' closed'; });

    // ----- Grafik perbandingan Case Masuk & Case Close (COMBO bar + garis),
    // disamakan dengan grafik di halaman login: bar DITUMPUK IW (biru) di
    // bawah + OW (oranye) di atas, tiap segmen berlabel "jumlah (persen%)"
    // dari total hari itu; garis lurus menyambung TOTAL tiap hari, angka
    // total di atas titik + indikator naik/turun (▲/▼ %) dibanding hari
    // sebelumnya yang tampil. Sumbu-X pakai TANGGAL ASLI (bukan "Hari
    // ini") dan jendelanya 3 hari kerja terakhir (H-2, H-1, hari ini).
    // Data tetap dari $ledgerFrames (kartu angka & tab di atas tidak
    // berubah, masih 4 hari).
    (function () {
        var allFrames = @json($ledgerFrames ?? []);
        var frames = allFrames.slice(-3);
        if (!frames.length) return;

        var MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var dayLabels = frames.map(function (f) {
            var d = String(f.date).split('-'); // 'YYYY-MM-DD'
            return d.length === 3 ? (d[2] + ' ' + MONTHS[parseInt(d[1], 10) - 1]) : f.label;
        });

        var colorIw = '#2F6FBA', colorOw = '#D98C2B', colorLine = '#10222F';

        function seriesOf(key, part) {
            return frames.map(function (f) { return f[key][part] || 0; });
        }

        function segmentLabelPlugin(iwValues, owValues) {
            return {
                id: 'segmentLabel',
                afterDatasetsDraw: function (chart) {
                    var ctx = chart.ctx;
                    ctx.save();
                    ctx.font = "700 10.5px 'IBM Plex Sans', system-ui, sans-serif";
                    ctx.textAlign = 'center';
                    ctx.fillStyle = '#fff';
                    [iwValues, owValues].forEach(function (values, datasetIndex) {
                        chart.getDatasetMeta(datasetIndex).data.forEach(function (el, i) {
                            var v = values[i] || 0;
                            if (!v) return;
                            var total = (iwValues[i] || 0) + (owValues[i] || 0);
                            var pct = total > 0 ? Math.round((v / total) * 100) : 0;
                            ctx.fillText(v + ' (' + pct + '%)', el.x, (el.y + el.base) / 2 + 4);
                        });
                    });
                    ctx.restore();
                }
            };
        }

        function totalDeltaPlugin(totals, lineIdx) {
            return {
                id: 'totalDelta',
                afterDatasetsDraw: function (chart) {
                    var ctx = chart.ctx;
                    ctx.save();
                    ctx.textAlign = 'center';
                    chart.getDatasetMeta(lineIdx).data.forEach(function (point, i) {
                        var total = totals[i] || 0;
                        ctx.font = "700 13px 'IBM Plex Sans', system-ui, sans-serif";
                        ctx.fillStyle = '#10222F';
                        ctx.fillText(String(total), point.x, point.y - 26);

                        if (i > 0) {
                            var prev = totals[i - 1] || 0, txt = '', col = '#8a97a3';
                            if (prev > 0) {
                                var pct = Math.round(((total - prev) / prev) * 100);
                                if (pct > 0) { txt = '\u25B2 ' + pct + '%'; col = '#1E8A4C'; }
                                else if (pct < 0) { txt = '\u25BC ' + Math.abs(pct) + '%'; col = '#C23B3B'; }
                                else { txt = '\u2013 0%'; }
                            } else if (total > 0) { txt = 'baru'; col = '#1E8A4C'; }
                            if (txt) {
                                ctx.font = "700 11px 'IBM Plex Sans', system-ui, sans-serif";
                                ctx.fillStyle = col;
                                ctx.fillText(txt, point.x, point.y - 12);
                            }
                        }
                    });
                    ctx.restore();
                }
            };
        }

        function buildLedgerCompareChart(canvasId, key, sumLabel) {
            var el = document.getElementById(canvasId);
            if (!el) return;

            var iw = seriesOf(key, 'iw'), ow = seriesOf(key, 'ow'), totals = seriesOf(key, 'total');

            var chart = new Chart(el, {
                data: {
                    labels: dayLabels,
                    datasets: [
                        { type: 'bar', label: 'IW', data: iw, backgroundColor: colorIw, stack: 'total', maxBarThickness: 70 },
                        { type: 'bar', label: 'OW', data: ow, backgroundColor: colorOw, stack: 'total', maxBarThickness: 70 },
                        {
                            type: 'line', label: 'Total', data: totals,
                            borderColor: colorLine, backgroundColor: colorLine,
                            pointBackgroundColor: colorLine, pointBorderColor: '#fff', pointBorderWidth: 2,
                            pointRadius: 6, pointHoverRadius: 7, borderWidth: 2.5, tension: 0, fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    layout: { padding: { top: 48 } },
                    plugins: {
                        legend: {
                            display: true, position: 'bottom',
                            labels: { boxWidth: 12, padding: 14, filter: function (item) { return item.text !== 'Total'; } }
                        },
                        datalabels: { display: false }
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, beginAtZero: true, grace: '30%', ticks: { precision: 0 }, grid: { color: '#E1E8EF' }, border: { display: false } }
                    }
                },
                plugins: [segmentLabelPlugin(iw, ow), totalDeltaPlugin(totals, 2)]
            });

            charts[canvasId] = { chart: chart, applyIdx: null };

            var noteEl = document.getElementById(canvasId + 'Total');
            if (noteEl) {
                var sum = totals.reduce(function (a, b) { return a + b; }, 0);
                noteEl.textContent = sum + ' ' + sumLabel + ' sepanjang ' + frames.length + ' hari yang tampil';
            }
        }

        buildLedgerCompareChart('chartLedgerMasuk', 'masuk', 'Pending Case');
        buildLedgerCompareChart('chartLedgerClose', 'close', 'Close Case');
    })();

    // ----- Kartu "Case masuk" & "Case close" (4 hari terakhir) -----
    // Beda dari chart di atas: ini cuma angka biasa (bukan Chart.js), jadi
    // klik tab langsung ganti textContent elemen terkait lewat ID,
    // memakai data $ledgerFrames yang sudah dikirim controller (SUDAH
    // dihitung per hari di server, JS di sini cuma tampilkan ulang).
    var ledgerFrames = @json($ledgerFrames ?? []);

    function fmtLedger(n) {
        return (n || 0).toLocaleString('en-US');
    }

    function applyLedgerIdx(idx) {
        var f = ledgerFrames[idx];
        if (!f) return;

        var setText = function (id, val) {
            var el = document.getElementById(id);
            if (el) el.textContent = val;
        };

        setText('ledgerDateStamp', f.label);

        setText('ledgerMasukIw', fmtLedger(f.masuk.iw));
        setText('ledgerMasukIwNote', fmtLedger(f.pending.iw) + ' pending + ' + fmtLedger(f.closeFromMasuk.iw) + ' close');
        setText('ledgerMasukOw', fmtLedger(f.masuk.ow));
        setText('ledgerMasukOwNote', fmtLedger(f.pending.ow) + ' pending + ' + fmtLedger(f.closeFromMasuk.ow) + ' close');
        setText('ledgerMasukTotal', fmtLedger(f.masuk.total));
        setText('ledgerMasukTotalNote', fmtLedger(f.pending.total) + ' pending + ' + fmtLedger(f.closeFromMasuk.total) + ' close');

        setText('ledgerCloseIw', fmtLedger(f.close.iw));
        setText('ledgerCloseOw', fmtLedger(f.close.ow));
        setText('ledgerCloseTotal', fmtLedger(f.close.total));
    }

    document.querySelectorAll('[data-ledger-tabs]').forEach(function (tabsEl) {
        tabsEl.querySelectorAll('button').forEach(function (btn) {
            btn.addEventListener('click', function () {
                tabsEl.querySelectorAll('button').forEach(function (b) { b.classList.toggle('active', b === btn); });
                applyLedgerIdx(parseInt(btn.dataset.idx, 10));
            });
        });
    });

    // Tab hari/bulan: klik tombol -> render ulang chart terkait, tanpa reload.
    document.querySelectorAll('[data-chart-tabs]').forEach(function (tabsEl) {
        var key = tabsEl.dataset.chartTabs;
        tabsEl.querySelectorAll('button').forEach(function (btn) {
            btn.addEventListener('click', function () {
                tabsEl.querySelectorAll('button').forEach(function (b) { b.classList.toggle('active', b === btn); });
                var idx = parseInt(btn.dataset.idx, 10);
                if (charts[key] && charts[key].applyIdx) charts[key].applyIdx(idx);
            });
        });
    });

    // Tombol "Unduh CSV": ekspor data grafik yang SEDANG tampil (bukan dari
    // tabel, karena tabelnya sudah tidak ada — datanya diambil langsung
    // dari state Chart.js saat ini).
    document.querySelectorAll('[data-chart-export]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var entry = charts[btn.dataset.chartExport];
            if (!entry) return;

            var labels = entry.chart.data.labels;
            var datasets = entry.chart.data.datasets;
            var baris = [['"CE"'].concat(datasets.map(function (d) { return '"' + d.label.replace(/"/g, '""') + '"'; })).join(',')];

            labels.forEach(function (lbl, i) {
                var kolom = ['"' + String(lbl).replace(/"/g, '""') + '"'];
                datasets.forEach(function (d) { kolom.push(d.data[i] != null ? d.data[i] : ''); });
                baris.push(kolom.join(','));
            });

            var blob = new Blob(['\uFEFF' + baris.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = btn.dataset.exportName + '-' + new Date().toISOString().slice(0, 10) + '.csv';
            a.click();
            URL.revokeObjectURL(a.href);
        });
    });

    // ----- Unduh SELURUH halaman Dashboard Master sebagai JPG -----
    // Pakai html2canvas buat "memotret" #dashboardExportArea (judul, form
    // filter, chip status, semua kartu & grafik) apa adanya, lalu hasilnya
    // dikonversi ke JPEG dan langsung didownload — semuanya di browser,
    // tidak kirim apa pun ke server. Tombol "Cetak halaman" & "Unduh JPG"
    // sendiri (di dalam .page-actions) sengaja DILEWATI saat difoto supaya
    // tidak ikut muncul di gambar hasil unduhan. Karena halamannya bisa
    // panjang (banyak panel/grafik), scale dibikin lebih rendah (1.5x,
    // bukan 2x seperti di halaman login yang jauh lebih pendek) supaya
    // ukuran kanvas tidak mentok limit browser & file JPG-nya tidak
    // kegedean.
    (function () {
        var btn      = document.getElementById('btnDownloadDashboardJpg');
        var btnLabel = document.getElementById('btnDownloadDashboardJpgLabel');
        var area     = document.getElementById('dashboardExportArea');

        if (!btn || !area) return;

        btn.addEventListener('click', function () {
            if (typeof html2canvas === 'undefined') {
                alert('Gagal memuat komponen unduh gambar. Coba muat ulang halaman.');
                return;
            }

            var originalLabel = btnLabel.textContent;
            btn.disabled = true;
            btnLabel.textContent = 'Menyiapkan...';

            // Balik ke paling atas dulu supaya html2canvas menangkap dari
            // posisi (0,0) yang benar, bukan dari posisi scroll saat ini.
            window.scrollTo(0, 0);

            window.setTimeout(function () {
                html2canvas(area, {
                    backgroundColor: '#F4F7FA',
                    scale: 1.5,
                    useCORS: true,
                    windowWidth: document.documentElement.scrollWidth,
                    ignoreElements: function (el) {
                        return el.classList && el.classList.contains('page-actions');
                    }
                }).then(function (canvas) {
                    var link  = document.createElement('a');
                    var stamp = new Date().toISOString().slice(0, 10);
                    link.download = 'dashboard-master-' + stamp + '.jpg';
                    link.href = canvas.toDataURL('image/jpeg', 0.92);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }).catch(function (err) {
                    console.error('Gagal membuat JPG:', err);
                    alert('Gagal membuat gambar untuk diunduh. Coba lagi.');
                }).finally(function () {
                    btn.disabled = false;
                    btnLabel.textContent = originalLabel;
                });
            }, 50);
        });
    })();
})();
</script>
@endpush

@endsection