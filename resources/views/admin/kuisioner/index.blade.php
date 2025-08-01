@extends('layouts.apk')

@section('title', 'Kuisioner')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h4 class="mb-0 fw-bold" style="color: #0367A6;">Kuisioner Event: {{ $event->nama_event }}</h4>
        <button class="btn btn-success btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalTambahKuisioner">
            <i class="bi bi-plus-circle"></i> Tambah
        </button>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm p-4 bg-light">
                    <h5 class="fw-bold mb-4 text-primary d-flex align-items-center">
                        <i class="bi bi-bar-chart-fill me-2"></i> Statistik Kuisioner
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <strong>Total Pendaftar:</strong> {{ $totalPendaftar }}
                                </li>
                                <li class="mb-2">
                                    <strong>Sudah Mengisi Kuisioner:</strong> {{ $jumlahJawaban }}
                                </li>
                                <li class="mb-2">
                                    <strong>Belum Mengisi Kuisioner:</strong> {{ $totalPendaftar - $jumlahJawaban }}
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <a href="{{ route('admin.kuisioner.export', $event->id) }}" class="btn btn-outline-success">
                                <i class="bi bi-download me-1"></i> Download Excel Hasil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Pertanyaan</th>
                        <th class="text-end" style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kuisioners as $index => $kuisioner)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $kuisioner->pertanyaan }}</td>
                            <td class="text-end">
                                <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#modalEditKuisioner{{ $kuisioner->id }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('admin.kuisioner.destroy', $kuisioner->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus pertanyaan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">
                                <i class="bi bi-exclamation-circle fs-4"></i>
                                <div>Belum ada pertanyaan untuk event ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah --}}
<div class="modal fade" id="modalTambahKuisioner" tabindex="-1" aria-labelledby="modalTambahKuisionerLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.kuisioner.store') }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="event_id" value="{{ $event->id }}">
            <div class="modal-header bg-success bg-opacity-10">
                <h5 class="modal-title fw-bold text-success">Tambah Pertanyaan Kuisioner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="pertanyaan" class="form-label">Pertanyaan</label>
                    <input type="text" name="pertanyaan" class="form-control" placeholder="Contoh: Bagaimana kualitas pelayanan?" required>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
@foreach($kuisioners as $kuisioner)
<div class="modal fade" id="modalEditKuisioner{{ $kuisioner->id }}" tabindex="-1" aria-labelledby="modalEditKuisionerLabel{{ $kuisioner->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.kuisioner.update', $kuisioner->id) }}" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header bg-warning bg-opacity-10">
                <h5 class="modal-title fw-bold text-warning">Edit Pertanyaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="pertanyaan" class="form-label">Pertanyaan</label>
                    <input type="text" name="pertanyaan" class="form-control" value="{{ $kuisioner->pertanyaan }}" required>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning">Update</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
