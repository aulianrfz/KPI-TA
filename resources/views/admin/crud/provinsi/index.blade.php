@extends('layouts.apk')

@section('content')
<div class="container mt-5">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Provinsi</h4>
            <a href="{{ route('provinsi.create') }}" class="btn btn-primary">+ Tambah Data</a>
        </div>

        <form method="GET" action="{{ route('provinsi.index') }}" class="mb-3">
            <div class="d-flex justify-content-start align-items-center gap-2 flex-wrap">
                <div class="position-relative" style="width: 300px;">
                    <input
                        type="text"
                        name="search"
                        class="form-control rounded-pill ps-4"
                        placeholder="Cari berdasarkan nama kategori"
                        value="{{ request('search') }}"
                    >
                </div>
                <button type="submit" class="btn btn-success">
                <i class="fa fa-search"></i>
                </button>
            </div>
        </form>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body p-0">
                <table class="table table-bordered table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Provinsi</th>
                            <th>Alamat</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($provinsis as $index => $provinsi)
                        <tr>
                            <td>{{ $provinsis->firstItem() + $index }}</td>
                            <td>{{ $provinsi->nama_provinsi }}</td>
                            <td>{{ $provinsi->alamat }}</td>
                            <td>
                                <a href="{{ route('provinsi.edit', $provinsi->id) }}" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('provinsi.destroy', $provinsi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach

                        @if($provinsis->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center">Belum ada data institusi.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-end align-items-center mt-3 gap-2">
            <span class="small text-muted mb-0">
                Page {{ $provinsis->currentPage() }} of {{ $provinsis->lastPage() }}
            </span>
            @if ($provinsis->onFirstPage())
                <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">‹</span>
            @else
                <a href="{{ $provinsis->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">‹</a>
            @endif
            @if ($provinsis->hasMorePages())
                <a href="{{ $provinsis->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">›</a>
            @else
                <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">›</span>
            @endif
        </div>
    </div>
</div>
@endsection