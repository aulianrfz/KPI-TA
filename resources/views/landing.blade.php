@extends('layouts.app')

@section('content')

@include('layouts.navbar')


<!-- Banner -->
<!-- <div class="container-fluid mt-5 px-5"> -->
<div class="container mt-4">
    <div class="card shadow-sm rounded-4 overflow-hidden">
        <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="{{ asset('images/banner1.jpeg') }}" class="d-block w-100" style="height: 300px; object-fit: cover;" alt="Banner 1">
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/banner2.jpeg') }}" class="d-block w-100" style="height: 300px; object-fit: cover;" alt="Banner 2">
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/banner3.jpeg') }}" class="d-block w-100" style="height: 300px; object-fit: cover;" alt="Banner 2">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Events -->
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold" style="color: #0367A6;">Upcoming Events</h4>
        <a href="#" class="text-primary">View All ></a>
    </div>
    <div class="row">
        <div class="col-md-4 mb-4">
            <a href="{{ route('event.show', 1) }}">
                <div class="card shadow-sm h-100">
                    <img src="{{ asset('images/event.jpeg') }}" class="card-img-top" style="height: 180px; object-fit: cover;" alt="Event Image">
                    <div class="card-body">
                        <h6 class="card-title fw-bold">Kompetisi Pariwisata Indonesia</h6>
                        <p class="card-text text-muted"><small>Dipusatkan di Bandung (POLBAN), Indonesia</small></p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

@include('layouts.footer')

@endsection
