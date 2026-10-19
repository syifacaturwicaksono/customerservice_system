@extends('layouts.admin')

@section('content')

<h3>✏️ Edit Teknisi</h3>

<form method="POST" action="/technicians/update/{{ $technician->id }}">
@csrf

<div class="mb-3">
    <label>Nama Teknisi</label>
    <input type="text" name="name" class="form-control" value="{{ $technician->name }}" required>
</div>

<div class="mb-3">
    <label>No HP</label>
    <input type="text" name="phone" class="form-control" value="{{ $technician->phone }}" required>
</div>

<button class="btn btn-primary">💾 Update</button>
<a href="/technicians" class="btn btn-secondary">Kembali</a>

</form>

@endsection