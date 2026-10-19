@extends('layouts.admin')

@section('content')

<h3>Data Teknisi</h3>


<table class="table">
    <thead>
        <tr>
            <th width="50">No</th>
            <th>Nama</th>
            <th>No HP</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($technicians as $t)
        <tr>
            <td align="center">{{ $loop->iteration }}</td>
            <td>{{ $t->name }}</td>
            <td>{{ $t->phone }}</td>
            <td>
                <a href="/technicians/edit/{{ $t->id }}" class="btn btn-warning btn-sm">
                Edit
                </a>

                
</td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection