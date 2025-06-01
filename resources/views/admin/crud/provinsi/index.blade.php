@extends('layouts.apk')

@section('content')
<div class="container mt-5">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Provinsi</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahProvinsi">+ Tambah Provinsi</button>
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
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($provinsis as $index => $provinsi)
                        <tr>
                            <td>{{ $provinsis->firstItem() + $index }}</td>
                            <td>{{ $provinsi->nama_provinsi }}</td>
                            <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditProvinsi{{ $provinsi->id }}">Edit</button>
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
<div class="modal fade" id="modalTambahProvinsi" tabindex="-1" aria-labelledby="modalTambahProvinsiLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('provinsi.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalTambahProvinsiLabel">Tambah Provinsi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
              <label for="nama_provinsi" class="form-label">Nama Provinsi</label>
              <input type="text" name="nama_provinsi" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@foreach ($provinsis as $provinsi)
<div class="modal fade" id="modalEditProvinsi{{ $provinsi->id }}" tabindex="-1" aria-labelledby="modalEditProvinsiLabel{{ $provinsi->id }}" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('provinsi.update', $provinsi->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditProvinsiLabel{{ $provinsi->id }}">Edit Provinsi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
              <label for="nama_provinsi" class="form-label">Nama Provinsi</label>
              <input type="text" name="nama_provinsi" value="{{ $provinsi->nama_provinsi }}" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach

@endsection