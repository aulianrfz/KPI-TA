@extends('layouts.apk')

@section('content')
    <h4 class="fw-bold mb-3" style="color: #0367A6" >Pilih Event</h4>

    <form action="" method="GET">
        <div class="mb-3">
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
@endsection
