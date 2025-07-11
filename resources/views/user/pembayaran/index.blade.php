@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
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

        <!-- Main Content -->
        <div class="col-md-10">
            <h4 class="fw-bold mb-4">Pembayaran Event</h4>

            {{-- Filter Event --}}
            <form method="GET" action="{{ route('pembayaran.index') }}" class="mb-4">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <select name="event_id" class="form-select" style="width: 250px;" onchange="this.form.submit()">
                        <option value="">-- Pilih Event --</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                {{ $event->nama_event }}
                            </option>
                        @endforeach
                    </select>
                    @if(request('event_id'))
                        <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                    @endif
                </div>
            </form>

            @if($peserta->count())
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Lomba</th>
                                    <th>Order ID</th>
                                    <th>Status</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($peserta as $item)
                                @php
                                    $pendaftar = $item->pendaftar;
                                    $mataLomba = $pendaftar?->mataLomba;
                                    $latestPembayaran = $item->membayar->sortByDesc('waktu')->first();
                                    $status = strtolower($latestPembayaran->status ?? 'belum dibayar');
                                    $badgeColor = match($status) {
                                        'menunggu verifikasi' => '#FFF6D1',
                                        'sudah membayar' => '#D0F4FF',
                                        'ditolak' => '#FFBABA',
                                        default => '#FFDFDF'
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $mataLomba?->nama_lomba ?? '-' }}</td>
                                    <td>{{ $latestPembayaran?->invoice->id ?? '-' }}</td>
                                    <td><span class="badge text-dark" style="background-color: {{ $badgeColor }}">{{ ucwords($status) }}</span></td>
                                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d.m.Y') }}<br>
                                        <small>{{ \Carbon\Carbon::parse($item->created_at)->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-link text-dark" type="button" data-bs-toggle="dropdown">
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
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-end align-items-center mt-3 gap-2">
                        <span class="small text-muted mb-0">
                            Page {{ $peserta->currentPage() }} of {{ $peserta->lastPage() }}
                        </span>
                        @if ($peserta->onFirstPage())
                            <span class="btn btn-sm btn-light disabled">‹</span>
                        @else
                            <a href="{{ $peserta->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">‹</a>
                        @endif
                        @if ($peserta->hasMorePages())
                            <a href="{{ $peserta->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">›</a>
                        @else
                            <span class="btn btn-sm btn-light disabled">›</span>
                        @endif
                    </div>
                </div>
            </div>
            @else
                <div class="alert alert-info text-center mt-4">Tidak ada data pembayaran ditemukan.</div>
            @endif
        </div>
    </div>
</div>
@endsection
