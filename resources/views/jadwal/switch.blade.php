@extends('layouts.apk')

@section('content')
<div class="container">
    <h2>Switch Jadwal: {{ $nama_jadwal }} - {{ $tahun }} - Versi {{ $version }}</h2>
    <a href="{{ route('jadwal.detail', [$nama_jadwal, $tahun, $version]) }}" class="btn btn-secondary mb-3">Kembali</a>

    <form method="POST" action="{{ route('jadwal.switch.proses') }}" id="switchForm">
        @csrf

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Pilih</th>
                    <th>Sub Kategori</th>
                    <th>Waktu Mulai</th>
                    <th>Waktu Selesai</th>
                    <th>Venue</th>
                    <th>Peserta</th>
                    <th>Juri</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jadwals as $jadwal)
                <tr>
                    <td>
                        <input type="checkbox" name="selected_ids[]" value="{{ $jadwal->id }}" class="switch-checkbox">
                    </td>
                    <td>{{ $jadwal->mataLomba->nama_lomba ?? '-' }}</td>
                    <td>{{ $jadwal->waktu_mulai }}</td>
                    <td>{{ $jadwal->waktu_selesai }}</td>
                    <td>{{ $jadwal->venue->name ?? '-' }}</td>
                    <td>{{ $jadwal->peserta->nama ?? '-' }}</td>
                    <td>{{ $jadwal->juri->nama ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">Proses Switch</button>
    </form>
</div>

<script>
    // Batasi pilihan checkbox hanya 2
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('.switch-checkbox');
        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', function () {
                const checked = document.querySelectorAll('.switch-checkbox:checked');
                if (checked.length > 2) {
                    this.checked = false;
                    alert('Maksimal hanya boleh memilih 2 jadwal untuk ditukar.');
                }
            });
        });

        // Validasi sebelum submit
        const form = document.getElementById('switchForm');
        form.addEventListener('submit', function (e) {
            const checked = document.querySelectorAll('.switch-checkbox:checked');
            if (checked.length !== 2) {
                e.preventDefault();
                alert('Pilih tepat 2 jadwal untuk melakukan proses tukar.');
            }
        });
    });
</script>
@endsection
