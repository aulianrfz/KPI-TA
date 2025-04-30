@extends('layouts.app')

@include('layouts.navbar')

@section('content')
<div class="container mt-5">
    <h3 class="fw-bold">Edit Sub Kategori</h3>

    <form action="{{ route('subkategori.update', $subKategori->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="kategori_id" class="form-label">Kategori</label>
            <select name="kategori_id" id="kategori_id" class="form-select" required>
                @foreach ($kategoris as $kategori)
                    <option value="{{ $kategori->id }}" {{ $subKategori->kategori_id == $kategori->id ? 'selected' : '' }}>{{ $kategori->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Nama Sub Kategori</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $subKategori->name }}" required>
        </div>

        <div class="mb-3">
            <label for="jurusan" class="form-label">Jurusan</label>
            <input type="text" name="jurusan" id="jurusan" class="form-control" value="{{ $subKategori->jurusan }}" required>
        </div>

        <div class="mb-3">
            <label for="maks_peserta" class="form-label">Maksimal Peserta</label>
            <input type="number" name="maks_peserta" id="maks_peserta" class="form-control" value="{{ $subKategori->maks_peserta }}" required>
        </div>

        <div class="mb-3">
            <label for="biaya_pendaftaran" class="form-label">Biaya Pendaftaran</label>
            <input type="number" name="biaya_pendaftaran" id="biaya_pendaftaran" class="form-control" value="{{ $subKategori->biaya_pendaftaran }}" required>
        </div>

        <div class="mb-3">
            <label for="foto_kompetisi" class="form-label">Foto Kompetisi (Opsional)</label>
            <input type="file" name="foto_kompetisi" id="foto_kompetisi" class="form-control">
            @if ($subKategori->foto_kompetisi)
                <p class="mt-2">Foto lama: <img src="{{ asset('storage/' . $subKategori->foto_kompetisi) }}" alt="Foto" width="50"></p>
            @endif
        </div>

        <div class="mb-3">
            <label for="url_tor" class="form-label">URL TOR (Opsional)</label>
            <input type="text" name="url_tor" id="url_tor" class="form-control" value="{{ $subKategori->url_tor }}">
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('subkategori.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>

@include('layouts.footer')

@endsection
