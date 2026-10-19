@extends('layouts.admin')

@section('content')

<h3>✏️ Edit Template</h3>

<form method="POST" action="/templates/update/{{ $template->id }}">
    @csrf

    <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="{{ $template->name }}">
    </div>
    <div class="mb-3">
    <label>Subject</label>
    <input type="text" name="subject" class="form-control"
        value="{{ $template->subject }}">
    </div>
    <div class="mb-3">
        <label>Email Body</label>
        <textarea name="body" class="form-control" rows="6">{{ $template->body }}</textarea>
    </div>

    <div class="mb-3">
        <label>WA Body</label>
        <textarea name="wa_body" class="form-control" rows="4">{{ $template->wa_body }}</textarea>
    </div>
    

    <button class="btn btn-primary">💾 Simpan</button>
    <a href="/templates" class="btn btn-secondary">Kembali</a>
</form>

@endsection