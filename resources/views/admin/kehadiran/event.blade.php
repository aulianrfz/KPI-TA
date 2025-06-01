@extends('layouts.apk')

@section('content')


<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold" data-aos="zoom-in" style="color: #0367A6;">Daftar Events</h4>
    </div>
    <div class="row">
    @foreach($events ?? [] as $event)
        <div class="col-md-4 mb-4" data-aos="zoom-in-down" data-aos-delay="100">
            <a href="{{ route('kehadiran.kategori', $event->id) }}">
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
