@extends('layouts.apk')

@section('content')
<div class="container mt-5">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Institusi</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahInstitusi">+ Tambah Data</button>
        </div>

        <form method="GET" action="{{ route('institusi.index') }}" class="mb-3">
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
                                <button type="button" class="btn btn-sm btn-warning me-1" data-bs-toggle="modal" data-bs-target="#modalEditInstitusi{{ $institusi->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
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
        <div class="d-flex justify-content-end align-items-center mt-3 gap-2">
            <span class="small text-muted mb-0">
                Page {{ $institusis->currentPage() }} of {{ $institusis->lastPage() }}
            </span>
            @if ($institusis->onFirstPage())
                <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">‹</span>
            @else
                <a href="{{ $institusis->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">‹</a>
            @endif
            @if ($institusis->hasMorePages())
                <a href="{{ $institusis->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">›</a>
            @else
                <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">›</span>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahInstitusi" tabindex="-1" aria-labelledby="modalTambahInstitusiLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('institusi.store') }}" method="POST" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahInstitusiLabel">Tambah Institusi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="nama_institusi" class="form-label">Nama Institusi</label>
          <input type="text" class="form-control" name="nama_institusi" required>
        </div>
        <div class="mb-3">
          <label for="alamat" class="form-label">Alamat</label>
          <textarea name="alamat" class="form-control" rows="3" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

@foreach($institusis as $institusi)
<div class="modal fade" id="modalEditInstitusi{{ $institusi->id }}" tabindex="-1" aria-labelledby="modalEditInstitusiLabel{{ $institusi->id }}" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('institusi.update', $institusi->id) }}" method="POST" class="modal-content">
      @csrf
      @method('PUT')
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditInstitusiLabel{{ $institusi->id }}">Edit Institusi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="nama_institusi" class="form-label">Nama Institusi</label>
          <input type="text" class="form-control" name="nama_institusi" value="{{ $institusi->nama_institusi }}" required>
        </div>
        <div class="mb-3">
          <label for="alamat" class="form-label">Alamat</label>
          <textarea name="alamat" class="form-control" rows="3" required>{{ $institusi->alamat }}</textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-success">Update</button>
      </div>
    </form>
  </div>
</div>
@endforeach

@endsection