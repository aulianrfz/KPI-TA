@extends('layouts.app')

@section('content')

@include('layouts.navbar')

<!-- Event Details -->
<div class="container mt-5">
    <div class="d-flex align-items-center mb-3">
        <a href="{{ url('/andingpage') }}" class="me-2"><i class="bi bi-arrow-left"></i></a>
        <h5 class="mb-0">KEMBALI</h5>
    </div>
    <div class="row align-items-center">
        <div class="col-md-6">
            <img src="{{ asset('images/event.jpeg') }}" class="img-fluid rounded-4" style="object-fit: cover; width: 100%; height: 300px;" alt="Event Image">
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-bold">Kompetisi Pariwisata Indonesia</h5>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                        <p class="card-text text-muted"><small>Dipusatkan di Bandung (POLBAN), Indonesia</small></p>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-calendar-event-fill text-primary me-2"></i>
                        <p class="card-text text-muted"><small>25 Juni 2025</small></p>
                    </div>
                    <p>Prodi Usaha Perjalanan Wisata Politeknik Negeri Bandung (UPW Polban)  merupakan salah satu program studi D3 yang berada di bawah Jurusan Administrasi Niaga.  Setiap tahunnya, UPW Polban menyelenggarakan Kegiatan Kompetisi Pariwisata Indonesia (KPI). KPI merupakan ajang kompetisi pariwisata yang pertama kali diselenggarakan pada tahun 2011. Awalnya, kompetisi ini hanya diikuti oleh peserta nasional, namun sejak 2022, KPI mulai berkembang ke tingkat internasional dengan tujuan meningkatkan kompetensi mahasiswa agar mampu bersaing di industri pariwisata (KPI, 2024).   </p>

                    <a href="{{ route('event.list', 1) }}" class="btn btn-success w-100 mt-2" style="background-color: #2CC384; border-color: #2CC384; height: 50px;">Daftar</a>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <p>Prodi Usaha Perjalanan Wisata Politeknik Negeri Bandung (UPW Polban)  merupakan salah satu program studi D3 yang berada di bawah Jurusan Administrasi Niaga.  Setiap tahunnya, UPW Polban menyelenggarakan Kegiatan Kompetisi Pariwisata Indonesia (KPI). KPI merupakan ajang kompetisi pariwisata yang pertama kali diselenggarakan pada tahun 2011. Awalnya, kompetisi ini hanya diikuti oleh peserta nasional, namun sejak 2022, KPI mulai berkembang ke tingkat internasional dengan tujuan meningkatkan kompetensi mahasiswa agar mampu bersaing di industri pariwisata (KPI, 2024).   .</p>
    </div>
</div>

@include('layouts.footer')

@endsection
