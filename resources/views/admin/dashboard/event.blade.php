@extends('layouts.apk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold" data-aos="zoom-in" style="color: #0367A6;">Pilih Event untuk Lihat Peserta</h4>
</div>

<div class="row">
    @foreach($events as $event)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4" data-aos="zoom-in-down" data-aos-delay="100">
            <a href="{{ route('dashboard.by-event', $event->id) }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm hover-shadow border-0">
                    <img src="{{ $event->foto ? asset('storage/' . $event->foto) : asset('images/event.jpeg') }}" 
                         class="card-img-top" 
                         alt="{{ $event->nama_event }}" 
                         style="height: 180px; object-fit: cover;">
                    <div class="card-body">
                        <h6 class="card-title fw-bold text-dark mb-1">{{ $event->nama_event }}</h6>
                        <p class="card-text text-muted mb-0"><small>{{ $event->penyelenggara }}</small></p>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        once: true
    });
</script>

<style>
    .hover-shadow {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-shadow:hover {
        transform: translateY(-6px);
        box-shadow: 0 0.75rem 1.25rem rgba(0, 0, 0, 0.15);
    }

    .card-title {
        font-size: 1rem;
    }

    .card-text {
        font-size: 0.9rem;
    }
</style>
@endsection
