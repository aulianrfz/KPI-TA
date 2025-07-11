@extends('layouts.apk')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-3">Tambah Kuisioner untuk Event: {{ $event->nama_event }}</h4>

    <form method="POST" action="{{ route('admin.kuisioner.store') }}">
        @csrf
        <input type="hidden" name="event_id" value="{{ $event->id }}">

        <div class="mb-3">
            <label for="pertanyaan" class="form-label">Pertanyaan</label>
            <input type="text" name="pertanyaan" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('kuisioner.by-event', $event->id) }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
