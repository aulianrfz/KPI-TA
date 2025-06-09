@extends('layouts.apk')

@section('content')
{{-- Menambahkan link CDN Bootstrap secara eksplisit di dalam blade ini --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
{{-- Pastikan Font Awesome dimuat oleh layouts.apk atau tambahkan CDN di sini jika ikon tidak muncul --}}
{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> --}}

<style>
    /* General Page Styles */
    .form-page-title-container {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .form-page-title {
        color: #3A3B7B; /* Warna biru tua/ungu */
        font-weight: 600;
        font-size: 1.75rem;
        margin-bottom: 0;
    }
    .btn-back-icon {
        font-size: 1.25rem;
        color: #6c757d;
    }
    .btn-back-icon:hover {
        color: #3A3B7B;
    }

    /* Card Styling */
    .form-card {
        background-color: #ffffff;
        border-radius: 0.75rem;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
    }

    /* Form Element Styling */
    .form-label-styled {
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: #495057;
    }
    .form-control-custom-bg,
    .form-select-custom-bg {
        background-color: #f8f9fa; /* Latar abu-abu muda */
        border: 1px solid #e9ecef; /* Border tipis */
        border-radius: 0.375rem;
    }
    .form-control-custom-bg:focus,
    .form-select-custom-bg:focus {
        background-color: #ffffff;
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Button Styling */
    .btn-custom-cancel {
        background-color: #dc3545; /* Merah */
        color: white;
        border-color: #dc3545;
        padding: 0.5rem 1rem;
    }
    .btn-custom-cancel:hover {
        background-color: #bb2d3b;
        border-color: #b02a37;
    }
    .btn-custom-save {
        background-color: #198754; /* Hijau */
        color: white;
        border-color: #198754;
        padding: 0.5rem 1rem;
    }
    .btn-custom-save:hover {
        background-color: #157347;
        border-color: #146c43;
    }
    .form-actions {
        margin-top: 2rem;
        text-align: right;
    }

    /* Modal Error Styling */
    .modal-overlay-custom {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.6);
        display: none; /* Defaultnya tersembunyi, akan diaktifkan oleh JS jika session error ada */
        justify-content: center;
        align-items: center;
        z-index: 1055;
    }
    .modal-content-custom {
        background: white;
        padding: 2rem;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        max-width: 450px;
        width: 90%;
        text-align: center;
    }
    .modal-content-custom p {
        margin-bottom: 1.5rem;
        font-size: 1.05rem;
        color: #495057;
    }
    .modal-content-custom .btn-modal-close { /* Tombol tutup modal */
        background-color: #6c757d; /* Abu-abu */
        color: white;
        border-color: #6c757d;
    }
     .modal-content-custom .btn-modal-close:hover {
        background-color: #5c636a;
        border-color: #565e64;
    }
</style>

<div class="container">
    {{-- Judul Halaman dan Tombol Kembali --}}
    <div class="form-page-title-container">
        <a href="{{ route('juri.index') }}" class="btn btn-link p-0 me-3" title="Kembali ke Daftar Juri">
            <i class="fas fa-arrow-left btn-back-icon"></i> {{-- Pastikan Font Awesome dimuat --}}
        </a>
        <h2 class="form-page-title">Edit Juri</h2>
    </div>

    {{-- Modal untuk session error --}}
    @if(session('error'))
    <div id="errorModalCustom" class="modal-overlay-custom" style="display: flex;">
        <div class="modal-content-custom">
            <p>{{ session('error') }}</p>
            <button type="button" onclick="document.getElementById('errorModalCustom').style.display = 'none';" class="btn btn-modal-close">Tutup</button>
        </div>
    </div>
    @endif

    <div class="form-card">
        <form action="{{ route('juri.update', $juri->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Nama Juri --}}
            <div class="mb-3">
                <label for="nama" class="form-label form-label-styled">Nama Juri</label>
                <input type="text" name="nama" id="nama" class="form-control form-control-custom-bg @error('nama') is-invalid @enderror" value="{{ old('nama', $juri->nama) }}" placeholder="Masukkan nama juri" required>
                @error('nama')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Kategori Lomba (dari Sub Kategori di kode asli) --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="mata_lomba_id" class="form-label form-label-styled">Kategori Lomba</label>
                        <select name="mata_lomba_id" id="mata_lomba_id" class="form-select form-select-custom-bg @error('mata_lomba_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Kategori Lomba --</option>
                            @foreach($subKategoris as $sub)
                                <option value="{{ $sub->id }}" {{ old('mata_lomba_id', $juri->mata_lomba_id) == $sub->id ? 'selected' : '' }}>
                                    {{ $sub->nama_lomba }}
                                </option>
                            @endforeach
                        </select>
                        @error('mata_lomba_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    {{-- Tanggal (Field baru sesuai gambar) --}}
                    <div class="mb-3">
                        <label for="tanggal" class="form-label form-label-styled">Tanggal</label>
                        {{-- Asumsi $juri->tanggal_tersedia ada, jika tidak, gunakan old() atau kosongkan --}}
                        <input type="date" name="tanggal" id="tanggal" class="form-control form-control-custom-bg @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', $juri->tanggal_tersedia ?? '') }}" placeholder="Pilih tanggal">
                        @error('tanggal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    {{-- Waktu Mulai Tersedia (Field baru sesuai gambar) --}}
                    <div class="mb-3">
                        <label for="waktu_mulai_tersedia" class="form-label form-label-styled">Waktu Mulai Tersedia</label>
                        {{-- Asumsi $juri->waktu_mulai_tersedia ada --}}
                        <input type="time" name="waktu_mulai_tersedia" id="waktu_mulai_tersedia" class="form-control form-control-custom-bg @error('waktu_mulai_tersedia') is-invalid @enderror" value="{{ old('waktu_mulai_tersedia', $juri->waktu_mulai_tersedia ?? '') }}" placeholder="HH:MM">
                        @error('waktu_mulai_tersedia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    {{-- Waktu Akhir Tersedia (Field baru sesuai gambar) --}}
                    <div class="mb-3">
                        <label for="waktu_akhir_tersedia" class="form-label form-label-styled">Waktu Akhir Tersedia</label>
                        {{-- Asumsi $juri->waktu_akhir_tersedia ada --}}
                        <input type="time" name="waktu_akhir_tersedia" id="waktu_akhir_tersedia" class="form-control form-control-custom-bg @error('waktu_akhir_tersedia') is-invalid @enderror" value="{{ old('waktu_akhir_tersedia', $juri->waktu_akhir_tersedia ?? '') }}" placeholder="HH:MM">
                        @error('waktu_akhir_tersedia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('juri.index') }}" class="btn btn-custom-cancel me-2">Batal</a>
                <button type="submit" class="btn btn-custom-save">Simpan</button>
            </div>
        </form>
    </div>
</div>
{{-- Bootstrap JS Bundle (jika ada komponen Bootstrap yang memerlukan JS) --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script> --}}
<script>
    // Script untuk menampilkan modal error jika ada session 'error'
    window.addEventListener('DOMContentLoaded', (event) => {
        const modal = document.getElementById('errorModalCustom');
        @if(session('error'))
            if (modal) {
                modal.style.display = 'flex';
            }
        @endif
    });
</script>
@endsection
