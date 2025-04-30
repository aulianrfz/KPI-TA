@extends('layouts.app')

@section('content')

@include('layouts.navbar')

<!-- HEADER -->
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center mb-3">
            <!-- <a href="{{ url()->previous() }}" class="me-3 text-dark fs-4"><i class="bi bi-arrow-left"></i></a> -->
            <a href="{{ route('event.list', 1) }}" class="me-2 text-dark"><i class="bi bi-arrow-left"></i></a>
            <h5 class="mb-0 fw-bold">GENERAL COMPETITION</h5>
        </div>
    </div>

    <div class="row">
        <!-- FILTER -->
        <div class="col-md-3">
            <h6 class="fw-bold">Filters</h6>
            <hr>
            <div class="mb-3">
                <label class="form-label">Categories</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="team">
                    <label class="form-check-label" for="team">Team</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="individual">
                    <label class="form-check-label" for="individual">Individual</label>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Price</label>
                <input type="range" class="form-range" min="10000" max="500000" step="10000">
                <div class="d-flex justify-content-between">
                    <small>Rp. 10.000</small>
                    <small>Rp. 500.000</small>
                </div>
            </div>
        </div>

        <!-- CARD LIST -->
        <div class="col-md-9">
            <div class="row">
            @foreach ($events as $event)
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="{{ asset('storage/' . $event->foto_kompetisi) }}" class="card-img-top" style="height: 180px; object-fit: cover;" alt="{{ $event->nama_subKategori }}">
                        <div class="card-body text-center">
                            <h6 class="fw-bold">{{ $event->name_lomba }}</h6>
                            <small class="text-muted">Rp. {{ number_format($event->biaya_pendaftaran, 2) }} / {{ $event->jurusan }}</small>
                            <a href="{{ route('event.detail', $event->id) }}" class="btn btn-primary btn-sm w-100 mt-2">Pilih</a>
                        </div>
                    </div>
                </div>
            @endforeach

            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

@endsection
