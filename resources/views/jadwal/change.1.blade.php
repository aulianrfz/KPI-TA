@extends('layouts.apk')

@section('content')

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Detail Jadwal: {{ $nama_jadwal }} - {{ $tahun }} - Versi {{ $version }}</h2>
            <a href="{{ route('jadwal.switch', ['nama_jadwal' => $nama_jadwal, 'tahun' => $tahun, 'version' => $version]) }}"
                class="btn btn-warning">
                Switch Jadwal
            </a>
            <a href="{{ route('jadwal.create.withDetail', [$nama_jadwal, $tahun, $version]) }}"
                class="btn btn-success mb-3">Add Jadwal</a>
        </div>

        @if($jadwals->isEmpty())
            <div class="alert alert-warning">Tidak ada data untuk jadwal ini.</div>
        @else
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Hari/Tanggal</th> {{-- Kolom baru --}}
                        <th>Sub Kategori</th>
                        <th>Waktu Mulai</th>
                        <th>Waktu Selesai</th>
                        <th>Venue</th>
                        <th>Peserta/Tim</th>
                        <th>Juri</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jadwals as $jadwal)
                        <tr>
                            <td>{{ $jadwal->tanggal ?? '-' }}</td>
                            <td>{{ $jadwal->mataLomba->nama_lomba ?? '-' }}</td>
                            <td>{{ $jadwal->waktu_mulai }}</td>
                            <td>{{ $jadwal->waktu_selesai }}</td>
                            <td>{{ $jadwal->venue->name ?? '-' }}</td>
                            <td>
                                @if($jadwal->peserta && $jadwal->peserta->count())
                                    @foreach($jadwal->peserta as $peserta)
                                        {{ $peserta->nama_peserta }}<br>
                                    @endforeach
                                @elseif($jadwal->tim && $jadwal->tim->count())
                                    @foreach($jadwal->tim as $tim)
                                        {{ $tim->nama_tim }}<br>
                                    @endforeach
                                @else
                                    -
                                @endif
                            </td>

                            <td>{{ $jadwal->juri->nama ?? '-' }}</td>
                            <td>
                                <a href="{{ route('jadwal.edit', $jadwal->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal" data-action="{{ route('jadwal.destroy', $jadwal->id) }}">
                                    Delete
                                </button>
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
                        Apakah Anda yakin ingin menghapus jadwal ini?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
            const deleteModal = document.getElementById('deleteModal');
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const action = button.getAttribute('data-action');
                const form = document.getElementById('deleteForm');
                form.action = action;
            });
        </script>

    </div>

@endsection