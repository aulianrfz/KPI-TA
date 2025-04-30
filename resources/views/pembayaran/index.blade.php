@extends('layouts.app')
@include('layouts.navbar')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        {{-- Sidebar --}}
        <div class="col-md-2 d-none d-md-block bg-light border-end p-3">
            <ul class="nav flex-column mt-4">
                <li class="nav-item mb-3">
                    <a href="{{ route('dashboard') }}" class="nav-link text-dark">
                        <i class="bi bi-person-circle me-2"></i> My Categories
                    </a>
                </li>
                <li class="nav-item mb-3">
                    <a href="{{ route('pembayaran.index') }}" class="nav-link text-primary">
                        <i class="bi bi-wallet2 me-2"></i> Pembayaran
                    </a>
                </li>
            </ul>
        </div>

        {{-- Main Content --}}
        <div class="col-md-10">
            <h4 class="fw-bold mb-4">Payment Categories</h4>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Filter by
                        </button>
                        {{-- Tambahkan filter sesuai kebutuhan --}}
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Kategori Lomba</th>
                                    <th>Order ID</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($peserta as $item)
                                    <tr>
                                        <td>{{ $item->subKategori->name_lomba }}</td>
                                        <td>{{ $item->order_id }}</td>
                                        <td>
                                            @php
                                                $status = strtolower($item->status);
                                            @endphp
                                            @if ($status === 'belum bayar')
                                                <span class="badge text-dark" style="background-color: #FFDFDF;">Belum Dibayar</span>
                                            @elseif ($status === 'menunggu verifikasi')
                                                <span class="badge text-dark" style="background-color: #FFF6D1;">Menunggu Verifikasi</span>
                                            @elseif ($status === 'sudah bayar')
                                                <span class="badge text-dark" style="background-color: #D0F4FF;">Sudah Dibayar</span>
                                            @else
                                                <span class="badge bg-secondary">Tidak Diketahui</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d.m.Y') }}<br>
                                            <small>{{ \Carbon\Carbon::parse($item->created_at)->format('h:i A') }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada data pembayaran.</td>
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
