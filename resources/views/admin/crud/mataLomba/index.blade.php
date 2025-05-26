@extends('layouts.apk')

@section('content')
<div class="container mt-5">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Sub Kategori</h4>
            <a href="{{ route('mataLomba.create') }}" class="btn btn-primary">+ Tambah Data</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif


        <form method="GET" action="{{ route('mataLomba.index') }}" class="mb-3">
            <div class="d-flex justify-content-start align-items-center gap-2 flex-wrap">
                <div class="position-relative" style="width: 300px;">
                    <input
                        type="text"
                        name="search"
                        class="form-control rounded-pill ps-4"
                        placeholder="Cari berdasarkan nama lomba"
                        value="{{ request('search') }}"
                    >
                </div>
                <button type="submit" class="btn btn-success">
                <i class="fa fa-search"></i>
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Nama mataLomba</th>
                        <th>Jurusan</th>
                        <th>Maks Peserta</th>
                        <th>Maks Total Peserta</th>
                        <th>Biaya</th>
                        <th>Foto</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mataLombas as $index => $mataLomba)
                    <tr>
                        <td>{{ $mataLombas->firstItem() + $index }}</td>
                        <td>{{ $mataLomba->kategori->nama_kategori ?? '-' }}</td>
                        <td>{{ $mataLomba->nama_lomba }}</td>
                        <td>{{ $mataLomba->jurusan }}</td>
                        <td>{{ $mataLomba->maks_peserta }}</td>
                        <td>{{ $mataLomba->maks_total_peserta }}</td>
                        <td>Rp {{ number_format($mataLomba->biaya_pendaftaran, 0, ',', '.') }}</td>
                        <td>
                            @if($mataLomba->foto_kompetisi)
                                <img src="{{ asset('storage/' . $mataLomba->foto_kompetisi) }}" width="70">
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('mataLomba.edit', $mataLomba->id) }}" class="btn btn-sm btn-warning me-1" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('mataLomba.destroy', $mataLomba->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end align-items-center mt-3 gap-2">
            <span class="small text-muted mb-0">
                Page {{ $mataLombas->currentPage() }} of {{ $mataLombas->lastPage() }}
            </span>
            @if ($mataLombas->onFirstPage())
                <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">‹</span>
            @else
                <a href="{{ $mataLombas->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">‹</a>
            @endif
            @if ($mataLombas->hasMorePages())
                <a href="{{ $mataLombas->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">›</a>
            @else
                <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">›</span>
            @endif
        </div>
    </div>
</div>

@endsection
