@extends('layouts.app')

@section('content')


<div class="container mt-4">
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('event.show', $event->id) }}" class="me-2"><i class="bi bi-arrow-left"></i></a>
        <h5 class="mb-0">PILIHAN EVENTS</h5>
    </div>

    <div class="row align-items-start">
        <div class="col-md-4" data-aos="fade-down">
            <img src="{{ asset('images/event.jpeg') }}" class="img-fluid rounded-3" alt="Event Image">
        </div>

        <div class="col-md-8" data-aos="fade-left" data-aos-delay="100">
            <h5 class="fw-bold">{{ $event->nama_event }}</h5>
            <div class="d-flex align-items-center text-muted mb-3">
                <i class="bi bi-geo-alt-fill me-2 text-primary"></i>
                <small>{{ $event->lokasi ?? 'Lokasi tidak tersedia' }}</small>
            </div>
            <p style="text-align: justify;">
                {{ $event->deskripsi ?? 'Deskripsi event belum tersedia.' }}
            </p>
        </div>
    </div>

    <div class="text-center mt-5" data-aos="fade-up">
        <h3 class="fw-bold">CATEGORIES</h3>
        <hr class="mx-auto" style="width: 700px; border-top: 2px solid #000;">
    </div>

    <div class="row justify-content-center mt-4">
        @foreach ($categories as $index => $category)
            <div class="col-md-3 mb-4" data-aos="zoom-in" data-aos-delay="{{ $index * 100 }}">
                <div class="card shadow-sm border-0 h-100">
                    <img src="{{ asset('images/event.jpeg') }}" class="img-fluid rounded-3" alt="Event Image">
                    <div class="card-body text-center">
                        <h6 class="fw-bold">{{ $category->nama_kategori }}</h6>
                        <a href="{{ route('event.showCategory', $category->id) }}" class="btn btn-outline-primary w-100 mt-2">Pilih</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@include('layouts.footer')

<!-- AOS JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        once: true
    });
</script>

@endsection
