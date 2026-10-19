@extends('layouts.app')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">🟢 Part Ready Queue</h3>
            <small class="text-muted">Case dengan part sudah ready, siap direminder ke customer (sumber: upload ISD)</small>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ★★★ BARU: banner ini muncul kalau halaman dibuka otomatis habis
         upload ISD (?kode_upload=X), supaya CS tahu daftar di bawah adalah
         hasil upload yang baru saja diproses, bukan daftar semua case. --}}
    @if(!empty($kodeUpload))
        <div class="alert alert-info d-flex justify-content-between align-items-center" role="alert">
            <div>
                📥 Menampilkan case dari upload batch <strong>{{ $kodeUpload }}</strong>.
                Klik <strong>✉️ Buat Pesan</strong> di tiap case untuk review &amp; kirim.
            </div>
            <a href="/part-ready" class="btn btn-sm btn-outline-secondary">Lihat Semua Part Ready</a>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4" style="border-radius:16px;">
        <div class="card-body">

            <form method="GET" action="/part-ready" class="row g-3 align-items-end mb-3">
                @if(!empty($kodeUpload))
                    <input type="hidden" name="kode_upload" value="{{ $kodeUpload }}">
                @endif
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    @php
                        $defaultStatus = !empty($kodeUpload) ? 'all' : 'pending';
                    @endphp
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="pending" {{ request('status', $defaultStatus) == 'pending' ? 'selected' : '' }}>Belum Direminder</option>
                        <option value="sent" {{ request('status', $defaultStatus) == 'sent' ? 'selected' : '' }}>Sudah Direminder</option>
                        <option value="skipped" {{ request('status', $defaultStatus) == 'skipped' ? 'selected' : '' }}>Dilewati</option>
                        <option value="all" {{ request('status', $defaultStatus) == 'all' ? 'selected' : '' }}>Semua</option>
                    </select>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Case ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Produk</th>
                            <th>Kota</th>
                            <th>Status</th>
                            <th width="220">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cases as $c)
                            <tr>
                                <td>{{ $c->case_id }}</td>
                                <td>{{ $c->customer_name ?? '-' }}</td>
                                <td>{{ $c->customer_email ?? '-' }}</td>
                                <td>{{ $c->mobile_no ?? $c->phone_no ?? '-' }}</td>
                                <td>{{ $c->product_name ?? '-' }}</td>
                                <td>{{ $c->city ?? '-' }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($c->status) {
                                            'sent' => 'bg-success',
                                            'skipped' => 'bg-secondary',
                                            default => 'bg-warning text-dark',
                                        };
                                        $badgeLabel = match($c->status) {
                                            'sent' => 'Sudah Direminder',
                                            'skipped' => 'Dilewati',
                                            default => 'Belum Direminder',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                </td>
                                <td>
                                    <a href="/create-email?part_ready_id={{ $c->id }}" class="btn btn-sm btn-primary">
                                        ✉️ Buat Pesan
                                    </a>
                                    @if($c->status !== 'sent')
                                        <form method="POST" action="/part-ready/{{ $c->id }}/mark-sent" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                Tandai Sudah
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $cases->links() }}
            </div>

        </div>
    </div>

@endsection