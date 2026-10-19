{{-- ====================================================================
     Grafik TAT > 5 hari (Pending Case & Close Case) — v10
     Dipakai di: master-dashboard.blade.php & technician-dashboard.blade.php
     (=> Dashboard Master, CE, Semarang, Cirebon). Ikut filter ?cabang=.
     Data dari App\Support\TatOverFiveTrend (rumus ada di file itu).
     ==================================================================== --}}
@php
    $tat      = \App\Support\TatOverFiveTrend::build(request('cabang') ?: null);
    $tatMin   = $tat['minTat'];
    $tatDays  = $tat['windowDays'];
@endphp

<section class="panel" id="tatCardMasuk" data-case-status="pending">
    <div class="panel__head">
        <div>
            <h2 class="panel__title">Pending Case TAT &gt; {{ $tatMin }} hari ({{ $tatDays }} hari terakhir)</h2>
            <p class="panel__desc">
                Semua case yang belum selesai diperbaiki pada tanggal itu, dipecah IW dan OW, lalu dibagi antara yang
                TAT berjalannya (tanggal tsb dikurangi received date, hari kerja Senin&ndash;Sabtu) lebih dari
                {{ $tatMin }} hari (merah) dan yang {{ $tatMin }} hari atau kurang. Persen dihitung dari total tiap batang;
                angka di atas batang adalah total case.
            </p>
        </div>
    </div>
    <div class="panel__body">
        <div style="position:relative;height:340px;"><canvas id="tatChartMasuk"></canvas></div>
        <p class="note" id="tatNoteMasuk" style="text-align:center;margin:8px 0 0;"></p>
    </div>
</section>

<section class="panel" id="tatCardClose" data-case-status="close">
    <div class="panel__head">
        <div>
            <h2 class="panel__title">Close Case TAT &gt; {{ $tatMin }} hari ({{ $tatDays }} hari terakhir)</h2>
            <p class="panel__desc">
                Semua case yang selesai diperbaiki pada tanggal itu (finish date), baik yang sudah diambil maupun yang masih
                menunggu diambil, dipecah IW dan OW, lalu dibagi antara yang TAT-nya (finish date dikurangi received date,
                hari kerja Senin&ndash;Sabtu) lebih dari {{ $tatMin }} hari (merah) dan yang {{ $tatMin }} hari atau kurang.
                Persen dihitung dari total tiap batang; angka di atas batang adalah total case.
            </p>
        </div>
    </div>
    <div class="panel__body">
        <div style="position:relative;height:340px;"><canvas id="tatChartClose"></canvas></div>
        <p class="note" id="tatNoteClose" style="text-align:center;margin:8px 0 0;"></p>
    </div>
</section>

@push('scripts')
<script src="{{ asset('js/case-status-chart.js') }}"></script>
<script>
/* Chart.js dimuat oleh halaman induk SESUDAH partial ini di-include,
   jadi inisialisasi ditunda sampai DOMContentLoaded. */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined' || typeof CaseStatusChart === 'undefined') return;

    var tat = @json($tat);

    var cfgMasuk = { dateLabels: tat.dateLabels, minTat: tat.minTat, iw: tat.masuk.iw, ow: tat.masuk.ow, status: 'pending' };
    var cfgClose = { dateLabels: tat.dateLabels, minTat: tat.minTat, iw: tat.close.iw, ow: tat.close.ow, status: 'close' };

    CaseStatusChart.tatShare('tatChartMasuk', cfgMasuk);
    CaseStatusChart.tatShare('tatChartClose', cfgClose);

    // Pending = stok (case yang sama muncul di beberapa tanggal) -> ringkasan HANYA tanggal terakhir.
    // Close   = jumlah semua tanggal yang tampil.
    CaseStatusChart.tatNote(document.getElementById('tatNoteMasuk'), cfgMasuk, { latest: true,  noun: 'Pending Case' });
    CaseStatusChart.tatNote(document.getElementById('tatNoteClose'), cfgClose, { latest: false, noun: 'Close Case' });
});
</script>
@endpush