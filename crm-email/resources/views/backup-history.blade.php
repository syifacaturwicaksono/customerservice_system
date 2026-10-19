@extends('layouts.admin')

@section('title', 'Backup History')

@section('content')

<header class="page-head">
    <div>
        <h1 class="page-title">Backup History</h1>
        <p class="page-sub">
            File backup dibuat otomatis setiap hari untuk email_logs, wa_logs, pending_case,
            dan close_case. Backup yang lebih lama dari 30 hari dihapus sendiri oleh sistem.
        </p>
    </div>
</header>

{{-- Filter cabang, auto-submit begitu dropdown diganti. pending_case dan
     close_case dibackup per cabang, jadi filter ini menyaring nama filenya.
     email_logs & wa_logs tidak per cabang, jadi selalu tampil. --}}
<form method="GET" action="/backup-history" class="toolbar">
    <div class="field">
        <label for="f-cabang">Cabang</label>
        <select name="cabang" id="f-cabang" onchange="this.form.submit()">
            <option value="" {{ ! $cabang ? 'selected' : '' }}>Semua cabang</option>
            <option value="Semarang" {{ $cabang == 'Semarang' ? 'selected' : '' }}>Semarang</option>
            <option value="Cirebon" {{ $cabang == 'Cirebon' ? 'selected' : '' }}>Cirebon</option>
        </select>
    </div>
</form>

<div class="context-bar">
    <span class="chip"><span class="chip__dot"></span>{{ $cabang ?: 'Semua cabang' }}</span>
</div>

@forelse($folders as $folder)
    <section class="panel">
        <div class="panel__head">
            <div>
                <h2 class="panel__title">{{ $folder['date'] }}</h2>
                <p class="panel__desc">{{ $folder['files']->count() }} file</p>
            </div>
        </div>
        <div class="panel__body {{ $folder['files']->isEmpty() ? '' : 'panel__body--flush' }}">
            @if($folder['files']->isEmpty())
                <div class="empty">Tidak ada file pada tanggal ini.</div>
            @else
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th>Nama file</th>
                                <th class="num">Ukuran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($folder['files'] as $file)
                                <tr>
                                    <td class="name">{{ $file['name'] }}</td>
                                    <td class="num">{{ number_format($file['size'] / 1024, 1) }} KB</td>
                                    <td>
                                        <a href="/backup-history/download?path={{ urlencode($file['path']) }}"
                                           class="btn-row">Unduh CSV</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
@empty
    <section class="panel">
        <div class="panel__body">
            <div class="empty">
                Belum ada backup tersimpan. Backup otomatis akan muncul di sini setelah dijadwalkan —
                lihat CARA_AKTIFKAN_BACKUP_OTOMATIS.txt untuk cara mengaktifkannya.
            </div>
        </div>
    </section>
@endforelse

@endsection