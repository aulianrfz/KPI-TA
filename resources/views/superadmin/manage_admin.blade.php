@extends('layouts.apk')

@section('title', 'Manajemen Admin')

@section('content')
    <div class="container py-5">
        <h2 class="fw-bold mb-4 text-primary">Manajemen Admin</h2>

        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <a href="{{ route('superadmin.admin.approval') }}" class="text-decoration-none">
                    <div class="card custom-card bg-gradient-kategori text-white h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-person-check-fill display-5 mb-3"></i>
                            <h5 class="card-title fw-semibold">Verifikasi Admin</h5>
                            <p class="card-text">Setujui atau tolak admin yang mendaftar</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="{{ route('superadmin.admin.list') }}" class="text-decoration-none">
                    <div class="card custom-card bg-gradient-provinsi text-white h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-person-lines-fill display-5 mb-3"></i>
                            <h5 class="card-title fw-semibold">Daftar Admin</h5>
                            <p class="card-text">Lihat dan kelola semua admin yang aktif</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection