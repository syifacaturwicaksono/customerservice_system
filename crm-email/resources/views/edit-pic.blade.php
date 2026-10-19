@extends('layouts.admin')

@section('content')

<h3>✏️ Edit PIC</h3>

<form method="POST" action="/pics/update/{{ $pic->id }}">
@csrf

<div class="mb-3">
    <label>Nama PIC</label>
    <input type="text" name="name" class="form-control" value="{{ $pic->name }}" required>
</div>

<button class="btn btn-primary">💾 Update</button>
<a href="/pics" class="btn btn-secondary">Kembali</a>

</form>

@endsection