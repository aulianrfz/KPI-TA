@extends('layouts.apk')

@section('title', 'Pilih Event')

@section('content')
<div class="container-fluid py-4">
    <h4 class="fw-bold mb-4 text" style="color: #0367A6;">
        <i class="bi bi-award-fill me-2"></i> Pilih Event untuk Sertifikat
    </h4>

    <div class="row g-4">
        @forelse ($events as $event)
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="fw-bold text-dark mb-2">
                            <i class="bi bi-calendar-event me-2 text-primary"></i>{{ $event->nama_event }}
                        </h5>

                        <p class="mb-3">
                            <span class="text-muted">Status Template:</span>
                            @if($event->sertifikatTemplate)
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                                    <i class="bi bi-check-circle-fill me-1"></i> Sudah
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2">
                                    <i class="bi bi-x-circle-fill me-1"></i> Belum
                                </span>
                            @endif
                        </p>

                        <a href="{{ route('sertifikat.pesertaByEvent', $event->id) }}" class="btn btn-outline-primary mt-auto w-100">
                            <i class="bi bi-gear-fill me-1"></i> Kelola Sertifikat
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-circle me-1"></i> Belum ada event yang tersedia.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
