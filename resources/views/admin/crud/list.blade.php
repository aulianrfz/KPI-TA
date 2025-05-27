@extends('layouts.apk')

@section('content')
<div class="main-content flex-grow-1">
    <div class="container py-5">
        <h2 class="fw-bold mb-4 text-primary">Manajemen Data</h2>

        <div class="row g-4">
            <!-- Kategori -->
            <div class="col-md-4">
                <a href="{{ url('/kategori') }}" class="text-decoration-none">
                    <div class="card custom-card bg-gradient-kategori text-white h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-tags-fill display-5 mb-3"></i>
                            <h5 class="card-title fw-semibold">Kategori</h5>
                            <p class="card-text">Kelola kategori lomba</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Provinsi -->
            <div class="col-md-4">
                <a href="{{ url('/provinsi') }}" class="text-decoration-none">
                    <div class="card custom-card bg-gradient-provinsi text-white h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-geo-alt-fill display-5 mb-3"></i>
                            <h5 class="card-title fw-semibold">Provinsi</h5>
                            <p class="card-text">Kelola data wilayah</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Institusi -->
            <div class="col-md-4">
                <a href="{{ url('/institusi') }}" class="text-decoration-none">
                    <div class="card custom-card bg-gradient-institusi text-white h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-building-fill display-5 mb-3"></i>
                            <h5 class="card-title fw-semibold">Institusi</h5>
                            <p class="card-text">Kelola data institusi</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- List Event -->
            <div class="col-md-4">
                <a href="{{ url('/listevent') }}" class="text-decoration-none">
                    <div class="card custom-card bg-gradient-event text-white h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-event-fill display-5 mb-3"></i>
                            <h5 class="card-title fw-semibold">List Event</h5>
                            <p class="card-text">Data event lomba</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
