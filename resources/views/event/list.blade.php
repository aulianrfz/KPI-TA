@extends('layouts.app')

@section('content')

@include('layouts.navbar')

<!-- PILIHAN EVENTS -->
<div class="container mt-4">
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('event.show', 1) }}" class="me-2"><i class="bi bi-arrow-left"></i></a>
        <h5 class="mb-0">PILIHAN EVENTS</h5>
    </div>

    <div class="row align-items-start">
        <!-- Deatail Event -->
        <div class="col-md-4">
            <img src="{{ asset('images/event.jpeg') }}" class="img-fluid rounded-3" alt="Event Image">
        </div>

        <div class="col-md-8">
            <h5 class="fw-bold">KOMPETISI PARIWISATA INDONESIA 14</h5>
            <div class="d-flex align-items-center text-muted mb-2">
                <i class="bi bi-calendar-event me-2 text-primary"></i>
                <small>July 15 - 17, 2025 • 08.00 - 17.00 WIB</small>
            </div>
            <div class="d-flex align-items-center text-muted mb-3">
                <i class="bi bi-geo-alt-fill me-2 text-primary"></i>
                <small>Politeknik Negeri Bandung</small>
            </div>
            <p style="text-align: justify;">
            Kompetisi Pariwisata Indonesia (KPI) merupakan salah satu event kompetisi
            pariwisata yang mulai bertaraf internasional yang diselenggarakan setiap tahunnya oleh
            Program Studi D3 Usaha Perjalanan Wisata. Kopetisi ini telah dianakan sebanyak 14 kali. </p>
        </div>
    </div>

    <!-- Categories -->
    <div class="text-center mt-5">
        <h3 class="fw-bold">CATEGORIES</h3>
        <hr class="mx-auto" style="width: 100px; border-top: 2px solid #000;">
    </div>

    <div class="row justify-content-center mt-4">
        @foreach ($categories as $category) 
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm border-0">
                <!-- <img src="{{ asset('images/' . $category->image) }}" class="card-img-top" alt="{{ $category->name }}"> -->
                <img src="{{ asset('images/event.jpeg') }}" class="img-fluid rounded-3" alt="Event Image">
                <div class="card-body text-center">
                        <h6 class="fw-bold">{{ $category->name }}</h6>
                        <a href="{{ route('event.showCategory', $category->id) }}" class="btn btn-outline-primary w-100 mt-2">Pilih</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>


@include('layouts.footer')

@endsection
