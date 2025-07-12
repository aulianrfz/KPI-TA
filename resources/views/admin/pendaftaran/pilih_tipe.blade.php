@extends('layouts.apk')

@section('title', 'Pilih Pendaftar')

@section('content')
    <h4 class="fw-bold mb-4" style="color: #0367A6;">
        Pilih Jenis Pendaftar untuk Event: <span class="text">{{ $eventData->nama_event }}</span>
    </h4>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-4 text-center h-100">
                <h5 class="fw-bold mb-3 text-uppercase text-secondary">Peserta</h5>
                <a href="{{ route('admin.pendaftaran.peserta', $eventData->id) }}" class="btn btn-primary px-4">
                    PILIH
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-4 text-center h-100">
                <h5 class="fw-bold mb-3 text-uppercase text-secondary">Pendamping</h5>
                <a href="{{ route('admin.pendaftaran.pendamping', $eventData->id) }}" class="btn btn-primary px-4">
                    PILIH
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-4 text-center h-100">
                <h5 class="fw-bold mb-3 text-uppercase text-secondary">Supporter</h5>
                <a href="{{ route('admin.pendaftaran.supporter', $eventData->id) }}" class="btn btn-primary px-4">
                    PILIH
                </a>
            </div>
        </div>
    </div>
@endsection
