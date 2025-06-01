@extends('layouts.apk')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Daftar Jadwal</h1>
            <a href="{{ route('jadwal.create') }}" class="btn btn-primary">Create Jadwal</a>
        </div>

        @if($jadwals->isEmpty())
            <div class="alert alert-warning">Tidak ada jadwal ditemukan.</div>
        @else
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Jadwal</th>
                        <th>Tahun</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jadwals as $index => $jadwal)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $jadwal->nama_jadwal }}</td>
                            <td>{{ $jadwal->tahun }}</td>
                            <td>{{ $jadwal->version }}</td>
                            <td>{{ $jadwal->status ?? 'Belum Dijadwalkan' }}</td>
                            <td>
                                <div class="action d-flex gap-1">
                                    @if($jadwal->status === 'Menunggu' || $jadwal->status === 'Gagal')
                                        <button class="btn btn-sm btn-secondary" disabled>Detail</button>
                                    @else
                                        <a href="{{ route('jadwal.detail', $jadwal->id) }}" class="btn btn-sm btn-info">Detail</a>
                                    @endif

                                    <!-- Tombol Delete Modal -->
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal"
                                        data-action="{{ route('jadwal.destroyJadwal', $jadwal->id) }}">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
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
@endsection