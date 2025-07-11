@extends('layouts.apk')

@section('title', 'Peserta Sertifikat')

@section('content')
<div class="container-fluid py-4">
    <h4 class="fw-bold text-primary mb-4">Peserta Sertifikat - {{ $event->nama_event }}</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
            <span>Template Sertifikat</span>
            <div>
                <a href="{{ route('sertifikat.uploadForm', $event->id) }}" class="btn btn-sm btn-outline-primary me-2">
                    <i class="bi bi-upload"></i> Upload Ulang
                </a>
                <a href="{{ route('sertifikat.atur', $event->id) }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-crop"></i> Atur Posisi
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($event->sertifikatTemplate)
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <img src="{{ asset('storage/' . $event->sertifikatTemplate->nama_file) }}" alt="Template" class="img-fluid rounded shadow-sm">
                    </div>
                    <div class="col-md-8">
                        <p class="mb-2 fw-semibold">Posisi Nama:</p>
                        <p class="mb-0">X: <strong>{{ $event->sertifikatTemplate->posisi_x }}</strong>, Y: <strong>{{ $event->sertifikatTemplate->posisi_y }}</strong></p>
                    </div>
                </div>
            @else
                <p class="text-danger">Template belum tersedia.</p>
            @endif
        </div>
    </div>

    {{-- Daftar Peserta --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light fw-bold">Daftar Peserta</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Peserta</th>
                            <th>Email</th>
                            <th>Status Sertifikat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pesertas as $peserta)
                            <tr>
                                <td>{{ $peserta->nama }}</td>
                                <td>{{ $peserta->email }}</td>
                                <td>
                                    @if($peserta->sertifikat_generated)
                                        <span class="badge bg-success">✅ Sudah</span>
                                    @else
                                        <span class="badge bg-danger">❌ Belum</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('sertifikat.generateSingle', $peserta->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-download me-1"></i> Generate
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Tidak ada peserta yang tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ route('sertifikat.index') }}" class="btn btn-secondary mt-3">← Kembali</a>
        </div>
    </div>
</div>
@endsection
