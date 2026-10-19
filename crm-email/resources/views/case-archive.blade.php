@extends('layouts.app')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">🗂️ Arsip Close Case</h3>
            <small class="text-muted">Pencarian &amp; penyimpanan permanen seluruh riwayat Close Case</small>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- ★★★ BARU: REMINDER TAT OTOMATIS ★★★ -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius:16px;">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="fw-bold mb-1">🔔 Reminder TAT Otomatis</h5>
                    <small class="text-muted d-block" style="max-width:640px;">
                        Kirim WA otomatis ke teknisi kalau ada case Pending yang TAT-nya (sejak case dibuka)
                        sudah &ge; <strong>{{ $reminderSetting->threshold_days ?? 3 }} hari</strong> dan
                        belum Finish/Cancel Repair. Reminder dikirim ULANG setiap hari selama case masih
                        tertahan, supaya TAT per teknisi tidak menumpuk tinggi.
                    </small>
                </div>
                <div class="d-flex gap-2">
                    <form method="POST" action="/bc-email/reminder/toggle">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ ($reminderSetting->is_active ?? false) ? 'btn-success' : 'btn-outline-secondary' }}">
                            {{ ($reminderSetting->is_active ?? false) ? '🟢 Aktif — klik utk matikan' : '⚪ Nonaktif — klik utk aktifkan' }}
                        </button>
                    </form>
                    <form method="POST" action="/bc-email/reminder/send-now"
                        onsubmit="return confirm('Kirim WA reminder sekarang juga ke semua teknisi yang punya case tertahan? (buat uji coba)');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">🚀 Kirim Reminder Sekarang (Test)</button>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Teknisi</th>
                            <th width="140">Jumlah Case Direminder</th>
                            <th width="120">Status Kirim</th>
                            <th width="170">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reminderLogsToday as $ceName => $logs)
                            <tr>
                                <td>{{ $ceName }}</td>
                                <td><span class="badge bg-info text-dark">{{ $logs->count() }}</span></td>
                                <td>
                                    <span class="badge {{ $logs->first()->status == 'sent' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $logs->first()->status == 'sent' ? 'Terkirim' : 'Gagal' }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($logs->first()->created_at)->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">Belum ada reminder yang terkirim hari ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- FILTER -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius:16px;">
        <div class="card-body">

            <form method="GET" action="/bc-email" class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Cari</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control"
                        placeholder="Case ID, nama perusahaan, customer, produk, dll...">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Dari Tanggal</label>
                    <input type="date" name="dari" value="{{ $dari }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Sampai Tanggal</label>
                    <input type="date" name="sampai" value="{{ $sampai }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Teknisi</label>
                    <select name="teknisi" class="form-select">
                        <option value="">Semua Teknisi</option>
                        @foreach($technicianNames as $name)
                            <option value="{{ $name }}" {{ $teknisi == $name ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Case Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach($statusOptions as $s)
                            <option value="{{ $s }}" {{ $status == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">🔍 Filter</button>
                    <a href="/bc-email" class="btn btn-outline-secondary">↺ Reset</a>
                </div>

            </form>

        </div>
    </div>

    <!-- HASIL -->
    <div class="card shadow-sm border-0" style="border-radius:16px;">
        <div class="card-body">

            <h5 class="fw-bold mb-3">
                📋 Hasil Pencarian
                <span class="badge bg-secondary ms-2">{{ number_format($cases->total()) }} case</span>
            </h5>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Case ID</th>
                            <th>Perusahaan</th>
                            <th>Customer</th>
                            <th>Teknisi</th>
                            <th>Produk</th>
                            <th>Case Status</th>
                            <th>Warranty</th>
                            <th width="150">Received Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cases as $case)
                            <tr>
                                <td>
                                    @php $caseIdValue = $case->case_id ?? $case->case_id_manual ?? null; @endphp
                                    @if($caseIdValue)
                                        <a href="https://myscs.co.id/app/isd/view_case.php?q={{ $caseIdValue }}"
                                            target="_blank" rel="noopener">{{ $caseIdValue }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $case->company_name ?? '-' }}</td>
                                <td>{{ $case->customer_name ?? $case->customer_company ?? '-' }}</td>
                                <td>{{ $case->ce_name ?? '-' }}</td>
                                <td>{{ $case->product_name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $case->case_status ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $case->warranty_status == 'In Warranty' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $case->warranty_status ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $case->received_date ? \Carbon\Carbon::parse($case->received_date)->format('Y-m-d H:i') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Tidak ada case yang cocok dengan pencarian</td>
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