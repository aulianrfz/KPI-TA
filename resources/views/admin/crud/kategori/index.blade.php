@extends('layouts.apk')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-primary">Data Kategori</h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
            + Tambah Kategori
        </button>
    </div>

    <form method="GET" action="{{ route('kategori.index') }}" class="mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <div style="width: 250px;">
                <select name="event_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Pilih Event --</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                            {{ $event->nama_event }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(request('event_id'))
                <div class="position-relative" style="width: 300px;">
                    <input type="text" name="search" class="form-control ps-4"
                        placeholder="Cari berdasarkan nama kategori"
                        value="{{ request('search') }}">
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fa fa-search"></i>
                </button>

                <a href="{{ route('kategori.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </div>
    </form>

    @if(request('event_id'))
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center">Nama Kategori</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kategorislomba as $kategori)
                            <tr>
                                <td class="align-middle text-center">{{ $kategori->nama_kategori }}</td>
                                <td class="align-middle text-center">
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditKategori{{ $kategori->id }}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <form action="{{ route('kategori.destroy', $kategori->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin mau hapus?')">
                                       @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                    </form>
                                    <a href="{{ route('mataLomba.index', ['kategori_id' => $kategori->id]) }}" class="btn btn-info btn-sm">
                                        <i class="fa fa-eye"></i> Mata Lomba
                                    </a>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalEditKategori{{ $kategori->id }}" tabindex="-1" aria-labelledby="modalEditKategoriLabel{{ $kategori->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('kategori.update', $kategori->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Kategori</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Pilih Event</label>
                                                    <select name="event_id" class="form-select" required>
                                                        <option value="">-- Pilih Event --</option>
                                                        @foreach($events as $event)
                                                            <option value="{{ $event->id }}" {{ $event->id == $kategori->event_id ? 'selected' : '' }}>
                                                                {{ $event->nama_event }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Kategori</label>
                                                    <input type="text" name="nama_kategori" value="{{ $kategori->nama_kategori }}" class="form-control" required>
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
                        @empty
                            <tr>
                                <td colspan="2" class="text-center">Tidak ada kategori untuk event ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end align-items-center mt-3 gap-2">
            <span class="small text-muted mb-0">
                Page {{ $kategorislomba->currentPage() }} of {{ $kategorislomba->lastPage() }}
            </span>
            @if ($kategorislomba->onFirstPage())
                <span class="btn btn-sm btn-light disabled">‹</span>
            @else
                <a href="{{ $kategorislomba->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">‹</a>
            @endif
            @if ($kategorislomba->hasMorePages())
                <a href="{{ $kategorislomba->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">›</a>
            @else
                <span class="btn btn-sm btn-light disabled">›</span>
            @endif
        </div>
    @else
        <div class="alert alert-info">Silakan pilih event terlebih dahulu untuk melihat daftar kategori lomba.</div>
    @endif
</div>

<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-labelledby="modalTambahKategoriLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('kategori.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Tambah Kategori</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
              <label class="form-label">Pilih Event</label>
              <select name="event_id" class="form-select" required>
                  <option value="">-- Pilih Event --</option>
                  @foreach($events as $event)
                      <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                          {{ $event->nama_event }}
                      </option>
                  @endforeach
              </select>
          </div>
          <div class="mb-3">
              <label class="form-label">Nama Kategori</label>
              <input type="text" name="nama_kategori" class="form-control" required>
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
@endsection
