@extends('layouts.apk')

@section('content')
<div class="container mt-5">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Jurusan</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">+ Tambah Data</button>
        </div>

        <form method="GET" action="{{ route('jurusan.index') }}" class="mb-3">
            <div class="d-flex justify-content-start align-items-center gap-2 flex-wrap">
                <div class="position-relative" style="width: 300px;">
                    <input
                        type="text"
                        name="search"
                        class="form-control ps-4"
                        placeholder="Cari berdasarkan nama kategori"
                        value="{{ request('search') }}">
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
                <table class="table table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Jurusan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jurusans as $index => $jurusan)
                        <tr>
                            <td>{{ $jurusans->firstItem() + $index }}</td>
                            <td>{{ $jurusan->nama_jurusan }}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $jurusan->id }}"><i class="fas fa-edit"></i></button>
                                <form action="{{ route('jurusan.destroy', $jurusan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="modalEdit{{ $jurusan->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('jurusan.update', $jurusan->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Jurusan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="text" name="nama_jurusan" class="form-control" value="{{ $jurusan->nama_jurusan }}" required>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success">Simpan</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr><td colspan="3" class="text-center">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end align-items-center mt-3 gap-2">
            <span class="small text-muted mb-0">
                Page {{ $jurusans->currentPage() }} of {{ $jurusans->lastPage() }}
            </span>
            @if ($jurusans->onFirstPage())
                <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">‹</span>
            @else
                <a href="{{ $jurusans->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">‹</a>
            @endif
            @if ($jurusans->hasMorePages())
                <a href="{{ $jurusans->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">›</a>
            @else
                <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">›</span>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('jurusan.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jurusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="nama_jurusan" class="form-control" placeholder="Nama Jurusan" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection