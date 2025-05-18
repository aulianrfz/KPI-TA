@extends('layouts.apk')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Institusi</h4>
        <a href="{{ route('institusi.create') }}" class="btn btn-primary">+ Tambah Data</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Institusi</th>
                        <th>Alamat</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($institusis as $index => $institusi)
                    <tr>
                        <td>{{ $institusis->firstItem() + $index }}</td>
                        <td>{{ $institusi->nama_institusi }}</td>
                        <td>{{ $institusi->alamat }}</td>
                        <td>
                            <a href="{{ route('institusi.edit', $institusi->id) }}" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('institusi.destroy', $institusi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach

                    @if($institusis->isEmpty())
                    <tr>
                        <td colspan="4" class="text-center">Belum ada data institusi.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $institusis->links() }}
    </div>
</div>
@endsection