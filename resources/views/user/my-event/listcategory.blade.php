@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-2 d-none d-md-block bg-light border-end p-3">
            <ul class="nav flex-column mt-4">
                <li class="nav-item mb-3">
                    <a href="{{ route('events.list') }}" class="nav-link text-primary">
                        <i class="bi bi-person-circle me-2"></i> My Categories
                    </a>
                </li>
                <li class="nav-item mb-3">
                    <a href="{{ route('pembayaran.index') }}" class="nav-link text-dark">
                        <i class="bi bi-wallet2 me-2"></i> Pembayaran
                    </a>
                </li>
            </ul>
        </div>

        <div class="col-md-10">
            <h4 class="fw-bold mb-4">Kategori Lomba Saya</h4>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Filter by
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Lomba</th>
                                    <th>Jenis Lomba</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pendaftarList as $item)
                                    <tr>
                                        <td>{{ optional($item->mataLomba)->nama_lomba ?? '-' }}</td>
                                        <td>{{ optional($item->mataLomba)->jenis_lomba ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d.m.Y') }}<br>
                                            <small>{{ \Carbon\Carbon::parse($item->created_at)->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-link text-dark" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="#">
                                                            Lihat Detail
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada kategori lomba yang tersedia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.footer')
@endsection
