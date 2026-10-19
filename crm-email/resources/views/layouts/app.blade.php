<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title', 'Dashboard') — HPS CRM CS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

{{-- Stylesheet konsol HPS, dimuat SETELAH Bootstrap.
     ?v=filemtime() = cache buster: tiap CSS diedit, browser otomatis
     ambil versi baru, tidak perlu Ctrl+F5. --}}
<link rel="stylesheet" href="{{ asset('css/hps.css') }}?v={{ @filemtime(public_path('css/hps.css')) ?: 1 }}">

@stack('styles')
</head>

<body>

@include('partials.welcome-splash')

{{-- Topbar hanya muncul di layar < 992px (diatur CSS). --}}
<div class="app-topbar">
    <button class="app-topbar__btn" type="button" id="railToggle" aria-label="Buka menu" aria-expanded="false">☰</button>
    <span class="app-topbar__brand">HPS CRM Customer Service</span>
</div>

<div class="app-backdrop" id="railBackdrop"></div>

{{-- CATATAN: sidebar TIDAK memakai komponen offcanvas Bootstrap lagi.
     Bootstrap memaksa background transparan pada .offcanvas-lg di layar
     besar, yang membuat sidebar terlihat pudar. Buka/tutup di layar kecil
     sekarang ditangani script kecil di bawah. --}}
<aside class="app-sidebar" id="appSidebar">
    @include('layouts.partials.sidebar')
</aside>

<main class="app-main">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* Buka/tutup sidebar di layar kecil. */
(function () {
    var tombol   = document.getElementById('railToggle');
    var sidebar  = document.getElementById('appSidebar');
    var backdrop = document.getElementById('railBackdrop');
    if (!tombol || !sidebar) return;

    function setOpen(open) {
        sidebar.classList.toggle('is-open', open);
        if (backdrop) backdrop.classList.toggle('is-open', open);
        tombol.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    tombol.addEventListener('click', function () {
        setOpen(!sidebar.classList.contains('is-open'));
    });
    if (backdrop) backdrop.addEventListener('click', function () { setOpen(false); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') setOpen(false);
    });
})();
</script>

@stack('scripts')

</body>
</html>