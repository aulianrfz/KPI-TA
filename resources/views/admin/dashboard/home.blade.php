@extends('layouts.apk')

@section('content')
<div class="container py-4">

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 text-center py-4 px-2 bg-light h-100">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="bi bi-sun fs-1 text-warning"></i>
                    </div>
                    <h6 class="text-secondary mb-1">Hari Ini</h6>
                    <h5 class="fw-semibold">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</h5>
                    <div id="clock" class="text-primary fs-4 fw-bold my-2"></div>
                    @php
                        $now = \Carbon\Carbon::now('Asia/Jakarta');
                        $hour = $now->hour;
                        if ($hour >= 4 && $hour < 10) {
                            $salam = 'Selamat Pagi!';
                        } elseif ($hour >= 10 && $hour < 15) {
                            $salam = 'Selamat Siang!';
                        } elseif ($hour >= 15 && $hour < 18) {
                            $salam = 'Selamat Sore!';
                        } else {
                            $salam = 'Selamat Malam!';
                        }
                    @endphp
                    <div class="text-success fw-semibold">{{ $salam }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="row g-3">
                <div class="col-6">
                    <div class="card shadow-sm border-0 rounded-4 text-center py-3 h-100">
                        <div class="card-body">
                            <h6 class="text-secondary mb-2">Tim</h6>
                            <h3 class="fw-bold">{{ $timCount }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card shadow-sm border-0 rounded-4 text-center py-3 h-100">
                        <div class="card-body">
                            <h6 class="text-secondary mb-2">Individu</h6>
                            <h3 class="fw-bold">{{ $individuCount }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card shadow-sm border-0 rounded-4 text-center py-3 h-100">
                        <div class="card-body">
                            <h6 class="text-secondary mb-2">Peserta On-Site</h6>
                            <h3 class="fw-bold text-success">{{ $pesertaOnSite }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card shadow-sm border-0 rounded-4 text-center py-3 h-100">
                        <div class="card-body">
                            <h6 class="text-secondary mb-2">Peserta Belum Hadir</h6>
                            <h3 class="fw-bold text-danger">{{ $belumDaftarUlang }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-612">
                    <div class="card shadow-sm border-0 rounded-4 text-center py-3 h-100">
                        <div class="card-body">
                            <h6 class="text-secondary mb-2">Total Peserta</h6>
                            <h3 class="fw-bold">{{ $totalPeserta }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mb-4">
        <button class="btn btn-primary px-4 py-2 fw-semibold">
            <i class="bi bi-upc-scan me-2"></i> SCAN QR ATTENDANCE
        </button>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between mb-3">
        <input type="text" class="form-control mb-2 mb-md-0 w-100 w-md-50 rounded-3" placeholder="Search for something">
        <div class="d-flex">
            <button class="btn btn-outline-success me-2" title="Export Excel"><i class="bi bi-file-earmark-excel"></i></button>
            <button class="btn btn-outline-secondary">Filter by</button>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-bordered mb-0">
                <thead class="table-light text-center align-middle">
                    <tr>
                        <th>No</th>
                        <th>Nama Peserta</th>
                        <th>Institusi</th>
                        <th>No Handphone</th>
                        <th>NIM</th>
                        <th>Hari/Tanggal</th>
                        <th>QR Code</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="align-middle text-center">
                    @forelse ($pendaftarList as $index => $pendaftar)
                        <tr>
                            <td>{{ str_pad($index + $pendaftarList->firstItem(), 2, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $pendaftar->peserta->nama_peserta ?? '-' }}</td>
                            <td>{{ $pendaftar->peserta->institusi ?? '-' }}</td>
                            <td>{{ $pendaftar->peserta->no_hp ?? '-' }}</td>
                            <td>{{ $pendaftar->peserta->nim ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($pendaftar->created_at)->translatedFormat('l, d-m-Y') }}</td>
                            <td>
                                @if ($pendaftar->url_qrCode)
                                    <a href="{{url($pendaftar->url_qrCode) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                                @else
                                    <span class="text-muted">Tidak Ada</span>
                                @endif
                            </td>
                            <td>
                                @if ($pendaftar->status_hadir ?? false)
                                    <span class="badge bg-success">Hadir</span>
                                @else
                                    <span class="badge bg-danger">Belum Hadir</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">Belum ada data peserta.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-end">
        {{ $pendaftarList->links() }}
    </div>

</div>

<script>
    function updateClock() {
        const now = new Date();
        const clock = document.getElementById('clock');
        if (clock) {
            const time = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            clock.innerText = time;
        }
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
@endsection
