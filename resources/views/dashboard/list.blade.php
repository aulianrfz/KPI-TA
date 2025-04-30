@extends('layouts.app')

@section('content')

@include('layouts.navbar')

<div class="container-fluid mt-4">
    <div class="row">
        {{-- Sidebar --}}
        <div class="col-md-2 d-none d-md-block bg-light border-end" style="min-height: 100vh;">
            <ul class="nav flex-column mt-4 p-3">
                <li class="nav-item mb-3">
                    <a href="{{ route('dashboard') }}" class="nav-link text-primary">
                        <i class="bi bi-person-circle me-2"></i> My Categories
                    </a>
                </li>
                <li class="nav-item mb-3">
                    <a href="{{ route('pembayaran.index') }}" class="nav-link text-dark">
                        <i class="bi bi-wallet2 me-2"></i> Pembayaran
                    </a>
                </li>
            </ul>
        </div>

        {{-- Main Content --}}
        <div class="col-md-10 px-4 pb-4">
            <h4 class="fw-bold mt-4 mb-4">Sub Kategori yang Kamu Ikuti</h4>
            <div class="row">
                @forelse ($pesertaList as $peserta)
                    @php $sub = $peserta->subKategori; @endphp
                    @if ($sub)
                    <div class="col-lg-3 col-md-3 col-sm-8 mb-9">
                        <div class="card shadow-sm border-light rounded h-100">
                            <img src="{{ asset('storage/' . $sub->foto_kompetisi) }}" class="card-img-top" style="height: 180px; object-fit: cover;" alt="Event Image">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold">{{ $sub->name_lomba }}</h5>
                                <div class="text-muted mb-2">
                                    <i class="bi bi-currency-dollar me-2 text-success"></i>
                                    <small>Biaya: Rp{{ number_format($sub->biaya_pendaftaran, 0, ',', '.') }}</small>
                                </div>
                                <a href="{{ asset('storage/' . $sub->url_tor) }}" class="btn btn-sm btn-outline-primary mt-auto" target="_blank">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Lihat TOR
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                @empty
                    <div class="col-12">
                        <p class="text-muted">Kamu belum mendaftar pada sub kategori manapun.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

@endsection
