@extends('layouts.apk')

@section('content')
<div class="container mt-4">
    <h4>Edit Event</h4>

    <form action="{{ route('listevent.update', $listevent->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nama_event" class="form-label">Nama Event</label>
            <input type="text" name="nama_event" class="form-control" value="{{ $listevent->nama_event }}" required>
        </div>

        <div class="mb-3">
            <label for="penyelenggara" class="form-label">Penyelenggara</label>
            <input type="text" name="penyelenggara" class="form-control" value="{{ $listevent->penyelenggara }}" required>
        </div>

        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="4" required>{{ $listevent->deskripsi }}</textarea>
        </div>

        <div class="mb-3">
            <label for="tahun" class="form-label">Tahun</label>
            <input type="number" name="tahun" class="form-control" value="{{ $listevent->tahun }}" required>
        </div>

        <div class="mb-3">
            <label for="foto" class="form-label">Foto</label>
            <input type="file" name="foto" class="form-control">
            @if($listevent->foto)
                <img src="{{ asset('storage/' . $listevent->foto) }}" width="100" class="mt-2">
            @endif
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('listevent.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection