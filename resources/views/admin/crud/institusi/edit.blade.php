@extends('layouts.apk')

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">Edit Institusi</h4>
    <form action="{{ route('institusi.update', $institusi->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nama_institusi" class="form-label">Nama Institusi</label>
            <input type="text" name="nama_institusi" value="{{ $institusi->nama_institusi }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <input type="text" name="alamat" value="{{ $institusi->alamat }}" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('institusi.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection