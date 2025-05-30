@extends('layouts.apk')

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

    <form action="{{ route('mataLomba.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="kategori_id">Kategori</label>
            <select name="kategori_id" id="kategori_id" class="form-control" required>
                @foreach($kategoris as $kategori)
                    <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="nama_lomba">Nama mataLomba</label>
            <input type="text" name="nama_lomba" id="nama_lomba" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="jurusan">Jurusan</label>
            <input type="text" name="jurusan" id="jurusan" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="min_peserta">Minimal Peserta</label>
            <input type="number" name="min_peserta" id="min_peserta" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="maks_peserta">Maksimal Peserta</label>
            <input type="number" name="maks_peserta" id="maks_peserta" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="maks_total_peserta">Maksimal Total Peserta</label>
            <input type="number" name="maks_total_peserta" id="maks_total_peserta" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="durasi">Durasi Perlombaan (menit)</label>
            <input type="integer" name="durasi" id="durasi" class="form-control" required>
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
        <a href="{{ route('mataLomba.index') }}" class="btn btn-secondary mt-3">Batal</a>
    </form>
</div>

@endsection
