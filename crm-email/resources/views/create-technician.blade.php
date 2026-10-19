@extends('layouts.admin')

@section('content')

<h3>➕ Tambah Teknisi</h3>

<form method="POST" action="/technicians/store">
@csrf

<div class="mb-3">
    <label>Nama Teknisi</label>
    <input type="text" name="name" class="form-control" required>
</div>

<div class="mb-3">
    <label>No HP</label>
    <input type="text" name="phone" class="form-control" required>
</div>

<button class="btn btn-success">💾 Simpan</button>
<a href="/technicians" class="btn btn-secondary">Kembali</a>

</form>

@endsection