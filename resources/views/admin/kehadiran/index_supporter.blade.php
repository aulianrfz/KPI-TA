@extends('layouts.apk')

@section('title', 'Kehadiran Supporter')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-uppercase">Kehadiran Supporter</h4>
    <a href="{{ route('kehadiran.pilih-jenis.event', $eventId) }}" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<form method="GET" class="mb-4">
    <div class="row g-3 align-items-center">
        <div class="col-md-5 col-lg-4">
            <input type="text" name="search" value="{{ request('search') }}"
                class="form-control" placeholder="Cari nama atau instansi...">
        </div>
        <div class="col-md-3 col-lg-2">
            <select name="sort" class="form-select">
                <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Terlama</option>
            </select>
        </div>
        <div class="col-auto d-flex gap-2">
            <button class="btn btn-primary" type="submit">
                <i class="bi bi-search"></i> Filter
            </button>
            <a href="{{ route('kehadiran.jenis', [$eventId, 'supporter']) }}" class="btn btn-secondary">
                Reset
            </a>
        </div>
    </div>
</form>

<div class="row mb-4">
    <div class="col-12">
        <div class="card rounded-4 h-100 shadow-sm">
            <div class="card-body d-flex flex-column justify-content-between p-4" style="min-height: 130px;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-semibold mb-0" style="color: #0ea5e9;">Total Supporter</h6>
                    <i class="bi bi-people-fill fs-3" style="color: #0ea5e9;"></i>
                </div>
                <div class="fs-1 fw-bold">{{ $totalSupporter }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Instansi</th>
                        <th>Email</th>
                        <th>Waktu</th>
                        <th>QR Code</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendaftar as $i => $p)
                        <tr>
                            <td>{{ $i + 1 + ($pendaftar->currentPage()-1)*$pendaftar->perPage() }}</td>
                            <td>{{ $p->supporter->nama ?? '-' }}</td>
                            <td>{{ $p->supporter->instansi ?? '-' }}</td>
                            <td>{{ $p->supporter->email ?? '-' }}</td>
                            <td>{{ optional($p->tanggal_kehadiran)->format('H:i') ?? '-' }}</td>
                            <td><img src="{{ asset($p->url_qrCode) }}" width="50" alt="QR"></td>
                            <td>
                                <span class="badge {{ $p->status_kehadiran === 'Hadir' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $p->status_kehadiran ?? 'Belum Hadir' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted">Data tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $pendaftar->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
