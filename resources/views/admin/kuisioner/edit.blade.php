@extends('layouts.apk')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-3">Edit Kuisioner</h4>

    <form method="POST" action="{{ route('admin.kuisioner.update', $kuisioner->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="pertanyaan" class="form-label">Pertanyaan</label>
            <input type="text" name="pertanyaan" class="form-control" value="{{ $kuisioner->pertanyaan }}" required>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('kuisioner.by-event', $kuisioner->event_id) }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
