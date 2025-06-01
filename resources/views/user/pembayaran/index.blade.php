@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-2 d-none d-md-block bg-light border-end p-3">
            <ul class="nav flex-column mt-4">
                <li class="nav-item mb-3">
                    <a href="{{ route('events.list') }}" class="nav-link text-dark">
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

        <div class="col-md-10">
            <h4 class="fw-bold mb-4">Payment Categories</h4>

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
                                    <th>Kategori Lomba</th>
                                    <th>Order ID</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse ($peserta as $item)
                                <tr>
                                    <td>{{ optional($item->pendaftar)->mataLomba->nama_lomba ?? '-' }}</td>
                                    <td>{{ optional($item->membayar->first()?->invoice)->id ?? '-' }}</td>
                                    <td>
                                        @php
                                            $latestPembayaran = $item->membayar->sortByDesc('waktu')->first();
                                            $status = strtolower($latestPembayaran->status ?? 'belum dibayar');
                                        @endphp

                                        @if ($status === 'menunggu verifikasi')
                                            <span class="badge text-dark" style="background-color: #FFF6D1;">Menunggu Verifikasi</span>
                                        @elseif ($status === 'sudah membayar')
                                            <span class="badge text-dark" style="background-color: #D0F4FF;">Sudah Dibayar</span>
                                        @elseif ($status === 'ditolak')
                                            <span class="badge text-dark" style="background-color: #FFBABA;">Ditolak</span>
                                        @else
                                            <span class="badge text-dark" style="background-color: #FFDFDF;">Belum Dibayar</span>
                                        @endif
                                    </td>
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
                                                    <a class="dropdown-item" href="{{ route('pembayaran.bayar', $item->id) }}">
                                                        Lihat Detail Pembayaran
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data pembayaran.</td>
                                </tr>
                            @endforelse
                            </tbody>

                        </table>
                    </div>
                    <div class="d-flex justify-content-end align-items-center mt-3 gap-2">
                        <span class="small text-muted mb-0">
                            Page {{ $peserta->currentPage() }} of {{ $peserta->lastPage() }}
                        </span>
                        @if ($peserta->onFirstPage())
                            <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">‹</span>
                        @else
                            <a href="{{ $peserta->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">‹</a>
                        @endif
                        @if ($peserta->hasMorePages())
                            <a href="{{ $peserta->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">›</a>
                        @else
                            <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">›</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.footer')
@endsection
