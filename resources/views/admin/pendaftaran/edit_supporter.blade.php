@extends('layouts.apk')

@section('content')
<div class="container mt-4">
    <h2>Edit Data Supporter</h2>
    <form action="{{ route('pendaftaran.supporter.update', $supporter->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Nama</label>
            <input type="text" class="form-control" name="nama" value="{{ $supporter->nama }}" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" name="email" value="{{ $supporter->email }}" required>
        </div>

        <div class="mb-3">
            <label>Instansi</label>
            <input type="text" class="form-control" name="instansi" value="{{ $supporter->instansi }}" required>
        </div>

        <div class="mb-3">
            <label>No HP</label>
            <input type="text" class="form-control" name="no_hp" value="{{ $supporter->no_hp }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
