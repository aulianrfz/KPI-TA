@extends('layouts.app')

@include('layouts.navbar')

@section('content')
<div class="container">
    <h1>Tambah Sub Kategori</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('subkategori.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="kategori_id">Kategori</label>
            <select name="kategori_id" id="kategori_id" class="form-control" required>
                @foreach($kategoris as $kategori)
                    <option value="{{ $kategori->id }}">{{ $kategori->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="name_lomba">Nama SubKategori</label>
            <input type="text" name="name_lomba" id="name_lomba" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="jurusan">Jurusan</label>
            <input type="text" name="jurusan" id="jurusan" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="maks_peserta">Maksimal Peserta</label>
            <input type="number" name="maks_peserta" id="maks_peserta" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="duration">Durasi Perlombaan (menit)</label>
            <input type="integer" name="duration" id="duration" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="biaya_pendaftaran">Biaya Pendaftaran</label>
            <input type="number" name="biaya_pendaftaran" id="biaya_pendaftaran" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="url_tor">URL TOR (opsional)</label>
            <input type="text" name="url_tor" id="url_tor" class="form-control">
        </div>

        <div class="form-group">
            <label for="jenis_pelaksanaan">Jenis Pelaksanaan</label>
            <select name="jenis_pelaksanaan" id="jenis_pelaksanaan" class="form-control" required>
                <option value="">-- Pilih Jenis Pelaksanaan --</option>
                <option value="Online">Online</option>
                <option value="Offline">Offline</option>
            </select>
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="4"></textarea>
        </div>

        <div class="form-group">
            <label for="foto_kompetisi">Foto Kompetisi (opsional)</label>
            <input type="file" name="foto_kompetisi" id="foto_kompetisi" class="form-control-file">
        </div>

        <button type="submit" class="btn btn-success mt-3">Simpan</button>
        <a href="{{ route('subkategori.index') }}" class="btn btn-secondary mt-3">Batal</a>
    </form>
</div>

@include('layouts.footer')
@endsection
