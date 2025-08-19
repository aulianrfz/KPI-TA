@extends('layouts.apk')

@section('content')
{{-- Menambahkan link CDN Bootstrap secara eksplisit di dalam blade ini --}}
{{-- Pilih salah satu versi Bootstrap yang konsisten dengan layout Anda jika memungkinkan --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
{{-- Font Awesome juga penting untuk ikon, pastikan layouts.apk sudah memuatnya atau tambahkan di sini jika perlu --}}
{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> --}}


<style>
    /* General Page Styles (mirip dengan edit.blade.php) */
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

    /* Button Styling (konsisten dengan gambar 'tambah juri.png') */
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
</style>

<div class="container">
    {{-- Judul Halaman dan Tombol Kembali --}}
    <div class="form-page-title-container">
        <a href="{{ route('juri.index') }}" class="btn btn-link p-0 me-3" title="Kembali ke Daftar Juri">
            <i class="fas fa-arrow-left btn-back-icon"></i> {{-- Pastikan Font Awesome dimuat --}}
        </a>
        <h2 class="form-page-title">Tambah Juri</h2>
    </div>

    <div class="form-card">
        <form action="{{ route('juri.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="nama" class="form-label form-label-styled">Nama Juri</label>
                <input type="text" name="nama" id="nama" class="form-control form-control-custom-bg @error('nama') is-invalid @enderror" value="{{ old('nama') }}" placeholder="Masukkan nama juri" required>
                @error('nama')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="jabatan" class="form-label form-label-styled">Jabatan</label>
                <input type="text" name="jabatan" id="jabatan" class="form-control form-control-custom-bg @error('jabatan') is-invalid @enderror" value="{{ old('jabatan') }}" placeholder="Masukkan jabatan" required>
                 @error('jabatan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="mata_lomba_id" class="form-label form-label-styled">Sub Kategori Lomba</label>
                <select name="mata_lomba_id" id="mata_lomba_id" class="form-select form-select-custom-bg @error('mata_lomba_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Sub Kategori --</option>
                    @foreach($subKategoris as $sub) {{-- Menggunakan $subKategoris sesuai kode asli --}}
                        <option value="{{ $sub->id }}" {{ old('mata_lomba_id') == $sub->id ? 'selected' : '' }}>{{ $sub->nama_lomba }}</option>
                    @endforeach
                </select>
                @error('mata_lomba_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('juri.index') }}" class="btn btn-custom-cancel me-2">Batal</a>
                <button type="submit" class="btn btn-custom-save">Simpan</button>
            </div>
        </form>
    </div>
</div>
{{-- Bootstrap JS Bundle (jika ada komponen Bootstrap yang memerlukan JS, seperti dropdown, modal, dll.) --}}
{{-- Sebaiknya ini juga dimuat oleh layouts.apk, tapi ditambahkan di sini untuk kemandirian jika perlu --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script> --}}
@endsection
