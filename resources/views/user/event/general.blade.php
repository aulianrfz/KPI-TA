@extends('layouts.app')

@section('content')

<div class="container mt-5">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('event.list',1) }}" class="btn btn-outline-primary me-2">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-bold text-uppercase">Daftar Kategori</h4>
    </div>

    <div class="row">
        <div class="col-md-3">
            <h6 class="fw-bold">Filters</h6>
            <hr>
            <form method="GET" action="{{ url()->current() }}" id="filterForm">
                <div class="mb-3">
                    <label class="form-label">Jenis Peserta</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="jenis_lomba[]" value="Kelompok" id="Kelompok"
                            {{ is_array(request()->jenis_lomba) && in_array('Kelompok', request()->jenis_lomba) ? 'checked' : '' }}>
                        <label class="form-check-label" for="Kelompok">Kelompok</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="jenis_lomba[]" value="Individu" id="individu"
                            {{ is_array(request()->jenis_lomba) && in_array('Individu', request()->jenis_lomba) ? 'checked' : '' }}>
                        <label class="form-check-label" for="individu">Individu</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga Maksimal</label>
                    <input type="range" class="form-range" name="biaya_pendaftaran" min="500000" max="1000000" step="100000"
                        value="{{ request()->biaya_pendaftaran ?? 500000 }}">
                    <div class="d-flex justify-content-between">
                        <small>≤Rp. 500.000</small>
                        <small>Rp. 1.000.000</small>
                    </div>
                </div>

            </form>
        </div>

        <div class="col-md-9">
            <div class="row">
                @forelse ($events as $event)
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <img src="{{ asset('storage/' . $event->foto_kompetisi) }}" class="card-img-top"
                                style="height: 180px; object-fit: cover;" alt="{{ $event->nama_mataLomba }}">
                            <div class="card-body text-center">
                                <h6 class="fw-bold">{{ $event->nama_lomba }}</h6>
                                <small class="text-muted">Rp. {{ number_format($event->biaya_pendaftaran, 2) }} / {{ $event->jurusan }}</small>
                                <a href="{{ route('event.detail', $event->id) }}" class="btn btn-primary btn-sm w-100 mt-2">Pilih</a>
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

@include('layouts.footer')

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
