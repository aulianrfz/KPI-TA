@extends('layouts.apk')

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">Tambah Institusi</h4>
    <form action="{{ route('institusi.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="nama_institusi" class="form-label">Nama Institusi</label>
            <input type="text" name="nama_institusi" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <input type="text" name="alamat" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('institusi.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection