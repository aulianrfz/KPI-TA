@extends('layouts.app')

@section('content')

@include('layouts.navbar')

<div class="container mt-5">
    <!-- <div class="d-flex align-items-center mb-3">
        <a href="{{ url('/dashboard') }}" class="me-2"><i class="bi bi-arrow-left"></i></a>
        <h4 class="fw-bold mb-0">HOME</h4>
    </div> -->
    <div class="row align-items-center">
        <div class="col-md-6">
            <img src="{{ asset('images/event.jpeg') }}" class="img-fluid rounded-4" style="object-fit: cover; width: 100%; height: 300px;" alt="Event Image">
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Kompetisi Pariwisata Indonesia</h5>

                    <div class="d-flex align-items-center text-muted mb-3">
                        <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                        <small>Dipusatkan di Bandung (POLBAN), Indonesia</small>
                    </div>

                    <p class="mb-3" style="text-align: justify;">
                        Prodi Usaha Perjalanan Wisata Politeknik Negeri Bandung (UPW Polban) merupakan salah satu program studi D3 yang berada di bawah Jurusan Administrasi Niaga. Setiap tahunnya, UPW Polban menyelenggarakan Kegiatan Kompetisi Pariwisata Indonesia (KPI) sejak tahun 2011. Awalnya hanya tingkat nasional, sejak 2022 KPI berkembang ke tingkat internasional untuk meningkatkan kompetensi mahasiswa agar mampu bersaing di industri pariwisata.
                    </p>

                    @auth
                        <a href="{{ route('event.list', 1) }}" class="btn btn-success w-100" style="background-color: #2CC384; border-color: #2CC384; height: 50px;">
                            Daftar Sekarang
                        </a>
                    @else
                        <button class="btn btn-secondary w-100" id="showLoginModalBtn" style="height: 50px;">
                            Daftar
                        </button>
                    @endauth
                </div>
            </div>            
        </div>  
    </div>

    <div class="mt-4">
        <h5 class="fw-semibold mb-3">Tentang KPI</h5>
        <p>Prodi Usaha Perjalanan Wisata Politeknik Negeri Bandung (UPW Polban)  merupakan salah satu program studi D3 yang berada di bawah Jurusan Administrasi Niaga.  Setiap tahunnya, UPW Polban menyelenggarakan Kegiatan Kompetisi Pariwisata Indonesia (KPI). KPI merupakan ajang kompetisi pariwisata yang pertama kali diselenggarakan pada tahun 2011. Awalnya, kompetisi ini hanya diikuti oleh peserta nasional, namun sejak 2022, KPI mulai berkembang ke tingkat internasional dengan tujuan meningkatkan kompetensi mahasiswa agar mampu bersaing di industri pariwisata (KPI, 2024).   .</p>
    </div>
</div>

<div class="modal fade" id="loginAlertModal" tabindex="-1" aria-labelledby="loginAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginAlertModalLabel">Pemberitahuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Silakan login terlebih dahulu untuk melanjutkan.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var showLoginModalBtn = document.getElementById('showLoginModalBtn');
        if (showLoginModalBtn) {
            showLoginModalBtn.addEventListener('click', function() {
                var myModal = new bootstrap.Modal(document.getElementById('loginAlertModal'));
                myModal.show();
            });
        }
    });
</script>
