@extends('layouts.apk')

@section('title', 'Pengajuan')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0" style="color: #0367A6" >Daftar Pengajuan</h5>
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
                <div class="col-md-6">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input
                            type="text"
                            name="search"
                            class="form-control border-start-0"
                            placeholder="Cari nama user..."
                            value="{{ request('search') }}"
                            onchange="this.form.submit()"
                        >
                    </div>
                </div>

                <div class="col-md-4 text-end">
                    <select name="filter" class="form-select shadow-sm" onchange="this.form.submit()">
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
            <table class="table table-hover align-middle shadow-sm">
                <thead class="table-light text-center">
                    <tr>
                        <th>No</th>
                        <th>Nama User</th>
                        <th>Waktu</th>
                        <th>Jenis</th>
                        <th>Detail</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $key => $pengajuan)
                        <tr>
                            <td class="text-center">{{ $pengajuans->firstItem() + $key }}</td>
                            <td>{{ $pengajuan->user->first_name }} {{ $pengajuan->user->last_name }}</td>
                            <td class="text-center">
                                {{ $pengajuan->created_at->format('d M Y') }}
                                <br><small class="text-muted">{{ $pengajuan->created_at->format('H:i A') }}</small>
                            </td>
                            <td class="text-center">{{ $pengajuan->jenis }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.pengajuan.show', $pengajuan->id) }}"
                                   class="btn btn-outline-primary btn-sm fw-semibold">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>
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
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle fs-3"></i>
                                <p class="mb-0">Belum ada pengajuan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($pengajuans->hasPages())
            <div class="d-flex justify-content-end mt-3">
                {{ $pengajuans->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
