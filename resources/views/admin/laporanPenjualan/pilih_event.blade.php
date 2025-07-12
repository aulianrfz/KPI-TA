@extends('layouts.apk')

@section('title', 'Pilih Event')

@section('content')
    <h4 class="fw-bold mb-4 text" style="color: #0367A6;">
        <i class="bi me-2"></i>Pilih Event untuk Laporan Penjualan
    </h4>
    <div class="row">
        @foreach($events as $event)
            <div class="col-md-4 mb-3">
                <div class="card p-3 shadow-sm border-0 h-100">
                    <h5 class="fw-bold mb-2">{{ $event->nama_event }}</h5>
                    <a href="{{ route('laporan.penjualan', $event->id) }}" class="btn btn-primary mt-3">
                        Lihat Laporan
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endsection
