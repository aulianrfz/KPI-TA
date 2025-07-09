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
                        <!-- <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        Filter by
                                                    </button>
                                                </div> -->

                        <div class="table-responsive">
                            <table class="table align-middle" id="datatable-pembayaran">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tipe</th>
                                        <th>Kategori Lomba</th>
                                        <th>Order ID</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pembayaranGabungan as $item)
                                        <tr>
                                            <td>{{ $item->tipe }}</td>
                                            <td>
                                                {{ $item->tipe === 'Pembimbing' ? '-' : $item->nama_kategori }}
                                            </td>
                                            <td>{{ $item->invoice_id }}</td>
                                            <td>
                                                @php
                                                    $status = $item->status;
                                                @endphp

                                                @if ($status === 'menunggu verifikasi')
                                                    <span class="badge text-dark" style="background-color: #FFF6D1;">Menunggu
                                                        Verifikasi</span>
                                                @elseif ($status === 'sudah membayar')
                                                    <span class="badge text-dark" style="background-color: #D0F4FF;">Sudah
                                                        Dibayar</span>
                                                @elseif ($status === 'ditolak')
                                                    <span class="badge text-dark" style="background-color: #FFBABA;">Ditolak</span>
                                                @else
                                                    <span class="badge text-dark" style="background-color: #FFDFDF;">Belum
                                                        Dibayar</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($item->created_at)->format('d.m.Y') }}<br>
                                                <small>{{ \Carbon\Carbon::parse($item->created_at)->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-link text-dark" type="button"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('pembayaran.bayar', ['tipe' => strtolower($item->tipe), 'id' => $item->id]) }}">
                                                                Lihat Detail Pembayaran
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Tidak ada data pembayaran.</td>
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

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#datatable-pembayaran').DataTable({
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ entri",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    "paginate": {
                        "first": "Awal",
                        "last": "Akhir",
                        "next": "›",
                        "previous": "‹"
                    },
                    "zeroRecords": "Data tidak ditemukan",
                },
                "order": [[3, "desc"]] // default sorting by date desc
            });
        });
    </script>
@endsection