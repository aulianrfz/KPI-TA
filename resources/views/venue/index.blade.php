@extends('layouts.apk')

@section('content')
<div class="container mt-5">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Venue</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahVenue">+ Tambah Venue</button>
        </div>

        <form method="GET" action="{{ route('venue.index') }}" class="mb-3">
            <div class="d-flex justify-content-start align-items-center gap-2 flex-wrap">
                <div class="position-relative" style="width: 300px;">
                    <input
                        type="text"
                        name="search"
                        class="form-control ps-4"
                        placeholder="Cari nama venue"
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
                            <th>Nama Venue</th>
                            <th>Tanggal Tersedia</th>
                            <th>Waktu Mulai</th>
                            <th>Waktu Berakhir</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($venues as $index => $venue)
                        <tr>
                            <td>{{ $venues->firstItem() + $index }}</td>
                            <td>{{ $venue->name }}</td>
                            <td>{{ $venue->tanggal_tersedia ?? '-' }}</td>
                            <td>{{ $venue->waktu_mulai_tersedia ?? '-' }}</td>
                            <td>{{ $venue->waktu_berakhir_tersedia ?? '-' }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditVenue{{ $venue->id }}">Edit</button>
                                <form action="{{ route('venue.destroy', $venue->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach

                        @if($venues->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data Venue.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination mirip provinsi --}}
        <div class="d-flex justify-content-end align-items-center mt-3 gap-2">
            <span class="small text-muted mb-0">
                Page {{ $venues->currentPage() }} of {{ $venues->lastPage() }}
            </span>
            @if ($venues->onFirstPage())
                <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">‹</span>
            @else
                <a href="{{ $venues->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">‹</a>
            @endif
            @if ($venues->hasMorePages())
                <a href="{{ $venues->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">›</a>
            @else
                <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">›</span>
            @endif
        </div>
    </div>
</div>

{{-- Modal Tambah Venue --}}
<div class="modal fade" id="modalTambahVenue" tabindex="-1" aria-labelledby="modalTambahVenueLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('venue.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalTambahVenueLabel">Tambah Venue</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
              <label for="name" class="form-label">Nama Venue</label>
              <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Tanggal Tersedia</label>
              <input type="date" name="tanggal_tersedia" class="form-control">
          </div>
          <div class="mb-3">
              <label class="form-label">Waktu Mulai</label>
              <input type="time" name="waktu_mulai_tersedia" class="form-control">
          </div>
          <div class="mb-3">
              <label class="form-label">Waktu Berakhir</label>
              <input type="time" name="waktu_berakhir_tersedia" class="form-control">
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

{{-- Modal Edit Venue --}}
@foreach ($venues as $venue)
<div class="modal fade" id="modalEditVenue{{ $venue->id }}" tabindex="-1" aria-labelledby="modalEditVenueLabel{{ $venue->id }}" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('venue.update', $venue->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditVenueLabel{{ $venue->id }}">Edit Venue</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
              <label for="name" class="form-label">Nama Venue</label>
              <input type="text" name="name" value="{{ $venue->name }}" class="form-control" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Tanggal Tersedia</label>
              <input type="date" name="tanggal_tersedia" value="{{ $venue->tanggal_tersedia }}" class="form-control">
          </div>
          <div class="mb-3">
              <label class="form-label">Waktu Mulai</label>
              <input type="time" name="waktu_mulai_tersedia" value="{{ $venue->waktu_mulai_tersedia }}" class="form-control">
          </div>
          <div class="mb-3">
              <label class="form-label">Waktu Berakhir</label>
              <input type="time" name="waktu_berakhir_tersedia" value="{{ $venue->waktu_berakhir_tersedia }}" class="form-control">
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
