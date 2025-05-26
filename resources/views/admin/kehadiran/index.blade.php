@extends('layouts.apk')

@section('title', 'Dashboard Kehadiran')

@section('content')
<div class="container py-4">

    <!-- Cards 3 kolom responsif -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 py-3 h-100" style="background-color: #e0f2fe;">
                <div class="card-body d-flex justify-content-between align-items-center px-4">
                    <div class="text-primary fw-semibold fs-6 d-flex align-items-center">
                        Total Peserta 
                        <i class="bi bi-person-fill ms-2 fs-4"></i>
                    </div>
                    <div class="fs-2 fw-bold text-primary">{{ $totalPeserta }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 py-3 h-100" style="background-color: #d1fae5;">
                <div class="card-body d-flex justify-content-between align-items-center px-4">
                    <div class="text-success fw-semibold fs-6 d-flex align-items-center">
                        Peserta On-site 
                        <i class="bi bi-person-fill ms-2 fs-4"></i>
                    </div>
                    <div class="fs-2 fw-bold text-success">{{ $pesertaOnsite }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 py-3 h-100" style="background-color: #fee2e2;">
                <div class="card-body d-flex justify-content-between align-items-center px-4">
                    <div class="text-danger fw-semibold fs-6 d-flex align-items-center">
                        Belum Daftar Ulang 
                        <i class="bi bi-person-fill ms-2 fs-4"></i>
                    </div>
                    <div class="fs-2 fw-bold text-danger">{{ $belumDaftarUlang }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search form -->
    <div class="d-flex flex-column flex-md-row justify-content-between mb-3">
        <form method="GET" action="{{ route('kehadiran.index') }}" class="d-flex w-100 w-md-auto">
            <div class="input-group" style="max-width: 400px;">
                <input type="text" name="search" class="form-control border" placeholder="Cari peserta" style="border-color: #0367A6;" value="{{ request('search') }}">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>

    <!-- Table responsif -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-bordered mb-0 text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Institusi</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Lomba</th>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">QR</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendaftar as $i => $p)
                    <tr>
                        <td class="px-4 py-2">{{ str_pad($i + 1 + ($pendaftar->currentPage()-1)*$pendaftar->perPage(), 2, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-4 py-2">{{ $p->peserta->nama_peserta ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $p->peserta->institusi ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $p->kategori ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $p->mataLomba->nama_lomba ?? '-' }}</td>
                        <td class="px-4 py-2">
                            {{ optional($p->kehadiran)->tanggal ? \Carbon\Carbon::parse($p->kehadiran->tanggal)->format('H:i') : '-' }}
                        </td>
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.qr.show', $p->id) }}" class="text-primary">Lihat</a>
                        </td>
                        <td class="px-4 py-2">
                            @if($p->kehadiran)
                                <span class="badge bg-success">Hadir</span>
                            @else
                                <span class="badge bg-danger">Belum Hadir</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <a href="{{ route('pendaftar.edit', $p->id) }}" class="text-warning">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end align-items-center mt-3 gap-2 px-3">
            <span class="small text-muted mb-0">
                Page {{ $pendaftar->currentPage() }} of {{ $pendaftar->lastPage() }}
            </span>
            @if ($pendaftar->onFirstPage())
                <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">‹</span>
            @else
                <a href="{{ $pendaftar->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">‹</a>
            @endif
            @if ($pendaftar->hasMorePages())
                <a href="{{ $pendaftar->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">›</a>
            @else
                <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">›</span>
            @endif
        </div>
    </div>

</div>
@endsection
