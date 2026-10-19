@extends('layouts.admin')

@section('title', 'Message Dashboard')

@section('content')

<header class="page-head">
    <div>
        <h1 class="page-title">Message Dashboard</h1>
        <p class="page-sub">Rekap email yang dikirim ke customer.</p>
    </div>
</header>

<form method="GET" action="/dashboard" class="toolbar">

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
        <a href="/dashboard" class="btn-quiet">Atur ulang</a>
        <a href="{{ route('dashboard.export', request()->query()) }}" class="btn-quiet">Unduh Excel</a>
    </div>

</form>

<div class="context-bar">
    @if(request('from') || request('to'))
        <span class="chip"><span class="chip__dot"></span>{{ request('from') ?: 'awal' }} sampai {{ request('to') ?: 'sekarang' }}</span>
    @else
        <span class="chip"><span class="chip__dot"></span>Seluruh periode</span>
    @endif
</div>

<section class="panel ledger">
    <div class="ledger__group">
        <div class="ledger__head"><h2>Email terkirim</h2></div>
        <div class="metrics">
            <div class="metric">
                <div class="metric__label">Total pada periode ini</div>
                <div class="metric__value metric__value--iw">{{ number_format($totalEmails) }}</div>
            </div>
        </div>
    </div>
    <div class="ledger__group">
        <div class="ledger__head"><h2>Jenis pesan</h2></div>
        <div class="metrics">
            <div class="metric">
                <div class="metric__label">Template tersedia</div>
                <div class="metric__value">{{ number_format($totalTemplates) }}</div>
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel__head">
        <div>
            <h2 class="panel__title">Email terakhir</h2>
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
                        <th>PIC</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentEmails as $email)
                        <tr>
                            <td class="num">{{ $recentEmails->firstItem() + $loop->index }}</td>
                            <td>{{ $email->case_id }}</td>
                            <td>{{ $email->pic }}</td>
                            <td class="name">{{ $email->customer_name }}</td>
                            <td>{{ $email->customer_email }}</td>
                            <td>{{ $email->subject }}</td>
                            <td>{{ $email->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; color:var(--muted); padding:26px;">
                                Belum ada email terkirim pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel__body">
        {{ $recentEmails->links() }}
    </div>
</section>

@endsection