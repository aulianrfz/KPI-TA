@extends('layouts.app')

@section('content')

<div class="container mt-4">
    <div class="card shadow-sm rounded-4 overflow-hidden">
        <div id="bannerCarousel" class="carousel slide" data-aos="fade-up" data-bs-ride="carousel" data-bs-interval="2000">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="{{ asset('images/banner1.jpeg') }}" class="d-block w-100" style="height: 300px; object-fit: cover;" alt="Banner 1">
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/banner2.jpeg') }}" class="d-block w-100" style="height: 300px; object-fit: cover;" alt="Banner 2">
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/banner3.jpeg') }}" class="d-block w-100" style="height: 300px; object-fit: cover;" alt="Banner 3">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold" data-aos="zoom-in" style="color: #0367A6;">Upcoming Events</h4>
        <a href="#" class="text-primary">View All ></a>
    </div>
    <div class="row">
    @foreach($events ?? [] as $event)
        <div class="col-md-4 mb-4" data-aos="zoom-in-down" data-aos-delay="100">
            <a href="{{ route('event.show', $event->id) }}">
                <div class="card shadow-sm h-100 hover-shadow">
                    <img src="{{ $event->foto ? asset('storage/' . $event->foto) : asset('images/event.jpeg') }}" class="card-img-top" style="height: 180px; object-fit: cover;" alt="Event Image">
                    <div class="card-body">
                        <h6 class="card-title fw-bold">{{ $event->nama_event }}</h6>
                        <p class="card-text text-muted"><small>{{ $event->penyelenggara }}</small></p>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>

@include('layouts.footer')

<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        once: true
    });
</script>

<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        transition: all 0.3s ease-in-out;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }
</style>

@endsection
