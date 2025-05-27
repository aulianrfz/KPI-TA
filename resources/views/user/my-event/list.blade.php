@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 d-none d-md-block bg-light border-end p-3">
            <ul class="nav flex-column mt-4">
                <li class="nav-item mb-3">
                    <a href="{{ route('events.list') }}" class="nav-link text-primary">
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

        <!-- Main Content -->
        <div class="col-md-10">
            <h4 class="fw-bold mb-4">Event yang Kamu Ikuti</h4>
            <div class="row">
                @forelse ($groupedByEvent as $eventId => $pendaftars)
                    @php $event = $pendaftars->first()->mataLomba->kategori->event ?? null; @endphp
                    @if ($event)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card shadow-sm h-100 border-0">
                                <img src="{{ asset('storage/' . $event->foto) }}" class="card-img-top" style="height: 180px; object-fit: cover;" alt="Event Image">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">{{ $event->nama_event }}</h5>
                                    <p class="card-text text-muted mb-3">{{ $event->penyelenggara }}</p>
                                    <a href="{{ route('events.lomba.detail', $event->id) }}" class="btn btn-outline-primary mt-auto w-100">
                                        <i class="bi bi-list-task me-1"></i> Lihat Lomba
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            Belum ada event yang diikuti.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
@endsection
