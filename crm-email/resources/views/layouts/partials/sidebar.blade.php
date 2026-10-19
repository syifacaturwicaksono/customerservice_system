{{-- 
    Sidebar dipakai bersama oleh layouts/app.blade.php dan layouts/admin.blade.php.
    Sebelumnya menu ditulis dua kali di dua file — tiap tambah menu harus edit
    dua tempat dan gampang beda. Sekarang cukup edit file ini saja.

    Menu aktif ditandai otomatis lewat helper $railActive() di bawah.
--}}

@php
    /** Tandai menu aktif: cocokkan path, plus query ?cabang= untuk shortcut cabang. */
    $railActive = function (string $path, ?string $cabang = null) {
        if (! request()->is(ltrim($path, '/'))) {
            return '';
        }

        if ($cabang !== null && request('cabang') !== $cabang) {
            return '';
        }

        if ($cabang === null && request()->is('technician-dashboard') && request('cabang')) {
            return '';
        }

        return 'is-active';
    };

    /*
     * ROLE USER
     */

    // Teknisi
    $isTeknisi = auth()->check()
        && strtolower(auth()->user()->role ?? '') === 'teknisi';

    // Customer Service
    $isCS = auth()->check()
        && strtolower(auth()->user()->role ?? '') === 'cs';

    // Master Data
    $isMasterData = request()->is('users*')
        || request()->is('templates*')
        || request()->is('technicians*')
        || request()->is('pics*');
@endphp


<div class="rail-brand">
    <div class="rail-brand__name">
        HPS CRM<br>Customer Service
    </div>

    <div class="rail-brand__meta">
        Versi 10.16
    </div>
</div>


<nav class="rail-nav" aria-label="Menu utama">
    <ul class="list-unstyled mb-0">


        {{-- =========================================================
             PESAN
             Teknisi tidak melihat menu Pesan.
             CS tetap boleh melihatnya.
        ========================================================== --}}

        @unless($isTeknisi)
            <li class="rail-nav__caption">Pesan</li>

            <li>
                <a href="/dashboard"
                   class="{{ $railActive('dashboard') }}">
                    Message Dashboard
                </a>
            </li>

            <li>
                <a href="/wa-dashboard"
                   class="{{ $railActive('wa-dashboard') }}">
                    Whatsapp Dashboard
                </a>
            </li>

            <li>
                <a href="/create-email"
                   class="{{ $railActive('create-email') }}">
                    Create Message
                </a>
            </li>
        @endunless


        {{-- =========================================================
             MONITORING
        ========================================================== --}}

        <li class="rail-nav__caption">
            Monitoring
        </li>


        {{-- Dashboard CE
             Teknisi + CS boleh melihat
        --}}
        @if(!$isTeknisi)
            <li>
                <a href="/technician-dashboard"
                   class="{{ $railActive('technician-dashboard') }}">
                    Dashboard CE
                </a>
            </li>
        @endif


        {{-- Dashboard Semarang
             Semua user termasuk Teknisi dan CS
        --}}
        <li>
            <a href="/technician-dashboard?cabang=Semarang"
               class="{{ $railActive('technician-dashboard', 'Semarang') }}">
                Dashboard Semarang
            </a>
        </li>


        {{-- Dashboard Cirebon
             CS boleh melihat
        --}}
        @if(!$isTeknisi || $isCS)
            <li>
                <a href="/technician-dashboard?cabang=Cirebon"
                   class="{{ $railActive('technician-dashboard', 'Cirebon') }}">
                    Dashboard Cirebon
                </a>
            </li>
        @endif


        {{-- =========================================================
             DATA
             Hanya user non-teknisi dan bukan pembatasan CS.
             
             CS TIDAK otomatis mendapat Upload dan Backup.
        ========================================================== --}}

        @unless($isTeknisi || $isCS)

            <li>
                <a href="/dashboard-master"
                   class="{{ $railActive('dashboard-master') }}">
                    Dashboard Master
                </a>
            </li>
     @endunless
      @unless($isTeknisi)
            <li class="rail-nav__caption">
                Data
            </li>

            <li>
                <a href="/upload"
                   class="{{ $railActive('upload') }}">
                    Upload Data
                </a>
            </li>

            <li>
                <a href="/backup-history"
                   class="{{ $railActive('backup-history') }}">
                    Backup History
                </a>
            </li>
        @endunless
   


        {{-- =========================================================
             BC EMAIL
             Sengaja disembunyikan.
             Route & fitur tetap aktif.
        ========================================================== --}}

        {{--
        <li>
            <a href="/bc-email"
               class="{{ $railActive('bc-email') }}">
                BC Email
            </a>
        </li>
        --}}


        {{-- =========================================================
             ADMINISTRASI
             Hanya ADMIN
        ========================================================== --}}

        @if(
            auth()->check()
            && strtolower(auth()->user()->role ?? '') === 'admin'
        )

            <li class="rail-nav__caption">
                Administrasi
            </li>

            <li>

                <details {{ $isMasterData ? 'open' : '' }}>

                    <summary class="rail-nav__summary">
                        Master Data
                    </summary>

                    <ul class="rail-sub">

                        <li>
                            <a href="/users"
                               class="{{ $railActive('users*') }}">
                                Kelola Akun
                            </a>
                        </li>

                        <li>
                            <a href="/templates"
                               class="{{ $railActive('templates*') }}">
                                Template
                            </a>
                        </li>

                        <li>
                            <a href="/technicians"
                               class="{{ $railActive('technicians*') }}">
                                Teknisi
                            </a>
                        </li>

                        <li>
                            <a href="/pics"
                               class="{{ $railActive('pics*') }}">
                                PIC
                            </a>
                        </li>

                    </ul>

                </details>

            </li>

        @endif

    </ul>
</nav>


{{-- =========================================================
     FOOTER USER
========================================================== --}}

<div class="rail-foot">

    @auth

        <div class="rail-foot__user">

            Masuk sebagai

            @if($isTeknisi)
                {{ auth()->user()->username }}
            @elseif($isCS)
                {{ auth()->user()->name ?? auth()->user()->email }}
            @else
                {{ auth()->user()->name ?? auth()->user()->email }}
            @endif

        </div>

    @endauth


    <form method="POST" action="{{ route('logout') }}">

        @csrf

        <button type="submit">
            Keluar
        </button>

    </form>

</div>


{{-- Style submenu <details> ada di public/css/hps.css (bagian sidebar). --}}