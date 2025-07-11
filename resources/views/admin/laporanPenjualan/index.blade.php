@extends('layouts.apk')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="container py-4">

    {{-- Ringkasan Statistik --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 text-center py-3 h-100">
                <div class="d-flex justify-content-between align-items-center px-3">
                    <div>
                        <h6 class="text-muted">Tiket Terjual</h6>
                        <h4 class="fw-bold text-primary">{{ $totalPeserta }}</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 3rem; height: 3rem;">
                        <i class="bi bi-ticket-perforated-fill fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 text-center py-3 h-100">
                <div class="d-flex justify-content-between align-items-center px-3">
                    <div>
                        <h6 class="text-muted">Penyebaran Provinsi</h6>
                        <h4 class="fw-bold text-success">{{ $provinsiCount }}</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 3rem; height: 3rem;">
                        <i class="bi bi-geo-alt-fill fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 text-center py-3 h-100">
                <div class="d-flex justify-content-between align-items-center px-3">
                    <div>
                        <h6 class="text-muted">Institusi Mendaftar</h6>
                        <h4 class="fw-bold text-danger">{{ $institusiCount }}</h4>
                    </div>
                    <div class="bg-danger bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 3rem; height: 3rem;">
                        <i class="bi bi-building fs-2 text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Pencarian dan Sorting --}}
    <div class="row mb-3">
        <div class="col-md-5">
            <form method="GET" action="{{ route('laporan.penjualan', $eventId) }}" class="w-100">
                <div class="position-relative">
                    <input type="text" name="search" class="form-control ps-5" placeholder="Cari nama institusi" value="{{ request('search') }}">
                    <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                </div>
            </form>
        </div>
        <div class="col-md-2">
            <form method="GET" action="{{ route('laporan.penjualan', $eventId) }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <select name="sort" class="form-select" onchange="this.form.submit()">
                    <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>A-Z</option>
                    <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Z-A</option>
                </select>
            </form>
        </div>
    </div>

    {{-- Tabel Laporan --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0 text-center">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Institusi</th>
                        <th>Total Tiket</th>
                        <th>Total Mahasiswa</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($laporanList as $index => $item)
                        <tr>
                            <td>{{ str_pad($index + $laporanList->firstItem(), 2, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $item->institusi }}</td>
                            <td>{{ $item->total_tiket }}</td>
                            <td>{{ $item->total_mahasiswa }} Orang</td>
                            <td>
                                <a href="{{ route('laporan.penjualan.detail', ['event' => $eventId, 'institusi' => urlencode($item->institusi)]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4">Tidak ada data ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-end align-items-center mt-3 gap-2 px-3">
            <span class="small text-muted mb-0">
                Page {{ $laporanList->currentPage() }} of {{ $laporanList->lastPage() }}
            </span>
            @if ($laporanList->onFirstPage())
                <span class="btn btn-sm btn-light disabled">‹</span>
            @else
                <a href="{{ $laporanList->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">‹</a>
            @endif

            @if ($laporanList->hasMorePages())
                <a href="{{ $laporanList->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">›</a>
            @else
                <span class="btn btn-sm btn-light disabled">›</span>
            @endif
        </div>
    </div>
</div>
@endsection
