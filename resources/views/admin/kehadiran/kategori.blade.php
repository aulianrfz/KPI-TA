@extends('layouts.apk')

@section('content')


<div class="container mt-4">
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('kehadiran.event', $event->id) }}" class="btn btn-outline-primary me-2">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h5 class="mb-0 fw-bold text-uppercase">Kategori Lomba</h5>
    </div>

    <div class="row justify-content-center mt-4">
        @foreach ($categories as $index => $category)
            <div class="col-md-3 mb-4" data-aos="zoom-in" data-aos-delay="{{ $index * 100 }}">
                <div class="card shadow-sm border-0 h-100">
                    <img src="{{ asset('images/event.jpeg') }}" class="img-fluid rounded-3" alt="Event Image">
                    <div class="card-body text-center">
                        <h6 class="fw-bold">{{ $category->nama_kategori }}</h6>
                        <a href="{{ route('kehadiran.mataLomba', $category->id) }}" class="btn btn-outline-primary w-100 mt-2">Pilih</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>


<!-- AOS JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        once: true
    });
</script>

@endsection
