@extends('layouts.apk')

@section('content')
<div class="container mt-4">
    <h4>Tambah Event</h4>

    <form action="{{ route('listevent.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="nama_event" class="form-label">Nama Event</label>
            <input type="text" name="nama_event" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="penyelenggara" class="form-label">Lokasi Penyelenggara</label>
            <input type="text" name="penyelenggara" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="4" required></textarea>
        </div>

        <div class="mb-3">
            <label for="tanggal" class="form-label">Mulai Pendaftran Pada Tanggal</label>
            <input type="date" name="tanggal" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="tanggal_akhir" class="form-label">Tanggal Selesai Pendaftran</label>
            <input type="date" name="tanggal_akhir" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="biaya" class="form-label">Biaya Untuk Pembimbing(opsional)</label>
            <input type="number" name="biaya" class="form-control" step="0.01" min="0" value="0">
            <small class="text-muted">Kosongkan atau isi dengan angka, default 0</small>
        </div>
        
        
        <div class="mb-3">
            <label for="foto" class="form-label">Foto</label>
            <input type="file" name="foto" class="form-control"required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('listevent.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection