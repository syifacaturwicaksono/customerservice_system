@extends('layouts.admin')

@section('content')

<h3>Data Template</h3>

<table class="table table-bordered" >
    <thead>
        <tr>
            <th width="50">No</th>
            <th>Name</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($templates as $t)
        <tr>
            <td align="center">{{ $loop->iteration }}</td>
            <td>{{ $t->name }}</td>
            <td>
                <a href="/templates/edit/{{ $t->id }}" class="btn btn-warning btn-sm">
                    Edit
                    
                
            </td>
        </tr>
        @endforeach
    </tbody>
    
</table>

@endsection