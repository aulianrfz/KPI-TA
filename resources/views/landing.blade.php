@extends('layouts.app')

@section('content')

<div class="container mt-4">
    <div class="card shadow-sm rounded-4 overflow-hidden">
        <div id="bannerCarousel" class="carousel slide" data-aos="fade-up" data-bs-ride="carousel" data-bs-interval="2500">
            <div class="carousel-inner">
                @forelse($events as $index => $event)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ asset('storage/' . $event->foto) }}" class="d-block w-100 banner-img" alt="{{ $event->nama_event }}">
                    </div>
                @empty
                    <div class="carousel-item active">
                        <img src="{{ asset('images/default-banner.jpeg') }}" class="d-block w-100 banner-img" alt="Default Banner">
                        <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded-3 p-2">
                            <h5>Tidak ada event aktif</h5>
                            <p class="mb-0 small">Silakan cek kembali nanti.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="container mt-5">
    <div class="row g-2 g-md-4">
        @foreach($events ?? [] as $event)
            <div class="col-4 col-sm-3 col-md-4 col-lg-3 col-xl-3" data-aos="zoom-in-down" data-aos-delay="100">
                <a href="{{ route('event.show', $event->id) }}">
                    <div class="card event-card shadow-sm h-100 hover-shadow text-center">
                        <img src="{{ $event->foto ? asset('storage/' . $event->foto) : asset('images/event.jpeg') }}" class="card-img-top" alt="Event Image">
                        <div class="card-body px-1 py-2">
                            <h6 class="card-title fw-bold mb-1">{{ Str::limit($event->nama_event, 20) }}</h6>
                            <p class="card-text"><small>{{ Str::limit($event->penyelenggara, 18) }}</small></p>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        once: true
    });
</script>

<style>
    .banner-img {
        height: 300px;
        object-fit: cover;
    }

    @media (max-width: 576px) {
        .banner-img {
            height: 180px;
        }
    }

    .event-card {
        border-radius: 0.75rem;
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    .event-card img {
        height: 100px;
        object-fit: cover;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }

    .event-card .card-title {
        font-size: 0.75rem;
        margin-bottom: 0.25rem;
    }

    .event-card .card-text {
        font-size: 0.65rem;
        color: #6c757d;
    }

    .hover-shadow:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }

    @media (max-width: 576px) {
        .event-card .card-body {
            padding: 0.25rem 0.5rem;
        }
    }
</style>

@endsection
