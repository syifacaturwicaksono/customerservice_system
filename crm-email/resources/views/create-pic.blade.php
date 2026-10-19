@extends('layouts.admin')

@section('content')

<h3>➕ Tambah PIC</h3>

<form method="POST" action="/pics/store">
@csrf

<div class="mb-3">
    <label>Nama PIC</label>
    <input type="text" name="name" class="form-control" required>
</div>

<button class="btn btn-success">💾 Simpan</button>
<a href="/pics" class="btn btn-secondary">Kembali</a>

</form>

@endsection