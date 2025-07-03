@extends('layouts.apk')

@section('content')
<div class="container mt-4">
    <h3 class="fw-bold text-primary mb-3">Tambah Mata Lomba</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($kategoris->isNotEmpty())
        <div class="alert alert-info">
            <strong>Event: </strong> {{ $kategoris->first()->event->nama_event }}
        </div>
    @else
        <div class="alert alert-warning">
            Tidak ada kategori ditemukan. Pastikan Anda memilih event terlebih dahulu.
        </div>
    @endif

    <form action="{{ route('mataLomba.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Event ID hidden --}}
        <input type="hidden" name="event_id" value="{{ $eventId }}">

        {{-- Kategori --}}
        <div class="mb-3">
            <label for="kategori_id" class="form-label">Kategori</label>
            <select name="kategori_id" id="kategori_id" class="form-select" required>
                <option value="">-- Pilih Kategori --</option>
                @foreach($kategoris as $kategori)
                    <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                @endforeach
            </select>
        </div>

        {{-- Venue --}}
        <div class="mb-3">
            <label for="venue_id" class="form-label">Venue</label>
            <select name="venue_id" id="venue_id" class="form-select">
                <option value="">-- Pilih Venue --</option>
                @foreach($venues as $venue)
                    <option value="{{ $venue->id }}">{{ $venue->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Nama Lomba --}}
        <div class="mb-3">
            <label for="nama_lomba" class="form-label">Nama Lomba</label>
            <input type="text" name="nama_lomba" id="nama_lomba" class="form-control" required>
        </div>

        {{-- Jurusan --}}
        <div class="mb-3">
            <label for="jurusan" class="form-label">Jurusan (opsional)</label>
            <input type="text" name="jurusan" id="jurusan" class="form-control">
        </div>

        {{-- Jumlah Peserta --}}
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="min_peserta" class="form-label">Minimal Peserta</label>
                <input type="number" name="min_peserta" id="min_peserta" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="maks_peserta" class="form-label">Maksimal Peserta</label>
                <input type="number" name="maks_peserta" id="maks_peserta" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="maks_total_peserta" class="form-label">Maks Total Peserta</label>
                <input type="number" name="maks_total_peserta" id="maks_total_peserta" class="form-control" required>
            </div>
        </div>

        {{-- Durasi --}}
        <div class="mb-3">
            <label for="durasi" class="form-label">Durasi Perlombaan (menit)</label>
            <input type="number" name="durasi" id="durasi" class="form-control" required>
        </div>

        {{-- Biaya --}}
        <div class="mb-3">
            <label for="biaya_pendaftaran" class="form-label">Biaya Pendaftaran</label>
            <input type="number" name="biaya_pendaftaran" id="biaya_pendaftaran" class="form-control" required>
        </div>

        {{-- TOR --}}
        <div class="mb-3">
            <label for="url_tor" class="form-label">URL TOR (opsional)</label>
            <input type="file" name="url_tor" id="url_tor" class="form-control" accept=".pdf,.doc,.docx">
        </div>

        {{-- Jenis Pelaksanaan --}}
        <div class="mb-3">
            <label for="jenis_pelaksanaan" class="form-label">Jenis Pelaksanaan</label>
            <select name="jenis_pelaksanaan" id="jenis_pelaksanaan" class="form-select" required>
                <option value="">-- Pilih Jenis --</option>
                <option value="Online">Online</option>
                <option value="Offline">Offline</option>
            </select>
        </div>

        {{-- Serentak --}}
        <div class="mb-3">
            <label for="is_serentak" class="form-label">Lomba Serentak</label>
            <select name="is_serentak" id="is_serentak" class="form-select" required>
                <option value="">-- Pilih Status --</option>
                <option value="1">Ya</option>
                <option value="0">Tidak</option>
            </select>
        </div>

        {{-- Deskripsi --}}
        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="4" required></textarea>
        </div>

        {{-- Foto --}}
        <div class="mb-3">
            <label for="foto_kompetisi" class="form-label">Foto Kompetisi</label>
            <input type="file" name="foto_kompetisi" id="foto_kompetisi" class="form-control" required>
        </div>

        {{-- Tombol --}}
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="{{ route('mataLomba.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
