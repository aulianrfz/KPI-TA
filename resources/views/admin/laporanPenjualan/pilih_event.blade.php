@extends('layouts.apk')

@section('title', 'Pilih Event')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold text-primary mb-4">Pilih Event untuk Laporan Penjualan</h4>

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
</div>
@endsection
