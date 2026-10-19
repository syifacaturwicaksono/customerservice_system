@extends('layouts.app')

@section('title', 'Upload Data')

@section('content')

<header class="page-head">
    <div>
        <h1 class="page-title">Upload Data</h1>
        <p class="page-sub">Impor file Excel hasil export ISD ke dalam sistem, per cabang dan jenis monitoring.</p>
    </div>
</header>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

{{-- ------------------------------------------------------------------
     FORM UPLOAD
     ------------------------------------------------------------------ --}}
<section class="panel">
    <div class="panel__head">
        <div>
            <h2 class="panel__title">Upload data Excel</h2>
            <p class="panel__desc">File boleh berisi campuran Close Case dan Pending Case — sistem menyaring otomatis.</p>
        </div>
    </div>

    <div class="panel__body">
        <form method="POST" action="/upload/import" enctype="multipart/form-data" id="uploadForm">
            @csrf

            <div class="toolbar mb-3" style="box-shadow:none;">

                <div class="field">
                    <label for="f-jenis">Jenis monitoring</label>
                    <select name="jenis_monitoring" id="f-jenis" required>
                        <option value="pending_case" selected>Pending case — data lama diganti</option>
                        <option value="close_case">Close case — data ditambahkan</option>
                    </select>
                </div>

                <div class="field">
                    <label for="f-cabang">Cabang</label>
                    <select name="cabang" id="f-cabang" required>
                        <option value="">Pilih cabang</option>
                        <option value="Semarang">Semarang</option>
                        <option value="Cirebon">Cirebon</option>
                    </select>
                </div>

                <div class="field">
                    <label for="f-batch">Nama batch</label>
                    <input type="text" name="kode_upload" id="f-batch"
                           placeholder="Contoh: CC0806" autocomplete="off" required>
                </div>

            </div>

            {{-- Dropzone menggantikan input file bawaan browser: nama file yang
                 dipilih langsung terlihat, dan area klik/drop jauh lebih besar
                 daripada tombol "Browse..." kecil. --}}
            <label class="field mb-3" style="min-width:0;">
                <span>File Excel</span>
                <span class="dropzone" id="dropzone">
                    <span class="dropzone__icon" aria-hidden="true">⭳</span>
                    <span class="dropzone__text">
                        <span class="dropzone__title" id="dzTitle">Klik untuk pilih file, atau seret file ke sini</span>
                        <span class="dropzone__file" id="dzFile">Format .xlsx atau .xls</span>
                    </span>
                    <span class="dropzone__browse">Pilih file</span>
                    <input type="file" name="file" id="f-file" accept=".xlsx,.xls" required>
                </span>
            </label>

            <button type="submit" class="btn-main" id="uploadSubmitBtn">Upload &amp; proses</button>

            <p class="callout mt-3 mb-0">
                Kolom Case status pada file menentukan penyaringan Close Case / Pending Case — baris
                dengan jenis lain di file yang sama otomatis diabaikan. Kolom kota tiap baris juga
                dicocokkan dengan Cabang yang dipilih; baris yang kotanya jelas beda akan dilewati
                supaya tidak tercampur antar cabang.
                <br><br>
                <strong>Close case:</strong> data lama tidak pernah dihapus, tiap upload menambah baris baru.
                <strong>Pending case:</strong> tiap upload baru menggantikan data pending case lama untuk
                cabang yang sama saja — cabang lain tidak ikut terpengaruh.
            </p>

        </form>
    </div>
</section>

{{-- ------------------------------------------------------------------
     RIWAYAT UPLOAD
     ------------------------------------------------------------------ --}}
<section class="panel">
    <div class="panel__head">
        <div>
            <h2 class="panel__title">Riwayat upload batch</h2>
        </div>
    </div>
    <div class="panel__body panel__body--flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Cabang</th>
                        <th>Batch</th>
                        <th>File</th>
                        <th class="num">Total baris</th>
                        <th>Tanggal upload</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($uploadHistory as $h)
                        <tr>
                            <td>
                                <span class="tag {{ $h->jenis_upload == 'pending_case' ? 'tag--pending' : 'tag--close' }}">
                                    {{ $h->jenis_upload == 'pending_case' ? 'Pending case' : 'Close case' }}
                                </span>
                            </td>
                            <td>
                                @if($h->cabang)
                                    <span class="tag tag--branch">{{ $h->cabang }}</span>
                                @else
                                    <span style="color:var(--muted);">–</span>
                                @endif
                            </td>
                            <td><span class="tag tag--batch">{{ $h->kode_upload }}</span></td>
                            <td class="name">{{ $h->file_name }}</td>
                            <td class="num">{{ number_format($h->total_row) }}</td>
                            <td>{{ \Carbon\Carbon::parse($h->created_at)->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; color:var(--muted); padding:26px;">
                                Belum ada riwayat upload.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel__body">
        {{ $uploadHistory->links('pagination::bootstrap-5') }}
    </div>
</section>

{{-- OVERLAY SAAT UPLOAD DIPROSES --}}
<div class="busy-overlay" id="uploadLoadingOverlay">
    <div class="busy-spinner"></div>
    <div class="busy-overlay__title">Sedang memproses file…</div>
    <div class="busy-overlay__note">Mohon tunggu, jangan tutup atau muat ulang halaman ini.</div>
</div>

@push('scripts')
<script>
(function () {
    var form      = document.getElementById('uploadForm');
    var fileInput = document.getElementById('f-file');
    var dropzone  = document.getElementById('dropzone');
    var dzTitle   = document.getElementById('dzTitle');
    var dzFile    = document.getElementById('dzFile');

    function showFileName(file) {
        if (!file) return;
        dzTitle.textContent = file.name;
        dzFile.textContent = (file.size / 1024).toFixed(0) + ' KB — klik untuk ganti file';
    }

    fileInput.addEventListener('change', function () {
        if (this.files[0]) showFileName(this.files[0]);
    });

    // Drag & drop di atas dropzone
    ['dragover', 'dragleave', 'drop'].forEach(function (evt) {
        dropzone.addEventListener(evt, function (e) {
            e.preventDefault();
            dropzone.classList.toggle('is-dragover', evt === 'dragover');
        });
    });
    dropzone.addEventListener('drop', function (e) {
        var f = e.dataTransfer.files;
        if (f.length) {
            fileInput.files = f;
            showFileName(f[0]);
        }
    });

    form.addEventListener('submit', function (e) {
        var jenis  = form.querySelector('[name="jenis_monitoring"]').value;
        var cabang = form.querySelector('[name="cabang"]').value;

        if (jenis === 'pending_case') {
            var namaCabang = cabang || '(cabang belum dipilih)';
            var ok = confirm(
                'Yakin? Kalau pilih Pending Case, data pending case lama untuk cabang ' + namaCabang +
                ' akan DIGANTI data baru ini. Data cabang lain tidak terpengaruh.'
            );
            if (!ok) { e.preventDefault(); return; }
        }

        document.getElementById('uploadLoadingOverlay').classList.add('is-active');

        var btn = document.getElementById('uploadSubmitBtn');
        btn.disabled = true;
        btn.textContent = 'Memproses…';
    });
})();
</script>
@endpush

@endsection