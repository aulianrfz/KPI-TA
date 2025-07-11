@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card shadow rounded-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0"  style="color: #0367A6">Daftar Pengajuan</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('pengajuan.retur') }}" class="btn btn-outline-info shadow-sm">
                    <i class="bi bi-info-circle me-1"></i> Info S&K
                </a>
                <a href="{{ route('pengajuan.create') }}" class="btn btn-primary shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Tambah
                </a>
            </div>
        </div>

        <form method="GET" class="mb-4">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select name="filter" class="form-select" onchange="this.form.submit()">
                        <option value="">Filter berdasarkan jenis</option>
                        @foreach ($jenisList as $jenis)
                            <option value="{{ $jenis }}" {{ request('filter') == $jenis ? 'selected' : '' }}>
                                {{ $jenis }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle table-hover text-center">
                <thead class="table-light">
                    <tr>
                        <th class="text-start">Jenis Pengajuan</th>
                        <th class="text-start">Deskripsi</th>
                        <th>Status</th>
                        <th>Dibuat Pada</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengajuans as $pengajuan)
                        <tr>
                            <td class="text-start">{{ $pengajuan->jenis }}</td>
                            <td class="text-start">{{ $pengajuan->deskripsi ?? '-' }}</td>
                            <td>
                                @php
                                    $colorMap = [
                                        'Menunggu' => '#FFF6D1',
                                        'Disetujui' => '#D0F4FF',
                                        'Ditolak' => '#FFBABA',
                                    ];
                                    $statusColor = $colorMap[$pengajuan->status] ?? '#FFDFDF';
                                @endphp
                                <div class="d-flex justify-content-center">
                                    <span class="badge text-dark fw-semibold py-2 px-3 rounded-pill"
                                          style="background-color: {{ $statusColor }}; min-width: 120px;">
                                        {{ $pengajuan->status }}
                                    </span>
                                </div>
                            </td>
                            <td>{{ $pengajuan->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Belum ada pengajuan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pengajuans->hasPages())
        <div class="mt-4 d-flex justify-content-end">
            {{ $pengajuans->withQueryString()->links('vendor.pagination.bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection
