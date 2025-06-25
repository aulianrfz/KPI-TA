@extends('layouts.apk')

@section('content')
<div class="container mt-5">
    <h3 class="fw-bold">Edit Sub Kategori</h3>

    <form action="{{ route('mataLomba.update', $mataLomba->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="kategori_id" class="form-label">Kategori</label>
            <select name="kategori_id" id="kategori_id" class="form-select" required>
                @foreach ($kategoris as $kategori)
                    <option value="{{ $kategori->id }}" {{ $mataLomba->kategori_id == $kategori->id ? 'selected' : '' }}>
                        {{ $kategori->nama_kategori }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="venue_id" class="form-label">Venue</label>
            <select name="venue_id" id="venue_id" class="form-select">
                <option value="">-- Pilih Venue --</option>
                @foreach($venues as $venue)
                    <option value="{{ $venue->id }}" {{ $mataLomba->venue_id == $venue->id ? 'selected' : '' }}>
                        {{ $venue->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="nama_lomba" class="form-label">Nama Sub Kategori</label>
            <input type="text" name="nama_lomba" id="nama_lomba" class="form-control" value="{{ $mataLomba->nama_lomba }}" required>
        </div>

        <div class="mb-3">
            <label for="jurusan" class="form-label">Jurusan</label>
            <input type="text" name="jurusan" id="jurusan" class="form-control" value="{{ $mataLomba->jurusan }}">
        </div>

        <div class="mb-3">
            <label for="min_peserta" class="form-label">Minimal Peserta</label>
            <input type="number" name="min_peserta" id="min_peserta" class="form-control" value="{{ $mataLomba->min_peserta }}" required>
        </div>

        <div class="mb-3">
            <label for="maks_peserta" class="form-label">Maksimal Peserta</label>
            <input type="number" name="maks_peserta" id="maks_peserta" class="form-control" value="{{ $mataLomba->maks_peserta }}" required>
        </div>

        <div class="mb-3">
            <label for="maks_total_peserta" class="form-label">Maksimal Total Peserta</label>
            <input type="number" name="maks_total_peserta" id="maks_total_peserta" class="form-control" value="{{ $mataLomba->maks_total_peserta }}" required>
        </div>

        <div class="mb-3">
            <label for="biaya_pendaftaran" class="form-label">Biaya Pendaftaran</label>
            <input type="number" name="biaya_pendaftaran" id="biaya_pendaftaran" class="form-control" value="{{ $mataLomba->biaya_pendaftaran }}" required>
        </div>

        <div class="mb-3">
            <label for="durasi" class="form-label">Durasi Perlombaan (menit)</label>
            <input type="number" name="durasi" id="durasi" class="form-control" value="{{ $mataLomba->durasi }}" required>
        </div>

        <div class="mb-3">
            <label for="url_tor" class="form-label">File TOR</label>

            @if ($mataLomba->url_tor && !old('hapus_tor'))
                <div class="p-3 rounded border" style="background-color: #fafafa;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            📄 <strong>File Saat Ini:</strong> 
                            <a href="{{ asset('storage/' . $mataLomba->url_tor) }}" target="_blank" style="text-decoration: underline;">
                                Lihat TOR
                            </a>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="hapus_tor" id="hapus_tor" value="1"onchange="toggleFileInput(this)">
                            <label class="form-check-label" for="hapus_tor">Hapus File TOR</label>
                        </div>
                    </div>
                </div>
            @endif

            <div id="url_tor_file_wrapper" style="{{ $mataLomba->url_tor && !old('hapus_tor') ? 'display: none;' : 'display: block;' }}">
                <input type="file" name="url_tor" id="url_tor" class="form-control mt-2">
            </div>
        </div>

        <script>
            function toggleFileInput(checkbox) {
                const fileWrapper = document.getElementById('url_tor_file_wrapper');
                if (checkbox.checked) {
                    fileWrapper.style.display = 'block';
                } else {
                    fileWrapper.style.display = 'none';
                }
            }
        </script>

        <div class="mb-3">
            <label for="jenis_pelaksanaan" class="form-label">Jenis Pelaksanaan</label>
            <select name="jenis_pelaksanaan" id="jenis_pelaksanaan" class="form-select" required>
                <option value="Online" {{ $mataLomba->jenis_pelaksanaan == 'Online' ? 'selected' : '' }}>Online</option>
                <option value="Offline" {{ $mataLomba->jenis_pelaksanaan == 'Offline' ? 'selected' : '' }}>Offline</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="is_serentak" class="form-label">Lomba Serentak</label>
            <select name="is_serentak" id="is_serentak" class="form-select" required>
                <option value="1" {{ $mataLomba->is_serentak == 1 ? 'selected' : '' }}>Ya</option>
                <option value="0" {{ $mataLomba->is_serentak == 0 ? 'selected' : '' }}>Tidak</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="4" required>{{ $mataLomba->deskripsi }}</textarea>
        </div>

        <div class="mb-3">
            <label for="foto_kompetisi" class="form-label">Foto Kompetisi (Opsional)</label>
            <input type="file" name="foto_kompetisi" id="foto_kompetisi" class="form-control">
            @if ($mataLomba->foto_kompetisi)
                <div class="mt-2">
                    <label>Foto Lama:</label><br>
                    <img src="{{ asset('storage/' . $mataLomba->foto_kompetisi) }}" alt="Foto" style="max-width:100px;">
                </div>
            @endif
        </div>

        <div class="d-flex justify-content-start mt-4">
            <button type="submit" class="btn btn-primary me-2">Update</button>
            <a href="{{ route('mataLomba.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection
