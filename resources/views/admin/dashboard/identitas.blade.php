@extends('layouts.apk')

@section('content')
<div class="container py-4">

    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary mb-3">
        <i class="bi bi-arrow-left"></i> QR Attendance
    </a>

    <div class="text-center mb-5">
        <div class="bg-dark text-white p-4 rounded mx-auto" style="max-width: 100%; width: 100%; max-width: 600px;">
            <p class="mb-3 fs-5 fw-semibold">Scan your QR Code</p>
            @if ($pendaftar->url_qrCode)
                <img src="{{ asset($pendaftar->url_qrCode) }}" alt="QR Code" style="max-width: 200px; width: 100%;">
            @else
                <p class="text-light">QR Code tidak tersedia.</p>
            @endif
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 p-4">
        <h3 class="text-center mb-5" style="color: #252C58;"><strong>IDENTITAS PESERTA</strong></h3>

        <div class="row gy-4">
            <div class="col-12 col-md-4 text-center">
                @if ($pendaftar->peserta->url_ktm)
                    <img src="{{ asset('storage/' . $pendaftar->peserta->url_ktm) }}" 
                         alt="Foto Peserta" 
                         class="img-fluid rounded border mx-auto d-block"
                         style="width: 100%; max-width: 200px; height: 200px; object-fit: cover;">
                @else
                    <div class="bg-light border rounded p-4 text-center" style="height: 200px;">Tidak ada foto</div>
                @endif

                <div class="mt-3">
                    @if ($pendaftar->peserta->url_ttd)
                        <img src="{{ asset($pendaftar->peserta->url_ttd) }}" 
                             alt="Tanda Tangan" 
                             style="max-height: 50px; object-fit: contain;">
                    @else
                        <p class="text-muted">Tanda tangan tidak tersedia.</p>
                    @endif
                </div>
            </div>

            <div class="col-12 col-md-8 fw-semibold">
                <h5 class="fw-bold text-uppercase mb-1">{{ $pendaftar->peserta->nama_peserta ?? '-' }}</h5>
                <p class="mb-2 text-muted" style="letter-spacing: 0.3px;">
                    PESERTA LOMBA - {{ $pendaftar->mataLomba->nama_lomba ?? '-' }}
                </p>
                <hr class="my-2">

                <p class="mb-1"><strong>NIM:</strong> {{ $pendaftar->peserta->nim ?? '-' }}</p>
                <p class="mb-1"><strong>Nama Tim:</strong> {{ $pendaftar->tim->nama_tim ?? '-' }}</p>
                <p class="mb-1"><strong>Email:</strong> {{ $pendaftar->peserta->email ?? '-' }}</p>
                <p class="mb-1"><strong>No TLP:</strong> {{ $pendaftar->peserta->no_hp ?? '-' }}</p>
                <p class="mb-1"><strong>Institusi:</strong> {{ $pendaftar->peserta->institusi ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
