@extends('layouts.apk')

@section('title', 'Pengajuan Admin')

@section('content')

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0" style="color: #0367A6">Daftar Pengajuan</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('pengajuan.retur') }}" class="btn btn-outline-info shadow-sm">
                <i class="bi bi-info-circle me-1"></i> Info S&K
            </a>
            <a href="{{ route('pengajuan.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Tambah
            </a>
        </div>
    </div>

    <div class="card-body px-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="GET" class="mb-4">
            <div class="row justify-content-between g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input
                            type="text"
                            name="search"
                            class="form-control border-start-0"
                            placeholder="Cari peserta/penyaji..."
                            value="{{ request('search') }}"
                            onchange="this.form.submit()"
                        >
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="filter" class="form-select shadow-sm" onchange="this.form.submit()">
                        <option value="">Filter berdasarkan jenis</option>
                        @foreach ($jenisList as $jenis)
                            <option value="{{ $jenis }}" {{ request('filter') === $jenis ? 'selected' : '' }}>
                                {{ $jenis }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle shadow-sm">
                <thead class="table-light text-center">
                    <tr>
                        <th>No</th>
                        <th>Peserta</th>
                        <th>Lomba</th>
                        <th>Event</th>
                        <th>Jenis</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $index => $pengajuan)
                        @php
                            $peserta = $pengajuan->peserta;
                            $lomba = optional($peserta->pendaftar->mataLomba);
                            $event = optional($lomba->kategori->event);
                        @endphp
                        <tr>
                            <td>{{ $pengajuans->firstItem() + $index }}</td>
                            <td class="text-start">{{ $peserta->nama_peserta }}</td>
                            <td class="text-start">{{ $lomba->nama_lomba ?? '-' }}</td>
                            <td class="text-start">{{ $event->nama_event ?? '-' }}</td>
                            <td>{{ $pengajuan->jenis }}</td>
                            <td>{{ $pengajuan->created_at->format('d M Y H:i') }}</td>
                            <td>
                                @php
                                    $colorMap = [
                                        'Menunggu' => '#FFF6D1',
                                        'Disetujui' => '#D0F4FF',
                                        'Ditolak' => '#FFBABA',
                                    ];
                                    $statusColor = $colorMap[$pengajuan->status] ?? '#FFDFDF';
                                @endphp
                                <span class="badge text-dark fw-semibold py-2 px-3 rounded-pill"
                                      style="background-color: {{ $statusColor }};">
                                    {{ $pengajuan->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <form method="POST" action="{{ route('admin.pengajuan.update', ['id' => $pengajuan->id, 'status' => 'Disetujui']) }}">
                                        @csrf @method('PUT')
                                        <button class="btn btn-sm fw-semibold btn-outline-success">
                                            <i class="bi bi-check-circle me-1"></i> Setujui
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.pengajuan.update', ['id' => $pengajuan->id, 'status' => 'Ditolak']) }}">
                                        @csrf @method('PUT')
                                        <button class="btn btn-sm fw-semibold btn-outline-danger">
                                            <i class="bi bi-x-circle me-1"></i> Tolak
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle fs-3"></i>
                                <p class="mb-0">Belum ada pengajuan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pengajuans->hasPages())
            <div class="d-flex justify-content-end mt-3">
                {{ $pengajuans->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
