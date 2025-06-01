@extends('layouts.apk')

@section('content')

<div class="container mt-5">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('kehadiran.kategori',1) }}" class="btn btn-outline-primary me-2">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold text-uppercase">Mata Lomba</h4>
    </div>

    <div class="row">
        <div class="col-md-9 mx-auto">
            <div class="row justify-content-center">
                @forelse ($events as $event)
                    <div class="col-md-4 mb-4 d-flex">
                        <div class="card border-0 shadow-sm h-100 w-100">
                            <img src="{{ asset('storage/' . $event->foto_kompetisi) }}" class="card-img-top"
                                style="height: 180px; object-fit: cover;" alt="{{ $event->nama_mataLomba }}">
                            <div class="card-body text-center">
                                <h6 class="fw-bold">{{ $event->nama_lomba }}</h6>
                                <small class="text-muted">Rp. {{ number_format($event->biaya_pendaftaran, 2) }} / {{ $event->jurusan }}</small>
                                <a href="{{ route('kehadiran.mata-lomba', $event->id) }}" class="btn btn-primary btn-sm w-100 mt-2">Pilih</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-center text-muted">Tidak ada mata lomba yang sesuai filter.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('filterForm');
        const checkboxes = form.querySelectorAll('input[type="checkbox"]');
        const range = form.querySelector('input[type="range"]');

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => form.submit());
        });

        range.addEventListener('change', () => form.submit());
    });
</script>

@endsection
