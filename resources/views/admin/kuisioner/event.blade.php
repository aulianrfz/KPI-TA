@extends('layouts.apk')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-3" style="color: #0367A6" >Pilih Event</h4>

    <form action="" method="GET">
        <div class="mb-3">
            <label for="event_id" style="color: #0367A6" class="form-label">Event</label>
            <select class="form-select" onchange="if(this.value) window.location.href=this.value;">
                <option value="">-- Pilih Event --</option>
                @foreach($events as $event)
                    <option value="{{ route('kuisioner.by-event', $event->id) }}">
                        {{ $event->nama_event }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>
</div>
@endsection
