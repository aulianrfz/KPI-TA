@extends('layouts.apk')

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">Edit Provinsi</h4>
    <form action="{{ route('provinsi.update', $provinsi->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nama_provinsi" class="form-label">Nama Provinsi</label>
            <input type="text" name="nama_provinsi" value="{{ $provinsi->nama_provinsi }}" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('provinsi.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection