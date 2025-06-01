@extends('layouts.apk')

@section('content')
    <style>
        /* Mengikuti style dari change.blade.php untuk tabel */
        .table-header-dark-custom {
            background-color: #000000 !important;
        }
        .table-header-dark-custom th {
            font-weight: 600;
            text-align: center;
            color: white;
        }
        .table-hover tbody td {
            text-align: center;
            vertical-align: middle;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        .container {
            max-width: 960px;
        }
    </style>

    <div class="container pt-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0" style="color:#3A3B7B;">Switch Jadwal - {{ $nama_jadwal }} <small class="text-muted">({{ $tahun }} - Versi {{ $version }})</small></h3>
            <a href="{{ route('jadwal.detail', [$nama_jadwal, $tahun, $version]) }}" class="btn btn-sm btn-outline-secondary" title="Kembali">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        @if($jadwals->isEmpty())
            <div class="alert alert-warning">Tidak ada data untuk jadwal ini.</div>
        @else
            <form method="POST" action="{{ route('jadwal.switch.proses') }}" id="switchForm">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-header-dark-custom">
                            <tr>
                                <th>Hari/Tanggal</th>
                                <th>Sub Kategori</th>
                                <th>Waktu Mulai</th>
                                <th>Waktu Selesai</th>
                                <th>Venue</th>
                                <th>Peserta/Tim</th>
                                <th>Juri</th>
                                <th>Pilih untuk Switch</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jadwals as $jadwal)
                                <tr>
                                    <td>{{ $jadwal->tanggal ?? '-' }}</td>
                                    <td>{{ $jadwal->mataLomba->nama_lomba ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($jadwal->waktu_mulai)->format('H.i') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($jadwal->waktu_selesai)->format('H.i') }}</td>
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
                                        <input type="checkbox" name="switch_ids[]" value="{{ $jadwal->id }}" class="switch-checkbox">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary">Proses Switch</button>
                </div>
            </form>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.switch-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    const checked = document.querySelectorAll('.switch-checkbox:checked');
                    const limit = 2;

                    if (checked.length >= limit) {
                        checkboxes.forEach(cb => {
                            if (!cb.checked) {
                                cb.disabled = true;
                            }
                        });
                    } else {
                        checkboxes.forEach(cb => cb.disabled = false);
                    }
                });
            });
        });
    </script>
@endsection
