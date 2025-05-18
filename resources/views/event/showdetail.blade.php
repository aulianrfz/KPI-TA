@extends('layouts.app')

@section('content')

@include('layouts.navbar')

<div class="container my-5">
    <!-- <div class="d-flex align-items-center mb-4">
    <a href="{{ url()->previous() }}" class="me-3 text-dark fs-4"><i class="bi bi-arrow-left"></i></a>
    </div> -->

    <div class="row g-4 align-items-stretch">
            <div class="h-100 d-flex align-items-center justify-content-center" style="background-color: #f9f9f9;">
                <img src="{{ asset('storage/' . $event->foto_kompetisi) }}" alt="{{ $event->nama_lomba }}" class="img-fluid rounded" style="max-height: 100%; object-fit: cover; width: 100%;">
            </div>
        <div class="col-12 mt-4 text-center">
            <h3 class="fw-bold mb-3">{{ $event->nama_lomba }}</h3>
            <hr class="mx-auto" style="width: 500px; border-top: 2px solid #000;">
            <p class="text-muted" style="font-size: 1.1rem; text-align: center;">{{ $event->deskripsi }}</p>
        </div>
        <div>
            <p class="text-muted mb-2"><strong>Jurusan:</strong> {{ $event->jurusan }}</p>
            <p class="text-muted mb-2"><strong>Maks Peserta:</strong> {{ $event->maks_peserta }}</p>
            <p class="text-muted mb-2"><strong>Biaya Pendaftaran:</strong> {{ number_format($event->biaya_pendaftaran, 0, ',', '.') }}</p>
        </div>
         <div class="col-12 mt-4 text-center">
            <p class="text-muted mb-2" style="font-size: 1.1rem;"><strong>URL TOR:</strong> 
                <a href="{{ $event->url_tor }}" target="_blank" style="color: #0367A6; text-decoration: underline;">Klik Disini</a>
            </p>
        </div>
    </div>
    <a href="{{ route('pendaftaran.form', ['id_subkategori' => $event->id]) }}" class="btn mt-3 w-100" style="background-color: #2CC384; color: white; height: 50px; border-radius: 10px; font-weight: bold; text-transform: uppercase; border: none; transition: background-color 0.3s ease;">Daftar Sekarang</a>
</div>


       
@include('layouts.footer')

@endsection
