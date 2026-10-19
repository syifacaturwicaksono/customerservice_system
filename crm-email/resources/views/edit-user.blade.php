@extends('layouts.app')

@section('content')

@php
    // Teknisi Semarang yang belum punya akun + teknisi milik user ini
    $teknisiList = \App\Models\Technician::where('cabang', 'Semarang')
        ->where(function ($q) use ($user) {
            $q->whereNotIn('id', \App\Models\User::whereNotNull('technician_id')->pluck('technician_id'))
              ->orWhere('id', $user->technician_id);
        })
        ->orderBy('name')->get();
    $roleNow = old('role', $user->role);
@endphp

<div class="container">

    <h3>Edit User</h3>

    <form method="POST" action="/users/update/{{ $user->id }}">
        @csrf

        <div class="mb-2">
            <label>Role</label>
            <select name="role" id="roleSelect" class="form-control">
                <option value="cs" {{ $roleNow == 'cs' ? 'selected' : '' }}>CS</option>
                <option value="admin" {{ $roleNow == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="teknisi" {{ $roleNow == 'teknisi' ? 'selected' : '' }}>Teknisi (hanya Dashboard Semarang)</option>
            </select>
        </div>

        <div class="mb-2" id="teknisiWrap" style="display:none;">
            <label>Nama Teknisi (sesuai database)</label>
            <select name="technician_id" id="teknisiSelect" class="form-control">
                <option value="">— pilih teknisi —</option>
                @foreach($teknisiList as $t)
                    <option value="{{ $t->id }}" data-name="{{ $t->name }}" {{ old('technician_id', $user->technician_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-2">
            <label>Username</label>
            <input type="text" name="username" id="usernameInput" value="{{ old('username', $user->username) }}" class="form-control">
        </div>

        <div class="mb-2">
            <label>Password (opsional)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <button class="btn btn-primary">Update</button>

    </form>

</div>

<script>
(function () {
    var role    = document.getElementById('roleSelect');
    var wrap    = document.getElementById('teknisiWrap');
    var teknisi = document.getElementById('teknisiSelect');
    var uname   = document.getElementById('usernameInput');

    function sync() {
        var isTeknisi = role.value === 'teknisi';
        wrap.style.display = isTeknisi ? '' : 'none';
        if (!isTeknisi) teknisi.value = '';
    }

    teknisi.addEventListener('change', function () {
        var opt = teknisi.options[teknisi.selectedIndex];
        if (opt && opt.dataset.name) uname.value = opt.dataset.name;
    });

    role.addEventListener('change', sync);
    sync();
})();
</script>

@endsection