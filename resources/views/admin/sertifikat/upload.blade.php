@extends('layouts.apk')
@section('content')
<div class="container my-5">
    <h3 class="fw-bold mb-4">Upload Template Sertifikat - {{ $event->nama_event }}</h3>

    <form method="POST" action="{{ route('sertifikat.upload', $event->id) }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label">Pilih Gambar Template</label>
            <input type="file" name="nama_file" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</div>
@endsection
