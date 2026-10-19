@extends('layouts.admin')

@section('content')

    <h3>Data PIC</h3>


    <table class="table table-bordered">
        <thead>
            <tr>
                <th width="50">No</th>
                <th>Nama</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($pics as $p)
                <tr>
                    <td align="center">{{ $loop->iteration }}</td>
                    <td>{{ $p->name }}</td>
                    <td>
                        <a href="/pics/edit/{{ $p->id }}" class="btn btn-warning btn-sm">Edit</a>

                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>

@endsection