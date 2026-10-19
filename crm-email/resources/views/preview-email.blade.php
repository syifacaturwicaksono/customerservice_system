    @extends('layouts.admin')

@section('content')

<h3>Preview Email</h3>

<div class="card mb-3">
    <div class="card-body">
        <h5>Subject:</h5>
        <p>{{ $subject }}</p>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h5>Email Body:</h5>
        <div style="border:1px solid #ddd; padding:10px;">
            {!! $body !!}
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5>WhatsApp:</h5>
        <div style="background:#f1f1f1; padding:10px;">
            {!! nl2br(e($waBody)) !!}
        </div>
    </div>
</div>

<a href="/create-email" class="btn btn-secondary mt-3">Kembali</a>

@endsection