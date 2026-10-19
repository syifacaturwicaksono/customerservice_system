@extends('layouts.app')

@section('title', 'Dashboard CE')

@section('content')

@php
    /* -----------------------------------------------------------------
    Semua variabel tetap sama persis seperti yang dikirim
    TechnicianReportController — tidak ada perubahan di controller.
       ----------------------------------------------------------------- */

    $isPending  = $jenisMonitoring === 'pending_case';
    $labelJenis = $isPending ? 'Pending case' : 'Close case';

    /* Tiga tabel lama (total case, IW/OW, IPG/PSG) isinya sama-sama
    "satu baris per teknisi", jadi digabung menjadi satu tabel.
    $warrantySummary dipetakan berdasarkan nama supaya urutannya tidak
       harus sama dengan $summary. */
    $wsByName = [];
    foreach ($warrantySummary as $w) {
        $wsByName[$w['name']] = $w;
    }

    $totalCaseSemua = collect($summary)->sum('total_case');

    /* ★★★ BARU: pie chart komposisi 6 status "menunggu part / quotation"
       yang paling sering ditanya duluan — dijumlah lintas semua CE dari
       $pendingPivot yang sudah ada (bukan query baru). Cuma relevan untuk
       Pending Case, karena status ini tidak dipakai di alur Close Case
       ($closeStatusOrder cuma punya Close Repair/Close Cancel). Asumsi:
       "Part Order" di sini berarti status "Part Order Logistic" (satu-
       satunya status Part Order yang ada di database); "Part Available"
       adalah versi pendek dari status DB "Part Available (Part Ready)"
       (label pendek ini sudah dipakai juga di tabel pivot di bawah, lihat
       $pendingStatusOrder di controller); "Part Request" dipakai apa
       adanya sebagai status tersendiri, TERPISAH dari "Part Request
       Logistic" yang juga ada di tabel pivot tapi tidak ikut masuk pie
       ini. */
    $statusPieColors = [
        'Part Request'        => '#2E86AB',
        'Part Order Logistic' => '#0B6FB8',
        'Part Available'      => '#C0392B',
        'Quotation Request'   => '#C4881F',
        'Quotation Pending'   => '#6B5B95',
        'Quotation Approved'  => '#0B6B4E',
    ];

    $pivotByStatus = collect($pendingPivot)->keyBy('status');

    $statusPieData = collect($statusPieColors)->map(
        fn ($color, $label) => $pivotByStatus->has($label) ? array_sum($pivotByStatus[$label]['counts']) : 0
    );

    $statusPieTotal = $statusPieData->sum();
@endphp

<header class="page-head">
    <div>
        <h1 class="page-title">
            Dashboard CE{{ $cabang ? ' — ' . $cabang : '' }}
        </h1>
        <p class="page-sub">Rekap case per teknisi: beban kerja, status, warranty, dan product tower.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn-quiet" onclick="window.print()">Cetak halaman</button>
    </div>
</header>

<form method="GET" action="/technician-dashboard" class="toolbar">

    <div class="field">
        <label for="f-jenis">Jenis monitoring</label>
        <select name="jenis_monitoring" id="f-jenis">
            <option value="close_case" {{ ! $isPending ? 'selected' : '' }}>Close case</option>
            <option value="pending_case" {{ $isPending ? 'selected' : '' }}>Pending case</option>
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
                @foreach($availableMonths as $ym)
                    <option value="{{ $ym }}" {{ $bulanTerpilih == $ym ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $ym)->translatedFormat('F Y') }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="toolbar__actions">
        <button type="submit" class="btn-main">Terapkan filter</button>
        <a href="/technician-dashboard" class="btn-quiet">Atur ulang</a>
    </div>

</form>

<div class="context-bar">
    <span class="chip"><span class="chip__dot"></span>{{ $cabang ?: 'Semua cabang' }}</span>
    <span class="chip">{{ $labelJenis }}</span>
    @if($bulanLabel)
        <span class="chip">{{ $bulanLabel }}</span>
    @endif
    @if($lastUpdate)
        <span class="chip chip--time">Data per {{ \Carbon\Carbon::parse($lastUpdate)->format('d/m/Y H:i') }}</span>
    @endif
    <span class="chip">{{ number_format($totalCaseSemua) }} case · {{ count($summary) }} CE</span>
</div>

<p class="note mb-4">
    Ringkasan case masuk dan case close di bawah menampilkan data per hari — geser tab
    untuk membandingkan hari ini dengan 3 hari kerja sebelumnya (Minggu dikecualikan,
    karena libur kerja). Berlaku untuk cabang manapun
    yang sedang difilter, terlepas dari jenis monitoring yang dipilih di atas.
</p>

{{-- ------------------------------------------------------------------
     RINGKASAN "Case masuk" & "Case close" — SAMA PERSIS dengan Dashboard
     Master (lihat master-dashboard blade untuk versi aslinya). "Case
     masuk" pakai received_date, "Case close" pakai closed_date (tanggal
     case BENAR-BENAR ditutup, bukan tanggal repair selesai). Satu tab
     di atas mengontrol kedua kartu sekaligus, default = "Hari ini".
     ------------------------------------------------------------------ --}}
@php
    $ledgerFrames     = $ledgerFrames ?? [];
    $ledgerDefaultIdx = count($ledgerFrames) - 1;
    $ledgerDefault    = $ledgerFrames[$ledgerDefaultIdx] ?? null;
@endphp

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
            <h2>Case masuk</h2>
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

{{-- Grafik TAT > 5 hari (case masuk & close) --}}
@include('partials.tat-over5-chart')

@if($isPending)
    <p class="note mb-4">
        Pending case selalu menampilkan kondisi terkini, bukan akumulasi beberapa bulan,
        karena setiap upload baru menggantikan seluruh data lama.
    </p>

    {{-- ------------------------------------------------------------------
         PIE KOMPOSISI PART & QUOTATION — sengaja ditaruh paling atas dari
         semua yang lain di halaman ini, supaya begitu halaman dibuka
         langsung kelihatan seberapa besar beban part vs quotation
         dibanding total case yang menunggu.
         ------------------------------------------------------------------ --}}
    <section class="panel">
        <div class="panel__head">
            <div>
                <h2 class="panel__title">Komposisi part &amp; quotation</h2>
                <p class="panel__desc">
                    Case pending pada 6 status ini, dijumlah dari seluruh CE — Part Request,
                    Part Order Logistic, Part Available, Quotation Request, Quotation Pending,
                    dan Quotation Approved. Angka yang sama dengan baris terkait di tabel di
                    bawah.
                </p>
            </div>
        </div>
        <div class="panel__body">
            @if($statusPieTotal === 0)
                <div class="empty">Tidak ada case pada keenam status ini untuk filter ini.</div>
            @else
                @php $statusPct = fn ($v) => $statusPieTotal > 0 ? round($v / $statusPieTotal * 100) : 0; @endphp
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
                    {{ number_format($statusPieTotal) }} case pada keenam status ini, dari {{ number_format($totalCaseSemua) }} total case.
                </p>
            @endif
        </div>
    </section>
@endif

{{-- ------------------------------------------------------------------
    PIVOT STATUS × CE — sengaja ditaruh paling atas: ini tabel dan
    grafik yang paling sering dicek duluan (siapa pegang case status
    apa), jadi tidak perlu scroll melewati tabel rekap dulu.
    ------------------------------------------------------------------ --}}
<section class="panel">
    <div class="panel__head">
        <div>
            <h2 class="panel__title">{{ $pivotTitle }}</h2>
            <p class="panel__desc">
                Jumlah case per case status untuk tiap CE. Periode {{ $bulanLabel }}.
                @if($isPending) Urutan status mengikuti alur kerja dari New Case sampai Finish Repair. @endif
            </p>
        </div>
        <div class="panel__tools">
            <button type="button" class="btn-link-quiet" data-export="status-per-ce">Unduh CSV</button>
        </div>
    </div>
    <div class="panel__body panel__body--flush">
        <div class="table-wrap is-tall">
            <table class="data">
                <thead>
                    <tr>
                        <th class="stick">Case status</th>
                        @foreach($technicianNames as $name)
                            <th class="num">{{ $name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingPivot as $row)
                        <tr>
                            <td class="name stick">{{ $row['status'] }}</td>
                            @foreach($technicianNames as $name)
                                <td class="num">{{ $row['counts'][$name] > 0 ? $row['counts'][$name] : '' }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($technicianNames) + 1 }}"
                                style="text-align:center; color:var(--muted); padding:26px;">
                                Belum ada data {{ strtolower($labelJenis) }} untuk filter ini.
                            </td>
                        </tr>
                    @endforelse
                    @if(count($pendingPivot) > 0)
                        <tr class="total">
                            <td class="stick">Total</td>
                            @foreach($technicianNames as $name)
                                <td class="num">{{ $pivotTotals[$name] > 0 ? $pivotTotals[$name] : '' }}</td>
                            @endforeach
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- GRAFIK PIVOT --}}
@if(count($pendingPivot))
<section class="panel">
    <div class="panel__head">
        <div>
            <h2 class="panel__title">Komposisi status per CE</h2>
            <p class="panel__desc">
                Batang ditumpuk, jadi tinggi tiap batang adalah total case CE tersebut
                dan tiap warna adalah satu case status.
            </p>
        </div>
    </div>
    <div class="panel__body">
        <div class="chart-box"><canvas id="chartPivot" height="{{ count($pendingPivot) > 4 ? 110 : 90 }}"></canvas></div>
        <p class="chart-note">Klik nama status di legenda untuk menyembunyikan atau memunculkan status itu.</p>
    </div>
</section>
@endif

{{-- ------------------------------------------------------------------
    REKAP PER CE — tiga tabel lama digabung jadi satu. Sebelumnya total
    case, IW/OW, dan IPG/PSG dipisah di tiga kartu, padahal barisnya
    sama, sehingga harus scroll bolak-balik untuk membandingkan.
    ------------------------------------------------------------------ --}}
<section class="panel">
    <div class="panel__head">
        <div>
            <h2 class="panel__title">Rekap case per CE</h2>
            <p class="panel__desc">
                Total case, pembagian warranty, dan product tower dalam satu tabel.
                IW + OW maupun IPG + PSG tidak selalu sama dengan total case, karena ada
                sebagian kecil case dengan status warranty atau product group lain.
            </p>
        </div>
        <div class="panel__tools">
            <button type="button" class="btn-link-quiet" data-export="rekap-per-ce">Unduh CSV</button>
        </div>
    </div>
    <div class="panel__body panel__body--flush">
        <div class="table-wrap is-tall">
            <table class="data">
                <thead>
                    <tr>
                        <th class="stick">CE</th>
                        <th class="num">Total case</th>
                        <th class="num">IW</th>
                        <th class="num">OW</th>
                        <th class="num">IPG</th>
                        <th class="num">PSG</th>
                        <th class="num">Rata-rata TAT (hari)</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summary as $i => $row)
                        @php $w = $wsByName[$row['name']] ?? null; @endphp
                        <tr>
                            <td class="name stick">{{ $row['name'] }}</td>
                            <td class="num strong">{{ $row['total_case'] ?: '' }}</td>
                            <td class="num">{{ $w && $w['warranty'] ? $w['warranty'] : '' }}</td>
                            <td class="num">{{ $w && $w['out_warranty'] ? $w['out_warranty'] : '' }}</td>
                            <td class="num">{{ $w && $w['ipg'] ? $w['ipg'] : '' }}</td>
                            <td class="num">{{ $w && $w['psg'] ? $w['psg'] : '' }}</td>
                            <td class="num">{{ $row['avg_tat'] !== null ? $row['avg_tat'] : '' }}</td>
                            <td>
                                <button type="button" class="btn-row"
                                        data-bs-toggle="modal" data-bs-target="#previewModal{{ $i }}">
                                    Lihat case
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; color:var(--muted); padding:26px;">
                                Belum ada data teknisi untuk filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- GRAFIK BEBAN PER CE --}}
@if(count($summary))
<section class="panel">
    <div class="panel__head">
        <div>
            <h2 class="panel__title">Beban case per CE</h2>
            <p class="panel__desc">Urutan mengikuti tabel di atas.</p>
        </div>
    </div>
    <div class="panel__body">
        <div class="chart-box"><canvas id="chartTotalCase" height="90"></canvas></div>
    </div>
</section>
@endif


{{-- MODAL DETAIL CASE PER CE --}}
@foreach($summary as $i => $row)
    <div class="modal fade" id="previewModal{{ $i }}" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 90vw;">
            <div class="modal-content">

                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">{{ $row['name'] }} — {{ $labelJenis }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Tutup"></button>
                </div>

                <div class="modal-body p-0">
                    <div class="table-wrap" style="max-height:60vh;">
                        <table class="data">
                            <thead>
                                <tr>
                                    <th class="num">No</th>
                                    <th>Case ID</th>
                                    <th>Product name</th>
                                    <th>Case status</th>
                                    <th>Warranty</th>
                                    <th class="num">TAT (hari)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($row['cases'] as $case)
                                    @php
                                        $caseIdValue = $case->case_id ?? $case->case_id_manual ?? null;
                                        /* TAT diambil langsung dari kolom database, bukan dihitung ulang:
                                           pending pakai tat_open_case, close pakai tat_case. */
                                        $tatValue = $isPending ? $case->tat_open_case : $case->tat_case;
                                    @endphp
                                    <tr>
                                        <td class="num">{{ $loop->iteration }}</td>
                                        <td>
                                            @if($caseIdValue)
                                                <a href="https://myscs.co.id/app/isd/view_case.php?q={{ $caseIdValue }}"
                                                   target="_blank" rel="noopener">{{ $caseIdValue }}</a>
                                            @else
                                                <span style="color:var(--muted);">–</span>
                                            @endif
                                        </td>
                                        <td>{{ $case->product_name ?? '–' }}</td>
                                        <td>{{ $case->case_status ?? '–' }}</td>
                                        <td>{{ $case->warranty_status ?? '–' }}</td>
                                        <td class="num">{{ $tatValue !== null ? $tatValue : '' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align:center; color:var(--muted); padding:26px;">
                                            Tidak ada case pada filter ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endforeach

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return;          // CDN gagal dimuat, halaman tetap jalan
    Chart.register(ChartDataLabels);

    Chart.defaults.font.family = "'IBM Plex Sans', system-ui, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#33475A';

    var grid = { color: '#E1E8EF' };

    // ----- Kartu "Case masuk" & "Case close" (4 hari terakhir) -----
    // Sama persis dengan Dashboard Master: bukan Chart.js, cuma ganti
    // textContent elemen terkait pakai data $ledgerFrames dari controller.
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

    // Grafik 0: ring komposisi part & quotation — 5 lingkaran terpisah,
    // masing-masing menyorot satu status terhadap total keenam status.
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

    @foreach($statusPieColors as $label => $color)
        makeRing('ring-{{ \Illuminate\Support\Str::slug($label) }}', {{ $statusPieData[$label] ?? 0 }}, {{ $statusPieTotal }}, '{{ $color }}');
    @endforeach

    // Grafik 1: beban case per CE
    var elTotal = document.getElementById('chartTotalCase');
    if (elTotal) {
        new Chart(elTotal, {
            type: 'bar',
            data: {
                labels: @json(collect($summary)->pluck('name')),
                datasets: [{
                    label: 'Total case',
                    data: @json(collect($summary)->pluck('total_case')),
                    backgroundColor: '#0B6FB8',
                    borderRadius: 4,
                    maxBarThickness: 54
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        anchor: 'end', align: 'top',
                        font: { weight: '600' }, color: '#10222F',
                        formatter: function (v) { return v > 0 ? v : ''; }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grace: '12%', grid: grid, border: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Grafik 2: komposisi status per CE (batang ditumpuk)
    var elPivot = document.getElementById('chartPivot');
    if (elPivot) {
        var palet = ['#0B6FB8', '#C4881F', '#B03A2E', '#237A5C', '#6B5B95',
                     '#2E86AB', '#8C6D31', '#4F6D7A', '#A0522D', '#5B7285'];

        new Chart(elPivot, {
            type: 'bar',
            data: {
                labels: @json($technicianNames),
                datasets: [
                    @foreach($pendingPivot as $i => $row)
                    {
                        label: @json($row['status']),
                        data: @json(array_values($row['counts'])),
                        backgroundColor: palet[{{ $i }} % palet.length],
                        borderRadius: 2
                    },
                    @endforeach
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14 } },
                    datalabels: {
                        font: { size: 10, weight: '600' }, color: '#fff',
                        formatter: function (v) { return v > 2 ? v : ''; }
                    }
                },
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, beginAtZero: true, grace: '8%', grid: grid, border: { display: false } }
                }
            }
        });
    }
})();
</script>

<script>
/* Tombol "Unduh CSV" mengekspor tabel di panelnya ke file CSV. */
document.querySelectorAll('[data-export]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var panel = btn.closest('.panel');
        var table = panel ? panel.querySelector('table.data') : null;
        if (!table) return;

        var baris = [];
        table.querySelectorAll('tr').forEach(function (tr) {
            var kolom = [];
            tr.querySelectorAll('th, td').forEach(function (sel) {
                kolom.push('"' + sel.textContent.trim().replace(/"/g, '""') + '"');
            });
            baris.push(kolom.join(','));
        });

        var blob = new Blob(['\uFEFF' + baris.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = btn.dataset.export + '-' + new Date().toISOString().slice(0, 10) + '.csv';
        a.click();
        URL.revokeObjectURL(a.href);
    });
});
</script>
@endpush

@endsection