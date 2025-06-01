@extends('layouts.apk')

@section('title', 'Dashboard Kehadiran')

@section('content')
<div class="container mt-5">
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card rounded-4 h-100 shadow-sm">
                <div class="card-body d-flex flex-column justify-content-between p-4" style="min-height: 130px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0" style="color: #0ea5e9;">Peserta Lomba</h6>
                        <i class="bi bi-person-fill fs-3" style="color: #0ea5e9;"></i>
                    </div>
                    <div class="fs-1 fw-bold">{{ $totalPeserta }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card rounded-4 h-100 shadow-sm">
                <div class="card-body d-flex flex-column justify-content-between p-4" style="min-height: 130px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0" style="color: #10b981;">Peserta On-site</h6>
                        <i class="bi bi-person-fill fs-3" style="color: #10b981;"></i>
                    </div>
                    <div class="fs-1 fw-bold">{{ $pesertaOnsite }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card rounded-4 h-100 shadow-sm">
                <div class="card-body d-flex flex-column justify-content-between p-4" style="min-height: 130px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0" style="color: #ef4444;">Belum Daftar Ulang</h6>
                        <i class="bi bi-person-fill fs-3" style="color: #ef4444;"></i>
                    </div>
                    <div class="fs-1 fw-bold">{{ $belumDaftarUlang }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <form method="GET" action="{{ route('kehadiran.index') }}" class="w-100 w-md-auto">
            <div class="input-group" style="max-width: 400px;">
                <input type="text" name="search" class="form-control" placeholder="Cari peserta"
                    value="{{ request('search') }}">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>

        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('kehadiran.index') }}">
                <select name="sort" onchange="this.form.submit()" class="px-2 py-1 border rounded">
                    <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Data Terbaru</option>
                    <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Data Terlama</option>
                </select>
            </form>
            <a href="{{ route('kehadiran.export', ['search' => request('search'), 'sort' => request('sort')]) }}"
                class="btn btn-success">
                <i class="bi bi-file-earmark-excel-fill"></i>
            </a>
        </div>
    </div>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle text-center">
                        <thead class="table-light border-bottom">
                            <tr>
                                <th class="border-start-0 border-end-0">No</th>
                                <th class="border-start-0 border-end-0">Nama Peserta</th>
                                <th class="border-start-0 border-end-0">Institusi</th>
                                <th class="border-start-0 border-end-0">Kategori</th>
                                <th class="border-start-0 border-end-0">Mata Lomba</th>
                                <th class="border-start-0 border-end-0">Waktu</th>
                                <th class="border-start-0 border-end-0">QR Code</th>
                                <th class="border-start-0 border-end-0">Status</th>
                                <th class="border-start-0 border-end-0">Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendaftar as $i => $p)
                            <tr>
                                <td class="border-start-0 border-end-0">
                                    {{ str_pad($i + 1 + ($pendaftar->currentPage()-1)*$pendaftar->perPage(), 2, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="border-start-0 border-end-0">{{ $p->peserta->nama_peserta ?? '-' }}</td>
                                <td class="border-start-0 border-end-0">{{ $p->peserta->institusi ?? '-' }}</td>
                                <td class="border-start-0 border-end-0">{{ $p->mataLomba->kategori->nama_kategori ?? '-' }}</td>
                                <td class="border-start-0 border-end-0">{{ $p->mataLomba->nama_lomba ?? '-' }}</td>
                                <td class="border-start-0 border-end-0">
                                    {{ optional($p->kehadiran)->tanggal ? \Carbon\Carbon::parse($p->kehadiran->tanggal)->format('H:i') : '-' }}
                                </td>
                                <td class="border-start-0 border-end-0">
                                    <a href="{{ route('admin.qr.show', $p->id) }}"
                                       class="btn btn-sm px-3 py-1 fw-semibold rounded"
                                       style="background-color: #A6C9E5; color: #0064B6;">
                                        Lihat
                                    </a>
                                </td>
                                <td class="border-start-0 border-end-0">
                                    @if($p->kehadiran?->status === 'Hadir')
                                    <span class="badge fw-semibold px-3 py-2 rounded text-center" style="background-color: #A3E4DB; color:rgb(2, 129, 110); min-width: 100px;">
                                        Hadir
                                    </span>
                                    @else
                                    <span class="badge fw-semibold px-3 py-2 rounded text-center" style="background-color: #FCD7D4; color: #EF3826; min-width: 100px;">
                                        Belum Hadir
                                    </span>
                                    @endif
                                </td>
                                <td class="border-start-0 border-end-0">
                                    <a href="{{ route('kehadiran.edit', $p->id) }}"
                                       class="btn btn-sm px-3 py-1 fw-semibold rounded"
                                        style="background-color: #FBDDCC; color: #EF3826;" >
                                        Edit
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end align-items-center mt-3 gap-2 px-3 pb-3">
                    <span class="small text-muted">
                        Page {{ $pendaftar->currentPage() }} of {{ $pendaftar->lastPage() }}
                    </span>
                    @if ($pendaftar->onFirstPage())
                    <span class="btn btn-sm btn-light disabled">‹</span>
                    @else
                    <a href="{{ $pendaftar->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">‹</a>
                    @endif

                    @if ($pendaftar->hasMorePages())
                    <a href="{{ $pendaftar->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">›</a>
                    @else
                    <span class="btn btn-sm btn-light disabled">›</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
