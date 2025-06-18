@extends('layouts.apk')

@section('content')
    <style>
        /* Style untuk Card agar sesuai dengan UI create.blade */
        .card-stepper {
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: none;
            padding: 2rem;
        }

        /* CSS untuk Stepper (diambil dari create.blade) */
        .stepper-wrapper {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2.5rem;
            position: relative;
        }

        .stepper-item {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            text-align: center;
        }

        .stepper-item .step-counter {
            height: 2.5rem;
            width: 2.5rem;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            color: #e0e0e0;
            z-index: 2;
        }

        .stepper-item::after {
            content: '';
            position: absolute;
            top: 1.25rem;
            left: 50%;
            height: 2px;
            width: 100%;
            background-color: #e0e0e0;
            z-index: 1;
        }

        .stepper-item:last-child::after {
            display: none;
        }

        .stepper-item.active .step-counter {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #ffffff;
        }

        .stepper-item.active::after {
            background-color: #0d6efd;
        }
    </style>

    <div class="container py-5">
        <div class="row d-flex justify-content-center">
            <div class="col-md-10 col-lg-9">
                <div class="card card-stepper">
                    <div class="card-body text-center">

                        <h2 class="text-center fw-bold mb-4">Penjadwalan Sedang Diproses</h2>

                        <div class="stepper-wrapper">
                            {{-- Karena ini halaman proses (langkah terakhir), semua step dibuat 'active' --}}
                            <div class="stepper-item active">
                                <div class="step-counter">1</div>
                            </div>
                            <div class="stepper-item active">
                                <div class="step-counter">2</div>
                            </div>
                            <div class="stepper-item active">
                                <div class="step-counter">3</div>
                            </div>
                            <div class="stepper-item active">
                                <div class="step-counter">4</div>
                            </div>
                        </div>

                        {{-- Konten utama halaman --}}
                        <div class="mt-4">
                            <h5 class="mb-3">Jadwal <strong>{{ $namaJadwal }}</strong> sedang diproses di background.</h5>
                            <p>Silakan kembali ke halaman daftar jadwal untuk melihat hasilnya nanti.</p>

                            <a href="{{ route('jadwal.index', '1') }}" class="btn btn-primary btn-lg mt-3 px-4">
                                Kembali ke Jadwal
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection