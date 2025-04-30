@extends('layouts.app')

@section('content')

@include('layouts.navbar')

<!-- content -->
<div class="container my-5">
    <div class="d-flex align-items-center mb-4">
    <!-- backnya masi blom bnr -->
    <a href="{{ url()->previous() }}" class="me-3 text-dark fs-4"><i class="bi bi-arrow-left"></i></a>
    <h4 class="mb-0 fw-bold">{{ $event->name_lomba }}</h4>
    </div>

    <div class="row g-4 align-items-stretch">
        <div class="col-md-6">
            <div class="h-100 d-flex align-items-center justify-content-center" style="background-color: #f9f9f9;">
                <img src="{{ asset('storage/' . $event->foto_kompetisi) }}" alt="{{ $event->name_lomba }}" class="img-fluid rounded" style="max-height: 100%; object-fit: cover; width: 100%;">
            </div>
        </div>

        <div class="col-md-6">
            <div class="border rounded shadow-sm p-4 h-100" style="background-color: #ffffff;">
                <h3 class="fw-bold mb-3">{{ $event->name_lomba }}</h3>
                <div>
                    <p class="text-muted mb-2"><strong>Jurusan:</strong> {{ $event->jurusan }}</p>
                    <p class="text-muted mb-2"><strong>Maks Peserta:</strong> {{ $event->maks_peserta }}</p>
                    <p class="text-muted mb-2"><strong>Biaya Pendaftaran:</strong> {{ number_format($event->biaya_pendaftaran, 0, ',', '.') }}</p>
                    <p class="text-muted mb-2"><strong>URL TOR:</strong> <a href="{{ $event->url_tor }}" target="_blank" style="color: #0367A6;">Klik Disini</a></p>
                </div>
                <a href="{{ route('pendaftaran.form', ['id_subkategori' => $event->id]) }}" class="btn mt-3 w-100" style="background-color: #2CC384; color: white; height: 50px; border-radius: 10px; font-weight: bold; text-transform: uppercase; border: none; transition: background-color 0.3s ease;">Daftar Sekarang</a>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

@endsection
