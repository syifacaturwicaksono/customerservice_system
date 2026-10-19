<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>HP CRM CS Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>

*{ box-sizing:border-box; }

body{
    background:#F4F7FA;
    min-height:100vh;
    margin:0;
    font-family:'IBM Plex Sans', 'Segoe UI', Arial, sans-serif;
    color:#10222F;
}

/* ---------- top bar ---------- */

.topbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:16px 28px;
    background:#fff;
    border-bottom:1px solid #E1E8EF;
}

.brand{
    display:flex;
    align-items:center;
    gap:10px;
}
.brand img{ width:34px; }
.brand span{
    font-weight:600;
    font-size:15px;
    color:#10222F;
}

.login-corner{
    position:relative;
}

.btn-login-trigger{
    background:#0096d6;
    color:#fff;
    border:none;
    font-weight:600;
    font-size:14px;
    padding:9px 22px;
    border-radius:999px;
    cursor:pointer;
    transition:background .15s ease;
}
.btn-login-trigger:hover{ background:#007bb5; }

.login-panel{
    position:absolute;
    top:calc(100% + 10px);
    right:0;
    width:300px;
    background:#fff;
    border-radius:14px;
    box-shadow:0 18px 40px rgba(16,34,47,.18);
    padding:20px 20px 16px;
    z-index:50;

    opacity:0;
    transform:translateY(-6px);
    pointer-events:none;
    transition:opacity .15s ease, transform .15s ease;
}
.login-panel.is-open{
    opacity:1;
    transform:translateY(0);
    pointer-events:auto;
}

.login-panel h6{
    font-size:14px;
    font-weight:600;
    margin-bottom:14px;
}

.login-panel label{
    font-size:12.5px;
    color:#5B7285;
    margin-bottom:4px;
    display:block;
}

.login-panel .form-control{
    font-size:14px;
}

.pw-wrap{ position:relative; }
.pw-toggle{
    position:absolute;
    right:10px;
    top:50%;
    transform:translateY(-50%);
    background:none;
    border:none;
    padding:4px;
    line-height:1;
    color:#8a97a3;
    cursor:pointer;
}
.pw-toggle:hover{ color:#0096d6; }
.login-panel .pw-wrap .form-control{ padding-right:38px; }

.btn-hp{
    background:#0096d6;
    border:none;
    font-weight:600;
    letter-spacing:.01em;
}
.btn-hp:hover{ background:#007bb5; }

.login-panel .footer{
    font-size:11px;
    color:#8a97a3;
    text-align:center;
    margin-top:12px;
}

/* ---------- konten utama: tren Case Masuk / Case Close / Part Ready ---------- */

.hero{
    max-width:1080px;
    margin:0 auto;
    padding:48px 24px 64px;
}

.hero__head{
    text-align:center;
    margin-bottom:32px;
}
.hero__head h1{
    font-size:26px;
    font-weight:700;
    margin-bottom:6px;
}
.hero__head p{
    color:#5B7285;
    font-size:14.5px;
    margin:0;
}

.chart-card{
    background:#fff;
    border-radius:16px;
    box-shadow:0 4px 18px rgba(16,34,47,.06);
    padding:28px 24px 20px;
    margin-bottom:24px;
}

.chart-card__head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:8px;
    flex-wrap:wrap;
    gap:10px;
}
.chart-card__head h2{
    font-size:15px;
    font-weight:600;
    margin:0;
}
.chart-card__sub{
    font-size:12.5px;
    color:#8a97a3;
    margin:2px 0 18px;
}

.btn-download{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:#fff;
    color:#0B6FB8;
    border:1px solid #0B6FB8;
    font-size:12.5px;
    font-weight:600;
    padding:6px 14px;
    border-radius:999px;
    cursor:pointer;
    transition:background .15s ease, color .15s ease;
}
.btn-download:hover{ background:#0B6FB8; color:#fff; }
.btn-download:disabled{ opacity:.6; cursor:default; }
.btn-download svg{ width:14px; height:14px; }

.chart-box{ position:relative; height:320px; }

.chart-note{
    font-size:12.5px;
    color:#8a97a3;
    margin-top:14px;
    text-align:center;
}

.empty-note{
    text-align:center;
    color:#8a97a3;
    font-size:14px;
    padding:40px 0;
}

/* =====================================================================
   EFEK LOGIN & TRANSISI MASUK (tambahan)
   ===================================================================== */

/* --- background blob melayang (halus) --- */
.bg-blobs{ position:fixed; inset:0; z-index:-1; overflow:hidden; pointer-events:none; }
.bg-blobs span{
    position:absolute; border-radius:50%; filter:blur(70px); opacity:.55;
    animation:blobFloat 16s ease-in-out infinite;
}
.bg-blobs span:nth-child(1){ width:420px; height:420px; background:#bfe6f7; top:8%;  left:-90px;  }
.bg-blobs span:nth-child(2){ width:360px; height:360px; background:#d9e6fb; top:38%; right:-80px; animation-delay:-5s;  animation-duration:19s; }
.bg-blobs span:nth-child(3){ width:300px; height:300px; background:#c9f0e4; bottom:-60px; left:35%; animation-delay:-9s; animation-duration:22s; }
@keyframes blobFloat{
    0%,100%{ transform:translate(0,0) scale(1); }
    33%    { transform:translate(40px,-30px) scale(1.08); }
    66%    { transform:translate(-30px,30px) scale(.94); }
}

/* --- topbar & logo masuk dari atas --- */
.topbar{ animation:slideDown .6s cubic-bezier(.22,1,.36,1) both; position:relative; z-index:60; }
@keyframes slideDown{ from{ transform:translateY(-100%); opacity:0; } to{ transform:none; opacity:1; } }
.brand img{ animation:logoIn .9s cubic-bezier(.34,1.56,.64,1) .25s both; }
@keyframes logoIn{ from{ transform:scale(.4) rotate(-25deg); opacity:0; } to{ transform:none; opacity:1; } }

/* --- tombol Login: denyut halus sampai pertama kali diklik --- */
.btn-login-trigger{
    transition:background .15s ease, transform .15s ease, box-shadow .2s ease;
    animation:pulseRing 2.2s ease-out 1.2s infinite;
}
.btn-login-trigger:hover{ transform:translateY(-1px); box-shadow:0 6px 16px rgba(0,150,214,.35); }
.btn-login-trigger:active{ transform:scale(.96); }
.btn-login-trigger.is-seen{ animation:none; }
@keyframes pulseRing{
    0%  { box-shadow:0 0 0 0 rgba(0,150,214,.55); }
    70% { box-shadow:0 0 0 14px rgba(0,150,214,0); }
    100%{ box-shadow:0 0 0 0 rgba(0,150,214,0); }
}

/* --- panel login: muncul dengan efek pegas --- */
.login-panel{
    transform-origin:top right;
    transform:translateY(-12px) scale(.94);
    transition:opacity .25s ease, transform .45s cubic-bezier(.34,1.56,.64,1);
}
.login-panel.is-open{ transform:none; }

/* isi panel muncul berurutan */
.login-panel .stagger{
    opacity:0; transform:translateY(10px);
    transition:opacity .35s ease, transform .35s ease;
}
.login-panel.is-open .stagger{
    opacity:1; transform:none;
    transition-delay:calc(var(--i, 0) * 70ms + 120ms);
}

/* getar kalau login gagal */
.login-panel.shake{ animation:panelShake .5s cubic-bezier(.36,.07,.19,.97) .15s both; }
@keyframes panelShake{
    10%,90%{ transform:translateX(-2px); }
    20%,80%{ transform:translateX(4px); }
    30%,50%,70%{ transform:translateX(-7px); }
    40%,60%{ transform:translateX(7px); }
}

/* input: glow saat fokus */
.login-panel .form-control{ transition:border-color .2s ease, box-shadow .2s ease; }
.login-panel .form-control:focus{
    border-color:#0096d6;
    box-shadow:0 0 0 4px rgba(0,150,214,.16);
}

/* tombol submit: kilau, ripple, spinner */
.btn-hp{ position:relative; overflow:hidden; transition:background .15s ease, transform .15s ease, box-shadow .2s ease; }
.btn-hp:hover{ transform:translateY(-1px); box-shadow:0 8px 18px rgba(0,150,214,.35); }
.btn-hp:active{ transform:scale(.98); }
.btn-hp::after{
    content:''; position:absolute; top:0; left:-120%; width:60%; height:100%;
    background:linear-gradient(120deg, transparent, rgba(255,255,255,.35), transparent);
    transform:skewX(-20deg); transition:left .6s ease;
}
.btn-hp:hover::after{ left:140%; }
.btn-hp .ripple{
    position:absolute; border-radius:50%; background:rgba(255,255,255,.5);
    transform:scale(0); animation:rippleOut .6s ease-out forwards; pointer-events:none;
}
@keyframes rippleOut{ to{ transform:scale(2.6); opacity:0; } }
.btn-hp .btn-spinner{
    position:absolute; top:50%; left:50%; width:16px; height:16px; margin:-8px 0 0 -8px;
    border:2px solid rgba(255,255,255,.45); border-top-color:#fff; border-radius:50%;
    opacity:0; animation:spin .7s linear infinite;
}
.btn-hp .btn-label{ transition:opacity .15s ease; }
.btn-hp.is-loading{ pointer-events:none; }
.btn-hp.is-loading .btn-label{ opacity:0; }
.btn-hp.is-loading .btn-spinner{ opacity:1; }
@keyframes spin{ to{ transform:rotate(360deg); } }

/* --- konten muncul saat di-scroll --- */
.reveal{
    opacity:0; transform:translateY(28px);
    transition:opacity .7s ease, transform .7s cubic-bezier(.22,1,.36,1), box-shadow .25s ease;
}
.reveal.is-visible{ opacity:1; transform:none; }
.chart-card:hover{ box-shadow:0 10px 28px rgba(16,34,47,.11); }

/* --- overlay "memverifikasi..." saat tombol Login ditekan --- */
.auth-overlay{
    position:fixed; inset:0; z-index:9999;
    display:flex; flex-direction:column; align-items:center; justify-content:center; gap:14px;
    background:radial-gradient(circle at 50% 40%, rgba(255,255,255,.97), rgba(226,238,248,.98));
    backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px);
    opacity:0; pointer-events:none; transition:opacity .35s ease;
}
.auth-overlay.is-on{ opacity:1; pointer-events:auto; }
.auth-logo{ position:relative; width:96px; height:96px; display:grid; place-items:center; }
.auth-logo img{ width:58px; animation:logoBreath 1.6s ease-in-out infinite; }
.auth-logo::before{
    content:''; position:absolute; inset:0; border-radius:50%;
    border:3px solid rgba(0,150,214,.16); border-top-color:#0096d6;
    animation:spin .9s linear infinite;
}
@keyframes logoBreath{ 0%,100%{ transform:scale(1); } 50%{ transform:scale(1.12); } }
.auth-title{ font-weight:600; font-size:16px; color:#10222F; margin:0; }
.auth-sub{ font-size:13px; color:#5B7285; margin:0; }
.auth-dots span{ display:inline-block; animation:dotBlink 1.2s infinite both; }
.auth-dots span:nth-child(2){ animation-delay:.2s; }
.auth-dots span:nth-child(3){ animation-delay:.4s; }
@keyframes dotBlink{ 0%,80%,100%{ opacity:.2; } 40%{ opacity:1; } }

/* halaman mengecil & memudar saat keluar */
main.hero, .topbar{ transition:transform .45s ease, opacity .45s ease, filter .45s ease; }
body.is-leaving main.hero{ transform:scale(.97); opacity:.35; filter:blur(3px); }

/* hormati pengguna yang mematikan animasi di sistemnya */
@media (prefers-reduced-motion: reduce){
    *, *::before, *::after{ animation-duration:.01ms !important; animation-iteration-count:1 !important; transition-duration:.01ms !important; transition-delay:0ms !important; }
    .reveal{ opacity:1; transform:none; }
}

</style>

</head>

<body>

<div class="bg-blobs" aria-hidden="true"><span></span><span></span><span></span></div>

{{-- overlay saat tombol Login ditekan --}}
<div class="auth-overlay" id="authOverlay" aria-hidden="true">
    <div class="auth-logo">
        <img src="https://upload.wikimedia.org/wikipedia/commons/a/ad/HP_logo_2012.svg" alt="">
    </div>
    <p class="auth-title">Memverifikasi akun<span class="auth-dots"><span>.</span><span>.</span><span>.</span></span></p>
    <p class="auth-sub">Menyiapkan dashboard Anda</p>
</div>

<header class="topbar">
    <div class="brand">
        <img src="https://upload.wikimedia.org/wikipedia/commons/a/ad/HP_logo_2012.svg" alt="HP">
        <span>HP Service Center Semarang</span>
    </div>

    <div class="login-corner">
        <button type="button" class="btn-login-trigger" id="loginTrigger">Login</button>

        <div class="login-panel{{ session('error') ? ' shake' : '' }}" id="loginPanel">
            <h6 class="stagger" style="--i:0">Masuk ke Customer Service</h6>

            @if(session('error'))
            <div class="alert alert-danger py-2" style="font-size:13px;">
            {{ session('error') }}
            </div>
            @endif

            <form method="POST" action="/login" id="loginForm" autocomplete="on">
            @csrf

            <div class="mb-2 stagger" style="--i:1">
                <label>Username</label>
                <input type="text" name="username" class="form-control form-control-sm" placeholder="Masukkan username" required>
            </div>

            <div class="mb-3 stagger" style="--i:2">
                <label>Password</label>
                <div class="pw-wrap">
                    <input type="password" name="password" id="pw" class="form-control form-control-sm" placeholder="Masukkan password" required>
                    <button type="button" class="pw-toggle" id="pwToggle" aria-label="Lihat password" tabindex="-1">
                        <svg id="pwIconShow" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg id="pwIconHide" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a21.3 21.3 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 7 11 7a21.4 21.4 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/></svg>
                    </button>
                </div>
            </div>

            <button class="btn btn-hp btn-sm w-100 text-white stagger" style="--i:3" id="loginSubmit">
            <span class="btn-label">Login</span>
            <span class="btn-spinner"></span>
            </button>

            </form>

            <div class="footer stagger" style="--i:4">
            HP Customer Service System &copy;2026
            </div>
        </div>
    </div>
</header>

<main class="hero">

    <div class="hero__head">
        <h1>Tren Pending Case, Close Case &amp; Part Ready</h1>
        <p>Total gabungan seluruh teknisi, 3 hari kerja terakhir termasuk hari ini.</p>
    </div>

    @php
        $hasTrendData = !empty($trendDateLabels);
    @endphp

    @if(!$hasTrendData)
        <p class="empty-note">Belum ada data untuk ditampilkan pada 3 hari kerja terakhir.</p>
    @endif

    {{-- Card 1: Pending Case --}}
    <div class="chart-card" id="chartCardMasuk" data-case-status="pending">
        <div class="chart-card__head">
            <h2>Pending Case</h2>
            <button type="button" class="btn-download" data-export-target="chartCardMasuk" data-filename="case-masuk">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Unduh JPG
            </button>
        </div>
        <p class="chart-card__sub">Case diterima pada tanggal tsb (received date) — gabungan pending + yang sudah close, dipecah IW/OW</p>
        <div class="chart-box"><canvas id="chartTrendMasuk"></canvas></div>
        <p class="chart-note" id="noteTrendMasuk"></p>
    </div>

    {{-- Card 2: Close Case --}}
    <div class="chart-card" id="chartCardClose" data-case-status="close">
        <div class="chart-card__head">
            <h2>Close Case</h2>
            <button type="button" class="btn-download" data-export-target="chartCardClose" data-filename="case-close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Unduh JPG
            </button>
        </div>
        <p class="chart-card__sub">Case ditutup pada tanggal tsb (closed date, status Close Repair/Close Cancel), dipecah IW/OW</p>
        <div class="chart-box"><canvas id="chartTrendClose"></canvas></div>
        <p class="chart-note" id="noteTrendClose"></p>
    </div>

    {{-- Card 3: Part Ready --}}
    <div class="chart-card" id="chartCardPartReady" data-case-status="partready">
        <div class="chart-card__head">
            <h2>Part Ready</h2>
            <button type="button" class="btn-download" data-export-target="chartCardPartReady" data-filename="part-ready">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Unduh JPG
            </button>
        </div>
        <p class="chart-card__sub">Case yang part-nya ready pada tanggal tsb — tidak dipecah IW/OW (data warranty tidak tersimpan di tabel part)</p>
        <div class="chart-box"><canvas id="chartTrendPartReady"></canvas></div>
        <p class="chart-note" id="noteTrendPartReady"></p>
    </div>

    {{-- ============================================================
         GRAFIK Pending Case & Close Case (TAT > 5 HARI) (v10).
         4 tanggal (hari ini + 3 hari sebelumnya, hari Minggu dilompati).
         Tiap tanggal = batang IW & OW; tiap batang dipecah TAT > 5 hari
         (merah) vs <= 5 hari, lengkap dengan persen dari total batang.
         TAT = HARI KERJA Senin-Sabtu, berhenti di finish_date.
         Pending = case yang belum selesai diperbaiki pada tanggal tsb
         (TAT berjalan: received -> tanggal tsb); Close = case yang selesai
         pada tanggal tsb (TAT akhir: received -> finish_date).
         Data: App\\Support\\TatOverFiveTrend  ->  $trendTat (AuthController).
         Grafik digambar oleh CaseStatusChart.tatShare() di case-status-chart.js.
    ============================================================ --}}

    <div class="hero__head" style="margin-top:8px;">
        <h1 style="font-size:20px;">Pending Case &amp; Close Case: Porsi TAT &gt; 5 Hari</h1>
        <p>Tiap tanggal, case dipecah IW dan OW, lalu dibagi yang TAT-nya lebih dari 5 hari kerja (merah) dan yang 5 hari atau kurang. 4 hari terakhir termasuk hari ini; hari Minggu tidak ditampilkan.</p>
    </div>

    {{-- Card 4: Pending Case (TAT > 5 hari) --}}
    <div class="chart-card" id="chartCardMasukTat" data-case-status="pending">
        <div class="chart-card__head">
            <h2>Pending Case (TAT &gt; 5 Hari)</h2>
            <button type="button" class="btn-download" data-export-target="chartCardMasukTat" data-filename="case-masuk-tat-lebih-5-hari">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Unduh JPG
            </button>
        </div>
        <p class="chart-card__sub">Semua case yang belum selesai diperbaiki pada tanggal tsb, dipecah IW/OW, lalu dibagi yang TAT berjalannya (tanggal tsb dikurangi received date, hari kerja Senin-Sabtu) lebih dari 5 hari (merah) dan yang 5 hari atau kurang. Persen dihitung dari total tiap batang</p>
        <div class="chart-box"><canvas id="chartTrendMasukTat"></canvas></div>
        <p class="chart-note" id="noteTrendMasukTat"></p>
    </div>

    {{-- Card 5: Close Case (TAT > 5 hari) --}}
    <div class="chart-card" id="chartCardCloseTat" data-case-status="close">
        <div class="chart-card__head">
            <h2>Close Case (TAT &gt; 5 Hari)</h2>
            <button type="button" class="btn-download" data-export-target="chartCardCloseTat" data-filename="case-close-tat-lebih-5-hari">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Unduh JPG
            </button>
        </div>
        <p class="chart-card__sub">Semua case yang selesai diperbaiki pada tanggal tsb (finish date), dipecah IW/OW, lalu dibagi yang TAT-nya (finish date dikurangi received date, hari kerja Senin-Sabtu) lebih dari 5 hari (merah) dan yang 5 hari atau kurang. Persen dihitung dari total tiap batang</p>
        <div class="chart-box"><canvas id="chartTrendCloseTat"></canvas></div>
        <p class="chart-note" id="noteTrendCloseTat"></p>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="{{ asset('js/case-status-chart.js') }}"></script>
<script>
(function () {
    'use strict';

    // ----- toggle panel login di pojok kanan atas -----
    var trigger = document.getElementById('loginTrigger');
    var panel   = document.getElementById('loginPanel');

    if (trigger && panel) {
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            panel.classList.toggle('is-open');
        });

        document.addEventListener('click', function (e) {
            if (!panel.contains(e.target) && e.target !== trigger) {
                panel.classList.remove('is-open');
            }
        });

        @if(session('error'))
            panel.classList.add('is-open');
        @endif
    }

    // ----- efek login: ripple, loading, overlay, flag welcome -----
    (function () {
        var form      = document.getElementById('loginForm');
        var submitBtn = document.getElementById('loginSubmit');
        var overlay   = document.getElementById('authOverlay');

        // bersihkan sisa flag (mis. login sebelumnya gagal)
        try {
            sessionStorage.removeItem('hpWelcome');
            sessionStorage.removeItem('hpWelcomeUser');
        } catch (e) {}

        if (trigger) {
            trigger.addEventListener('click', function () { trigger.classList.add('is-seen'); });
        }

        // ripple di tombol submit
        if (submitBtn) {
            submitBtn.addEventListener('click', function (e) {
                var rect = submitBtn.getBoundingClientRect();
                var size = Math.max(rect.width, rect.height);
                var r = document.createElement('span');
                r.className = 'ripple';
                r.style.width = r.style.height = size + 'px';
                r.style.left = (e.clientX - rect.left - size / 2) + 'px';
                r.style.top  = (e.clientY - rect.top  - size / 2) + 'px';
                submitBtn.appendChild(r);
                setTimeout(function () { r.remove(); }, 650);
            });
        }

        // saat form dikirim: spinner + overlay + tandai untuk sapaan welcome
        if (form) {
            form.addEventListener('submit', function () {
                var u = form.querySelector('[name="username"]');
                try {
                    sessionStorage.setItem('hpWelcome', String(Date.now()));
                    sessionStorage.setItem('hpWelcomeUser', u ? u.value : '');
                } catch (e) {}

                if (submitBtn) submitBtn.classList.add('is-loading');
                document.body.classList.add('is-leaving');
                if (overlay) overlay.classList.add('is-on');
            });
        }

        // kalau user menekan tombol Back (halaman dari cache), reset tampilan
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) {
                if (submitBtn) submitBtn.classList.remove('is-loading');
                document.body.classList.remove('is-leaving');
                if (overlay) overlay.classList.remove('is-on');
            }
        });

        // kartu & judul muncul halus saat di-scroll
        var revealEls = document.querySelectorAll('.hero__head, .chart-card');
        if ('IntersectionObserver' in window && revealEls.length) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (en) {
                    if (en.isIntersecting) {
                        en.target.classList.add('is-visible');
                        io.unobserve(en.target);
                    }
                });
            }, { threshold: 0.1 });

            revealEls.forEach(function (el, i) {
                el.classList.add('reveal');
                if (i < 3) el.style.transitionDelay = (i * 120 + 300) + 'ms';
            });
            void document.body.offsetWidth; // paksa reflow supaya transisi jalan
            revealEls.forEach(function (el) { io.observe(el); });
        }
    })();

    // ----- toggle lihat/sembunyikan password -----
    var pw = document.getElementById('pw');
    var pwToggle = document.getElementById('pwToggle');
    var iconShow = document.getElementById('pwIconShow');
    var iconHide = document.getElementById('pwIconHide');

    if (pwToggle && pw) {
        pwToggle.addEventListener('click', function () {
            var showing = pw.type === 'text';
            pw.type = showing ? 'password' : 'text';
            iconShow.style.display = showing ? '' : 'none';
            iconHide.style.display = showing ? 'none' : '';
            pw.focus();
        });
    }

    // ----- 3 grafik COMBO (bar ditumpuk IW/OW + garis total). Case Masuk
    // & Case Close punya breakdown IW/OW; Part Ready cuma bar tunggal
    // (tidak ada data warranty di tabel part). Tiap segmen bar dikasih
    // label "jumlah (persentase%)"; titik garis di atas bar dikasih total
    // + indikator naik/turun (▲/▼ persen) dibanding hari sebelumnya yang
    // tampil di grafik. -----
    (function () {
        if (typeof Chart === 'undefined') return;

        var dateLabels = @json($trendDateLabels ?? []);
        var masukIw = @json($trendMasukIw ?? []);
        var masukOw = @json($trendMasukOw ?? []);
        var masukTotal = @json($trendMasukTotal ?? []);
        var closeIw = @json($trendCloseIw ?? []);
        var closeOw = @json($trendCloseOw ?? []);
        var closeTotal = @json($trendCloseTotal ?? []);
        var partReadyTotal = @json($trendPartReady ?? []);

        // ----- data grafik TAT > 5 hari (v10): 4 tanggal, IW & OW, dipecah > 5 hari vs <= 5 hari -----
        var tatData = @json($trendTat ?? null);

        if (!dateLabels.length) return;

        var colorIw   = '#2F6FBA';
        var colorOw   = '#D98C2B';
        var colorFlat = '#C0603D';
        var colorLine = '#10222F';

        // Plugin: label "jumlah (persentase%)" di tengah tiap segmen bar
        // (cuma dipakai kalau ada breakdown IW/OW).
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
                        var meta = chart.getDatasetMeta(datasetIndex);
                        meta.data.forEach(function (el, i) {
                            var v = values[i] || 0;
                            if (!v) return;
                            var total = (iwValues[i] || 0) + (owValues[i] || 0);
                            var pct = total > 0 ? Math.round((v / total) * 100) : 0;
                            var midY = (el.y + el.base) / 2;
                            ctx.fillText(v + ' (' + pct + '%)', el.x, midY + 4);
                        });
                    });
                    ctx.restore();
                }
            };
        }

        // Plugin: total + indikator naik/turun (%) di atas tiap titik
        // garis, dibanding hari SEBELUMNYA yang tampil di grafik ini.
        function totalDeltaPlugin(totalValues, lineDatasetIndex) {
            return {
                id: 'totalDelta',
                afterDatasetsDraw: function (chart) {
                    var meta = chart.getDatasetMeta(lineDatasetIndex);
                    var ctx = chart.ctx;
                    ctx.save();
                    ctx.textAlign = 'center';

                    meta.data.forEach(function (point, i) {
                        var total = totalValues[i] || 0;

                        ctx.font = "700 13px 'IBM Plex Sans', system-ui, sans-serif";
                        ctx.fillStyle = '#10222F';
                        ctx.fillText(String(total), point.x, point.y - 26);

                        if (i > 0) {
                            var prev = totalValues[i - 1] || 0;
                            var deltaText = '';
                            var deltaColor = '#8a97a3';

                            if (prev > 0) {
                                var pct = Math.round(((total - prev) / prev) * 100);
                                if (pct > 0) { deltaText = '\u25B2 ' + pct + '%'; deltaColor = '#1E8A4C'; }
                                else if (pct < 0) { deltaText = '\u25BC ' + Math.abs(pct) + '%'; deltaColor = '#C23B3B'; }
                                else { deltaText = '– 0%'; }
                            } else if (total > 0) {
                                deltaText = 'baru';
                                deltaColor = '#1E8A4C';
                            }

                            if (deltaText) {
                                ctx.font = "700 11px 'IBM Plex Sans', system-ui, sans-serif";
                                ctx.fillStyle = deltaColor;
                                ctx.fillText(deltaText, point.x, point.y - 12);
                            }
                        }
                    });
                    ctx.restore();
                }
            };
        }

        function buildComboChart(canvasId, opts) {
            var el = document.getElementById(canvasId);
            if (!el) return null;

            // grafik baru digambar saat kartunya terlihat di layar,
            // supaya animasi batangnya benar-benar kelihatan
            if (!opts._visible && 'IntersectionObserver' in window) {
                var obs = new IntersectionObserver(function (entries) {
                    if (entries[0].isIntersecting) {
                        obs.disconnect();
                        opts._visible = true;
                        buildComboChart(canvasId, opts);
                    }
                }, { threshold: 0.25 });
                obs.observe(el);
                return null;
            }

            var hasSplit = !!(opts.iwValues && opts.owValues);
            var datasets = [];

            if (hasSplit) {
                datasets.push({
                    type: 'bar', label: 'IW', data: opts.iwValues,
                    backgroundColor: colorIw, stack: 'total', maxBarThickness: 70
                });
                datasets.push({
                    type: 'bar', label: 'OW', data: opts.owValues,
                    backgroundColor: colorOw, stack: 'total', maxBarThickness: 70
                });
            } else {
                datasets.push({
                    type: 'bar', label: 'Total', data: opts.totalValues,
                    backgroundColor: colorFlat, stack: 'total', maxBarThickness: 70
                });
            }

            var lineDatasetIndex = datasets.length;

            datasets.push({
                type: 'line', label: 'Total', data: opts.totalValues,
                borderColor: colorLine, backgroundColor: colorLine,
                tension: 0, borderWidth: 2.5,
                pointRadius: 6, pointHoverRadius: 7,
                pointBackgroundColor: colorLine, pointBorderColor: '#fff', pointBorderWidth: 2,
                fill: false
            });

            var chart = new Chart(el, {
                data: { labels: opts.dateLabels || dateLabels, datasets: datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 1300, easing: 'easeOutQuart' },
                    layout: { padding: { top: 48 } },
                    plugins: {
                        tooltip: opts.status ? CaseStatusChart.tooltip(opts.status) : {},
                        legend: {
                            display: hasSplit,
                            position: 'bottom',
                            labels: { boxWidth: 12, padding: 14, filter: function (item) { return item.text !== 'Total'; } }
                        }
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, beginAtZero: true, grace: '30%', ticks: { precision: 0 }, grid: { color: '#E1E8EF' }, border: { display: false } }
                    }
                },
                plugins: hasSplit
                    ? [segmentLabelPlugin(opts.iwValues, opts.owValues), opts.status ? CaseStatusChart.totalDeltaPlugin(opts.totalValues, lineDatasetIndex, opts.status) : totalDeltaPlugin(opts.totalValues, lineDatasetIndex)]
                    : [totalDeltaPlugin(opts.totalValues, lineDatasetIndex)]
            });

            if (opts.noteEl) {
                var noteLabels = opts.dateLabels || dateLabels;
                if (opts.noteLatest) {
                    // backlog: case yang sama muncul di beberapa tanggal,
                    // jadi TIDAK dijumlah — tampilkan tanggal terakhir saja
                    var li = opts.totalValues.length - 1;
                    CaseStatusChart.setNote(opts.noteEl, opts.totalValues[li] || 0, opts.noteLabel + ' per ' + noteLabels[li], opts.status);
                } else {
                    var total = opts.totalValues.reduce(function (a, b) { return a + b; }, 0);
                    CaseStatusChart.setNote(opts.noteEl, total, opts.noteLabel + ' sepanjang ' + noteLabels.length + ' hari yang tampil', opts.status);
                }
            }

            return chart;
        }

        buildComboChart('chartTrendMasuk', {
            iwValues: masukIw, owValues: masukOw, totalValues: masukTotal,
            noteEl: document.getElementById('noteTrendMasuk'), noteLabel: 'Pending Case', status: 'pending'
        });
        buildComboChart('chartTrendClose', {
            iwValues: closeIw, owValues: closeOw, totalValues: closeTotal,
            noteEl: document.getElementById('noteTrendClose'), noteLabel: 'Close Case', status: 'close'
        });
        buildComboChart('chartTrendPartReady', {
            totalValues: partReadyTotal,
            noteEl: document.getElementById('noteTrendPartReady'), noteLabel: 'case part ready', status: 'partready'
        });

        // ----- 2 grafik: Pending Case & Close Case, TAT > 5 hari (4 tanggal) -----
        if (tatData && tatData.dateLabels && tatData.dateLabels.length) {
            var cfgMasukTat = { dateLabels: tatData.dateLabels, minTat: tatData.minTat, iw: tatData.masuk.iw, ow: tatData.masuk.ow, status: 'pending' };
            var cfgCloseTat = { dateLabels: tatData.dateLabels, minTat: tatData.minTat, iw: tatData.close.iw, ow: tatData.close.ow, status: 'close' };

            CaseStatusChart.tatShare('chartTrendMasukTat', cfgMasukTat);
            CaseStatusChart.tatShare('chartTrendCloseTat', cfgCloseTat);

            // Pending = stok -> ringkasan hanya tanggal terakhir; Close = jumlah semua tanggal
            CaseStatusChart.tatNote(document.getElementById('noteTrendMasukTat'), cfgMasukTat, { latest: true,  noun: 'Pending Case' });
            CaseStatusChart.tatNote(document.getElementById('noteTrendCloseTat'), cfgCloseTat, { latest: false, noun: 'Close Case' });
        }
    })();

    // ----- unduh salah satu kartu grafik sebagai JPG -----
    if (typeof html2canvas !== 'undefined') {
        document.querySelectorAll('.btn-download[data-export-target]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var exportEl = document.getElementById(btn.dataset.exportTarget);
                if (!exportEl) return;

                var originalText = btn.innerHTML;
                btn.style.visibility = 'hidden';
                btn.disabled = true;

                html2canvas(exportEl, {
                    backgroundColor: '#ffffff',
                    scale: 2,
                    useCORS: true
                }).then(function (canvas) {
                    var link = document.createElement('a');
                    var stamp = new Date().toISOString().slice(0, 10);
                    link.download = (btn.dataset.filename || 'chart') + '-' + stamp + '.jpg';
                    link.href = canvas.toDataURL('image/jpeg', 0.95);
                    link.click();
                }).catch(function (err) {
                    console.error('Gagal membuat gambar:', err);
                    alert('Gagal membuat file JPG. Coba lagi.');
                }).finally(function () {
                    btn.style.visibility = 'visible';
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
            });
        });
    }
})();
</script>

</body>
</html>