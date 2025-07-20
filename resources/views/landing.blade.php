@extends('layouts.app')

@section('content')

<div class="container mt-4">
    <div class="card shadow-sm rounded-4 overflow-hidden">
        <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000">
            <div class="carousel-inner">
                @forelse($events as $index => $event)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ asset('storage/' . $event->foto) }}" 
                             class="d-block w-100 banner-img" 
                             alt="{{ $event->nama_event }}">
                    </div>
                @empty
                    <div class="carousel-item active">
                        <img src="{{ asset('images/default-banner.jpeg') }}" 
                             class="d-block w-100 banner-img" 
                             alt="Default Banner">
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
    <div class="row g-3">
        @foreach($events ?? [] as $event)
            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                <a href="{{ route('event.show', $event->id) }}">
                    <div class="card shadow-sm h-100 hover-shadow">
                        <img src="{{ $event->foto ? asset('storage/' . $event->foto) : asset('images/event.jpeg') }}" 
                             class="card-img-top event-img" 
                             alt="Event Image">
                        <div class="card-body py-3 px-3">
                            <h6 class="card-title fw-bold mb-1">{{ $event->nama_event }}</h6>
                            <p class="card-text text-muted"><small>{{ $event->penyelenggara }}</small></p>
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

    .event-img {
        height: 180px;
        object-fit: cover;
    }

    .card-title {
        font-size: 1rem;
    }

    .card-text small {
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }

    .hover-shadow:hover {
        transform: translateY(-5px);
        transition: all 0.3s ease-in-out;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }

    @media (max-width: 768px) {
        .banner-img {
            height: 200px;
        }

        .event-img {
            height: 150px;
        }

        .card-title {
            font-size: 0.85rem;
        }

        .card-text small {
            font-size: 0.75rem;
        }
    }

    @media (max-width: 576px) {
        .banner-img {
            height: 180px;
        }

        .event-img {
            height: 130px;
        }

        .card-title {
            font-size: 0.8rem;
        }

        .card-text small {
            font-size: 0.7rem;
        }
    }
</style>

@endsection
