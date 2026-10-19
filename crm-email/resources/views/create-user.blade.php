@extends('layouts.app')

@section('content')

@php
    // Teknisi cabang Semarang yang belum punya akun
    $teknisiList = \App\Models\Technician::where('cabang', 'Semarang')
        ->whereNotIn('id', \App\Models\User::whereNotNull('technician_id')->pluck('technician_id'))
        ->orderBy('name')->get();
@endphp

<div class="container">

    <h3>Tambah User</h3>

    <form method="POST" action="/users/store">
        @csrf

        <div class="mb-2">
            <label>Role</label>
            <select name="role" id="roleSelect" class="form-control">
                <option value="cs" {{ old('role') == 'cs' ? 'selected' : '' }}>CS</option>
                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="teknisi" {{ old('role') == 'teknisi' ? 'selected' : '' }}>Teknisi (hanya Dashboard Semarang)</option>
            </select>
        </div>

        <div class="mb-2" id="teknisiWrap" style="display:none;">
            <label>Nama Teknisi (sesuai database)</label>
            <select name="technician_id" id="teknisiSelect" class="form-control">
                <option value="">— pilih teknisi —</option>
                @foreach($teknisiList as $t)
                    <option value="{{ $t->id }}" data-name="{{ $t->name }}" {{ old('technician_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
            @if($teknisiList->isEmpty())
                <small class="text-muted">Semua teknisi Semarang sudah punya akun.</small>
            @endif
        </div>

        <div class="mb-2">
            <label>Username</label>
            <input type="text" name="username" id="usernameInput" value="{{ old('username') }}" class="form-control">
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
        </div>

        <div class="mb-2">
            <label>Password</label>
            <input type="password" name="password" class="form-control">
        </div>

        <button class="btn btn-success">Simpan</button>

    </form>

</div>

<script>
(function () {
    var role   = document.getElementById('roleSelect');
    var wrap   = document.getElementById('teknisiWrap');
    var teknisi = document.getElementById('teknisiSelect');
    var uname  = document.getElementById('usernameInput');

    function sync() {
        var isTeknisi = role.value === 'teknisi';
        wrap.style.display = isTeknisi ? '' : 'none';
        if (!isTeknisi) teknisi.value = '';
    }

    // pilih teknisi -> username otomatis diisi nama teknisi
    teknisi.addEventListener('change', function () {
        var opt = teknisi.options[teknisi.selectedIndex];
        if (opt && opt.dataset.name) uname.value = opt.dataset.name;
    });

    role.addEventListener('change', sync);
    sync();
})();
</script>

@endsection