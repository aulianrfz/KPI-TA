@extends('layouts.apk')

@section('content')
    {{-- Menambahkan link CDN Bootstrap secara eksplisit di dalam blade ini --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    {{-- Pastikan Font Awesome dimuat oleh layouts.apk atau tambahkan CDN di sini jika ikon tidak muncul --}}
    {{--
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> --}}

    <style>
        /* General Page Styles */
        .form-page-title-container {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .form-page-title {
            color: #3A3B7B;
            /* Warna biru tua/ungu */
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        /* Form Element Styling */
        .form-label-styled {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #495057;
        }

        .form-control-custom-bg {
            background-color: #f8f9fa;
            /* Latar abu-abu muda */
            border: 1px solid #e9ecef;
            /* Border tipis */
            border-radius: 0.375rem;
        }

        .form-control-custom-bg:focus {
            background-color: #ffffff;
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Button Styling */
        .btn-custom-cancel {
            background-color: #dc3545;
            /* Merah */
            color: white;
            border-color: #dc3545;
            padding: 0.5rem 1rem;
        }

        .btn-custom-cancel:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
        }

        .btn-custom-save {
            /* Digunakan untuk tombol Update/Simpan */
            background-color: #198754;
            /* Hijau */
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
            <a href="{{ route('venue.index') }}" class="btn btn-link p-0 me-3" title="Kembali ke Daftar Venue">
                <i class="fas fa-arrow-left btn-back-icon"></i> {{-- Pastikan Font Awesome dimuat --}}
            </a>
            <h2 class="form-page-title">Edit Venue</h2>
        </div>

        <div class="form-card">
            <form action="{{ route('venue.update', $venue->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label form-label-styled">Nama Venue</label>
                    <input type="text" name="name" id="name"
                        class="form-control form-control-custom-bg @error('name') is-invalid @enderror"
                        value="{{ old('name', $venue->name) }}" placeholder="Masukkan nama venue" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="tanggal_tersedia" class="form-label form-label-styled">Tanggal Tersedia</label>
                            <input type="date" name="tanggal_tersedia" id="tanggal_tersedia"
                                class="form-control form-control-custom-bg @error('tanggal_tersedia') is-invalid @enderror"
                                value="{{ old('tanggal_tersedia', $venue->tanggal_tersedia) }}">
                            @error('tanggal_tersedia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="waktu_mulai_tersedia" class="form-label form-label-styled">Waktu Mulai</label>
                            <input type="time" name="waktu_mulai_tersedia" id="waktu_mulai_tersedia"
                                class="form-control form-control-custom-bg @error('waktu_mulai_tersedia') is-invalid @enderror"
                                value="{{ old('waktu_mulai_tersedia', $venue->waktu_mulai_tersedia) }}">
                            @error('waktu_mulai_tersedia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="waktu_berakhir_tersedia" class="form-label form-label-styled">Waktu Berakhir</label>
                            <input type="time" name="waktu_berakhir_tersedia" id="waktu_berakhir_tersedia"
                                class="form-control form-control-custom-bg @error('waktu_berakhir_tersedia') is-invalid @enderror"
                                value="{{ old('waktu_berakhir_tersedia', $venue->waktu_berakhir_tersedia) }}">
                            @error('waktu_berakhir_tersedia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('venue.index') }}" class="btn btn-custom-cancel me-2">Batal</a>
                    <button type="submit" class="btn btn-custom-save">Update</button>
                </div>
            </form>
        </div>
    </div>
    {{-- Bootstrap JS Bundle (jika ada komponen Bootstrap yang memerlukan JS) --}}
    {{--
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script> --}}
@endsection