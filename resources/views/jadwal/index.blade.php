@extends('layouts.apk')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Jadwal</h4>
            <div class="d-flex gap-2">
                <div class="dropdown d-inline-block">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Filter by
                    </button>
                    <ul class="dropdown-menu">
                        <!-- Filter Tahun -->
                        <li class="dropdown-header">Tahun</li>
                        @foreach ($availableYears as $year)
                            <li>
                                <a class="dropdown-item" href="{{ route('jadwal.index', ['tahun' => $year]) }}">
                                    {{ $year }}
                                </a>
                            </li>
                        @endforeach

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <!-- Urutkan Berdasarkan Status -->
                        <li>
                            <a class="dropdown-item" href="{{ route('jadwal.index', ['sort' => 'status']) }}">
                                Status
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Tombol Clear Filter -->
                @if(request()->has('tahun') || request('sort') === 'status')
                    <a href="{{ route('jadwal.index') }}" class="btn btn-outline-danger ms-2">
                        Clear Filter
                    </a>
                @endif






                @php
                    $disableCreate = $jadwals->contains('status', 'Menunggu');
                @endphp

                @if ($disableCreate)
                    <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#waitingModal">
                        Buat Jadwal
                    </button>
                @else
                    <a href="{{ route('jadwal.create') }}" class="btn btn-dark">
                        Buat Jadwal
                    </a>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                @if($jadwals->isEmpty())
                    <div class="alert alert-warning mb-0">Tidak ada jadwal ditemukan.</div>
                @else
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Jadwal</th>
                                <th>Tahun Jadwal</th>
                                <th>Version</th>
                                <th>Tanggal dan Waktu Dibuat</th>
                                <th>Status</th>
                                <th>Detail</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jadwals as $index => $jadwal)
                                <tr>
                                    <td>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}.</td>
                                    <td>{{ $jadwal->nama_jadwal }}</td>
                                    <td>{{ $jadwal->tahun }}</td>
                                    <td>{{ $jadwal->version }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($jadwal->created_at)->format('d/m/Y') }}<br>
                                        <small
                                            class="text-muted">{{ \Carbon\Carbon::parse($jadwal->created_at)->format('H:i') }}</small>
                                    </td>
                                    <td>{{ $jadwal->status ?? 'Belum Dijadwalkan' }}</td>
                                    <td>
                                        @if($jadwal->status === 'Menunggu')
                                            <button class="btn btn-sm btn-secondary" disabled>Detail</button>
                                        @elseif($jadwal->status === 'Gagal')
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#gagalModal"
                                                data-alasan="{{ $jadwal->alasan_gagal }}">
                                                Lihat Alasan
                                            </button>
                                        @else
                                            <a href="{{ route('jadwal.detail', $jadwal->id) }}" class="btn btn-sm btn-info">Lihat</a>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal"
                                            data-action="{{ route('jadwal.destroyJadwal', $jadwal->id) }}">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Delete -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        Apakah Anda yakin ingin menghapus jadwal ini? Semua agenda terkait juga akan dihapus.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Alasan Gagal -->
    <div class="modal fade" id="gagalModal" tabindex="-1" aria-labelledby="gagalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Alasan Gagal Penjadwalan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body" id="alasanGagalContent">
                    Loading...
                </div>
            </div>
        </div>
    </div>

    <script>
        const gagalModal = document.getElementById('gagalModal');
        gagalModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const alasan = button.getAttribute('data-alasan');
            const content = gagalModal.querySelector('#alasanGagalContent');
            content.textContent = alasan || 'Tidak ada informasi penyebab.';
        });
    </script>

    <!-- Script untuk atur form action saat modal muncul -->
    <script>
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');
            const form = document.getElementById('deleteForm');
            form.action = action;
        });
    </script>

    <!-- Modal Jika Status Menunggu -->
    <div class="modal fade" id="waitingModal" tabindex="-1" aria-labelledby="waitingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="waitingModalLabel">Informasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    Mohon tunggu penjadwalan sebelumnya selesai sebelum membuat jadwal baru.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection