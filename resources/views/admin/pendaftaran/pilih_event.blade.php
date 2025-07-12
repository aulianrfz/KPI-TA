@extends('layouts.apk')

@section('title', 'Pilih Event')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4" style="color: #0367A6;">Pilih Event</h4>
    <div class="row">
        @foreach ($events as $event)
            <div class="col-md-4 mb-3">
                <div class="card p-3 shadow-sm border-0 h-100">
                    <h5 class="fw-bold">{{ $event->nama_event }}</h5>
                    <a href="{{ route('pendaftaran.pilih-tipe', $event->id) }}" class="btn btn-primary mt-3">Pilih</a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
