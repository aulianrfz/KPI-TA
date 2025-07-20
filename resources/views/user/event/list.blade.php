@extends('layouts.app')

@section('content')

{{-- Responsive Style --}}
<style>
    .event-wrapper {
        display: flex;
        flex-wrap: nowrap;
        gap: 1rem;
        align-items: flex-start;
        margin-bottom: 2rem;
    }

    .event-img {
        width: 280px;
        height: 180px;
        object-fit: cover;
        border-radius: 12px;
        flex-shrink: 0;
    }

    .event-details {
        padding-left: 10px;
        flex: 1;
    }

    .event-details h5 {
        font-size: 1.4rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .event-details small,
    .event-details p {
        font-size: 0.95rem;
    }

    .event-details p {
        text-align: justify;
    }

    /* Category Card Styles */
    .category-card img {
        height: 120px;
        object-fit: cover;
    }

    .category-card h6 {
        font-size: 0.95rem;
    }

    .category-card .btn {
        font-size: 0.8rem;
        padding: 4px 6px;
    }

    @media (max-width: 768px) {
        .event-wrapper {
            gap: 0.75rem;
        }

        .event-img {
            width: 140px;
            height: 100px;
        }

        .event-details {
            padding-left: 8px;
        }

        .event-details h5 {
            font-size: 1rem;
        }

        .event-details small,
        .event-details p,
        .event-title,
        .categories-title,
        .btn-back {
            font-size: 13px !important;
        }

        .category-card img {
            height: 100px !important;
        }

        .category-card h6 {
            font-size: 12px !important;
        }

        .category-card .btn {
            font-size: 11px !important;
        }

        .category-card .card-body {
            padding: 0.5rem;
        }
    }
</style>

<div class="container mt-4">
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('event.show', $event->id) }}" class="btn btn-outline-primary btn-sm me-2 btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h5 class="mb-0 fw-bold text-uppercase event-title">Pilihan Events</h5>
    </div>

    <div class="event-wrapper" data-aos="fade-up">
        <img src="{{ asset('images/event.jpeg') }}" class="event-img" alt="Event Image">
        <div class="event-details">
            <h5>{{ $event->nama_event }}</h5>
            @php
                use Carbon\Carbon;
                $start = Carbon::parse($event->tanggal);
                $end = Carbon::parse($event->tanggal_akhir);
                $tanggalFormatted = $start->month === $end->month && $start->year === $end->year
                    ? $start->day . '–' . $end->day . ' ' . $start->translatedFormat('F Y')
                    : $start->translatedFormat('d F Y') . ' – ' . $end->translatedFormat('d F Y');
            @endphp
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-calendar-date text-primary me-2"></i>
                <small>{{ $tanggalFormatted }}</small>
            </div>
            <div class="d-flex align-items-center text-muted mb-2">
                <i class="bi bi-geo-alt-fill me-2 text-primary"></i>
                <small>{{ $event->penyelenggara ?? 'Lokasi tidak tersedia' }}</small>
            </div>
            <p>{{ $event->deskripsi ?? 'Deskripsi event belum tersedia.' }}</p>
        </div>
    </div>

    <div class="text-center mt-5" data-aos="fade-up">
        <h3 class="fw-bold categories-title">CATEGORIES</h3>
        <hr class="mx-auto" style="width: 90%; max-width: 700px; border-top: 2px solid #000;">
    </div>

    <div class="row row-cols-3 row-cols-md-3 g-3 g-md-4 justify-content-center mt-3">
        @foreach ($categories as $index => $category)
            <div class="col category-card" data-aos="zoom-in" data-aos-delay="{{ $index * 100 }}">
                <div class="card shadow-sm border-0 h-100 text-center">
                    <img src="{{ asset('images/event.jpeg') }}"
                         class="img-fluid rounded-top"
                         alt="Event Image">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">{{ $category->nama_kategori }}</h6>
                        <a href="{{ route('event.showCategory', $category->id) }}"
                           class="btn btn-outline-primary btn-sm w-100">Pilih</a>
                    </div>
                </div>
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
@endsection
