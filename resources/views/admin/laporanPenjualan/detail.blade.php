@extends('layouts.apk')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-3">
        <a href="{{ route('laporan.penjualan', ['event' => $eventId]) }}" class="btn btn-sm btn-light me-2">&larr;</a> 
        {{ $institusi }}
    </h4>

    <div class="card shadow-sm border-0 rounded-4 p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <form method="GET" action="{{ route('laporan.penjualan.detail', ['event' => $eventId, 'institusi' => urlencode($institusi)]) }}" class="w-50">
                <div class="input-group">
                    <input type="text" name="search" class="form-control rounded-pill" placeholder="Search for something" value="{{ request('search') }}">
                    <button class="btn btn-primary ms-2 rounded-pill px-4">Search</button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0 text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Peserta</th>
                        <th>Tim/Individu</th>
                        <th>Kategori</th>
                        <th>Mata Lomba</th>
                        <th>Nama Tim</th>
                        <!-- <th>Action</th> -->
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendaftarList as $index => $pendaftar)
                        <tr>
                            <td>{{ str_pad($index + $pendaftarList->firstItem(), 2, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $pendaftar->peserta->nama_peserta ?? '-' }}</td>
                            <td>{{ $pendaftar->peserta->jenis_peserta ?? '-' }}</td>
                            <td>{{ $pendaftar->mataLomba->kategori->nama_kategori ?? '-' }}</td>
                            <td>{{ $pendaftar->mataLomba->nama_lomba ?? '-' }}</td>
                            <td>{{ optional($pendaftar->peserta->bergabung->tim)->nama_tim ?? '-' }}</td>
                            <!-- <td>
                                <a href="#" class="btn btn-sm btn-primary"><i class="bi bi-pencil-square"></i></a>
                                <a href="#" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                            </td> -->
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Data tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end align-items-center mt-3 gap-2">
            <span class="small text-muted mb-0">Page {{ $pendaftarList->currentPage() }} of {{ $pendaftarList->lastPage() }}</span>
            @if ($pendaftarList->onFirstPage())
                <span class="btn btn-sm btn-light disabled">‹</span>
            @else
                <a href="{{ $pendaftarList->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">‹</a>
            @endif
            @if ($pendaftarList->hasMorePages())
                <a href="{{ $pendaftarList->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">›</a>
            @else
                <span class="btn btn-sm btn-light disabled">›</span>
            @endif
        </div>
    </div>
</div>
@endsection
