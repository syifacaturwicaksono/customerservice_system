@extends('layouts.admin')

@section('content')

<h3>➕ Tambah Template</h3>

<form method="POST" action="/templates/store">
    @csrf

    <div class="mb-3">
        <label>Subject</label>
        <input type="text" name="subject" class="form-control">
    </div>

    <div class="mb-3">
        <label>Email Body</label>
        <textarea name="body" class="form-control" rows="6"></textarea>
    </div>

    <div class="mb-3">
        <label>WA Body</label>
        <textarea name="wa_body" class="form-control" rows="4"></textarea>
    </div>

    <button class="btn btn-success">💾 Simpan</button>
    <a href="/templates" class="btn btn-secondary">Kembali</a>

</form>

@endsection