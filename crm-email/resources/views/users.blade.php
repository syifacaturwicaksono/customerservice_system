@extends('layouts.app') {{-- sesuaikan kalau layout kamu beda --}}

@section('content')

<div class="container">

    <h3 class="mb-3">Manajemen User</h3>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- BUTTON TAMBAH --}}
   

    {{-- TABLE --}}
    <table class="table table-bordered bg-white">
        <thead align="center">
            <tr>
                <th width="50">ID</th>
                <th>Username</th>
                <th>Role</th>
                <th width="200">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($users as $u)
            <tr>
                <td align="center">{{ $u->id }}</td>
                <td>{{ $u->username }}</td>
                <td>{{ $u->role }}</td>
                <td align="center">
                    <a href="/users/edit/{{ $u->id }}" class="btn btn-warning btn-sm">Edit</a>

                    
                </td>
            </tr>
            @endforeach
        </tbody>

    </table>

</div>

@endsection