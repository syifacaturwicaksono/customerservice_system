{{-- ============================================================
     Sapaan "Selamat datang" setelah login berhasil.
     Pasang SEKALI, tepat setelah tag <body> di layouts/app.blade.php:

         @include('partials.welcome-splash')

     Hanya tampil sekali setelah login (ditandai lewat sessionStorage
     dari halaman login), tidak tampil saat refresh / pindah menu.
============================================================ --}}

<style>
.welcome-splash{
    position:fixed; inset:0; z-index:99999;
    display:none; flex-direction:column; align-items:center; justify-content:center; gap:10px;
    background:radial-gradient(circle at 50% 40%, #ffffff, #dcebf7);
    transition:opacity .6s ease, transform .7s cubic-bezier(.22,1,.36,1), visibility 0s linear .7s;
}
.welcome-splash.is-show{ display:flex; }
.welcome-splash.is-out{ opacity:0; transform:scale(1.06); visibility:hidden; pointer-events:none; }

.welcome-splash__logo{
    width:74px;
    animation:wsPop .9s cubic-bezier(.34,1.56,.64,1) both;
}
.welcome-splash__hello{
    margin:8px 0 0; font-size:15px; color:#5B7285; letter-spacing:.02em;
    animation:wsUp .6s ease .35s both;
}
.welcome-splash__name{
    margin:0; font-size:30px; font-weight:700; color:#10222F;
    animation:wsUp .6s ease .5s both;
}
.welcome-splash__bar{
    width:180px; height:4px; border-radius:99px; background:rgba(0,150,214,.15);
    overflow:hidden; margin-top:14px; animation:wsUp .6s ease .65s both;
}
.welcome-splash__bar i{
    display:block; height:100%; width:0; background:#0096d6; border-radius:99px;
    animation:wsFill 1.5s cubic-bezier(.4,0,.2,1) .7s forwards;
}

@keyframes wsPop { from{ transform:scale(.3) rotate(-30deg); opacity:0; } to{ transform:none; opacity:1; } }
@keyframes wsUp  { from{ transform:translateY(14px); opacity:0; } to{ transform:none; opacity:1; } }
@keyframes wsFill{ to{ width:100%; } }

@media (prefers-reduced-motion: reduce){
    .welcome-splash, .welcome-splash *{ animation-duration:.01ms !important; transition-duration:.01ms !important; }
}
</style>

<div class="welcome-splash" id="welcomeSplash" aria-hidden="true">
    <img class="welcome-splash__logo" id="welcomeLogo"
         data-src="https://upload.wikimedia.org/wikipedia/commons/a/ad/HP_logo_2012.svg" alt="">
    <p class="welcome-splash__hello">Selamat datang kembali,</p>
    <p class="welcome-splash__name" id="welcomeName"></p>
    <div class="welcome-splash__bar"><i></i></div>
</div>

<script>
(function () {
    var splash = document.getElementById('welcomeSplash');
    if (!splash) return;

    var show = false, name = '';
    try {
        // flag = waktu login (ms); hanya berlaku 30 detik supaya sapaan
        // tidak muncul terlambat di halaman lain
        var ts = parseInt(sessionStorage.getItem('hpWelcome'), 10);
        show = !isNaN(ts) && (Date.now() - ts) < 30000;
        name = sessionStorage.getItem('hpWelcomeUser') || '';
        sessionStorage.removeItem('hpWelcome');       // sekali pakai
        sessionStorage.removeItem('hpWelcomeUser');
    } catch (e) {}

    // bukan habis login -> overlay memang sudah tersembunyi; buang saja
    if (!show) { splash.remove(); return; }

    document.getElementById('welcomeName').textContent = name || 'Pengguna';

    var logo = document.getElementById('welcomeLogo');
    if (logo) logo.src = logo.getAttribute('data-src');
    splash.classList.add('is-show');

    setTimeout(function () {
        splash.classList.add('is-out');
        setTimeout(function () { splash.remove(); }, 800);
    }, 2300);
})();
</script>