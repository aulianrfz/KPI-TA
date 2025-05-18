@extends('layouts.apk')

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">Tambah Provinsi</h4>
    <form action="{{ route('provinsi.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="nama_provinsi" class="form-label">Nama Provinsi</label>
            <input type="text" name="nama_provinsi" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('provinsi.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection