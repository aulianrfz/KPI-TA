@extends('layouts.app')

@section('content')

<div class="container my-5">
    <!-- <div class="d-flex align-items-center mb-4">
    <a href="{{ url()->previous() }}" class="me-3 text-dark fs-4"><i class="bi bi-arrow-left"></i></a>
    </div> -->

    <div class="row g-4 align-items-stretch">
            <div class="h-100 d-flex align-items-center justify-content-center" style="background-color: #f9f9f9;">
                <img src="{{ asset('storage/' . $events->foto_kompetisi) }}" alt="{{ $events->nama_lomba }}" class="img-fluid rounded" style="max-height: 100%; object-fit: cover; width: 100%;">
            </div>
        <div class="col-12 mt-4 text-center">
            <h3 class="fw-bold mb-3">{{ $events->nama_lomba }}</h3>
            <hr class="mx-auto" style="width: 500px; border-top: 2px solid #000;">
            <p class="text-muted" style="font-size: 1.1rem; text-align: center;">{{ $events->deskripsi }}</p>
        </div>
        <div>
            <p class="text-muted mb-2"><strong>Jurusan:</strong> {{ $events->jurusan }}</p>
            <p class="text-muted mb-2"><strong>Minimal Peserta:</strong> {{ $events->min_peserta }}</p>
            <p class="text-muted mb-2"><strong>Maksimal Peserta:</strong> {{ $events->maks_peserta }}</p>
            <p class="text-muted mb-2"><strong>Biaya Pendaftaran:</strong> {{ number_format($events->biaya_pendaftaran, 0, ',', '.') }}</p>
        </div>
         <div class="col-12 mt-4 text-center">
            <p class="text-muted mb-2" style="font-size: 1.1rem;"><strong>URL TOR:</strong> 
                <a href="{{ $events->url_tor }}" target="_blank" style="color: #0367A6; text-decoration: underline;">Klik Disini</a>
            </p>
        </div>
    </div>
    @php
        $kuotaPenuh = $total_pendaftar >= $events->maks_total_peserta;
    @endphp

    @if ($kuotaPenuh)
        <button class="btn mt-3 w-100" style="background-color: #ccc; color: #666; height: 50px; border-radius: 10px; font-weight: bold;" disabled>
            Kuota Pendaftaran Sudah Penuh
        </button>
    @else
        <a href="{{ route('pendaftaran.form', ['id_mataLomba' => $events->id]) }}" 
           class="btn mt-3 w-100" 
           style="background-color: #2CC384; color: white; height: 50px; border-radius: 10px; font-weight: bold; text-transform: uppercase; border: none; transition: background-color 0.3s ease;">
            Daftar Sekarang
        </a>
    @endif
</div>


       
@include('layouts.footer')

@endsection
