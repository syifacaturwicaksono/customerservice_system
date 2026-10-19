@extends('layouts.admin')

@section('title', 'WhatsApp Dashboard')

@section('content')

<header class="page-head">
    <div>
        <h1 class="page-title">WhatsApp Dashboard</h1>
        <p class="page-sub">Rekap pesan WhatsApp yang dikirim ke customer.</p>
    </div>
</header>

<form method="GET" action="/wa-dashboard" class="toolbar">

    <div class="field">
        <label for="f-from">Dari tanggal</label>
        <input type="date" name="from" id="f-from" value="{{ request('from') }}">
    </div>

    <div class="field">
        <label for="f-to">Sampai tanggal</label>
        <input type="date" name="to" id="f-to" value="{{ request('to') }}">
    </div>

    <div class="toolbar__actions">
        <button type="submit" class="btn-main">Terapkan filter</button>
        <a href="/wa-dashboard" class="btn-quiet">Atur ulang</a>
        <a href="{{ route('wa.dashboard.export', request()->query()) }}" class="btn-quiet">Unduh Excel</a>
    </div>

</form>

<div class="context-bar">
    @if(request('from') || request('to'))
        <span class="chip"><span class="chip__dot"></span>{{ request('from') ?: 'awal' }} sampai {{ request('to') ?: 'sekarang' }}</span>
    @else
        <span class="chip"><span class="chip__dot"></span>Seluruh periode</span>
    @endif
</div>

<section class="panel">
    <div class="panel__body">
        <div class="metrics" style="grid-template-columns: repeat(1, minmax(0, 260px));">
            <div class="metric">
                <div class="metric__label">WhatsApp terkirim pada periode ini</div>
                <div class="metric__value metric__value--iw">{{ number_format($totalWa) }}</div>
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel__head">
        <div>
            <h2 class="panel__title">Pesan terakhir</h2>
            <p class="panel__desc">Urut dari yang paling baru.</p>
        </div>
    </div>
    <div class="panel__body panel__body--flush">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th class="num">No</th>
                        <th>Case ID</th>
                        <th>Customer</th>
                        <th>PIC</th>
                        <th>Nomor</th>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentWa as $wa)
                        <tr>
                            <td class="num">{{ $recentWa->firstItem() + $loop->index }}</td>
                            <td>{{ $wa->case_id }}</td>
                            <td class="name">{{ $wa->customer_name }}</td>
                            <td>{{ $wa->pic }}</td>
                            <td>{{ $wa->customer_phone }}</td>
                            <td style="white-space:normal; max-width:38ch;">
                                {{ \Illuminate\Support\Str::limit($wa->message, 60) }}
                            </td>
                            <td class="{{ $wa->status == 'sent' ? 'ok' : '' }}"
                                style="{{ $wa->status == 'sent' ? '' : 'color:var(--crit); font-weight:600;' }}">
                                {{ $wa->status == 'sent' ? 'Terkirim' : 'Gagal' }}
                            </td>
                            <td>{{ $wa->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; color:var(--muted); padding:26px;">
                                Belum ada pesan WhatsApp terkirim pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel__body">
        {{ $recentWa->links() }}
    </div>
</section>

@endsection