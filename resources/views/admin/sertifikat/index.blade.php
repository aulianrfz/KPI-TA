@extends('layouts.apk')

@section('content')
<div class="container my-5">
    <h3 class="fw-bold mb-4">Generate Sertifikat</h3>

    <form method="GET" action="">
        <div class="mb-3">
            <label class="form-label">Pilih Event</label>
            <select class="form-select" onchange="location = this.value">
                <option value="">-- Pilih Event --</option>
                @foreach($events as $event)
                    <option value="{{ route('sertifikat.pesertaByEvent', $event->id) }}">
                        {{ $event->nama_event }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>
</div>
@endsection
