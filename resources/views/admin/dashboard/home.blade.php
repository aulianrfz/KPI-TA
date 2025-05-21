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
                        $salam = match(true) {
                            $hour >= 4 && $hour < 10 => 'Selamat Pagi!',
                            $hour >= 10 && $hour < 15 => 'Selamat Siang!',
                            $hour >= 15 && $hour < 18 => 'Selamat Sore!',
                            default => 'Selamat Malam!',
                        };
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
                <div class="col-12">
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
        <button class="btn btn-primary px-4 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#qrScanModal">
            <i class="bi bi-upc-scan me-2"></i> SCAN QR ATTENDANCE
        </button>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between mb-3">
        <form method="GET" action="{{ route('transaksi.index') }}" class="d-flex">
            <div class="input-group w-400 w-md-50">
                <input type="text" name="search" class="form-control border" placeholder="Cari nama peserta / institusi" style="border-color: #0367A6;" value="{{ request('search') }}">
                <span class="input-group-text" style="background-color: #0367A6; color: white;"><i class="bi bi-search"></i></span>
            </div>
        </form>
        <button class="btn btn-outline-secondary mt-3 mt-md-0">Filter by</button>
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
                                    <a href="{{ url($pendaftar->url_qrCode) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                                @else
                                    <span class="text-muted">Tidak Ada</span>
                                @endif
                            </td>
                            <td>
                                @if ($pendaftar->status === 'Hadir')
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

<div class="modal fade" id="qrScanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-4">
      <div class="modal-header">
        <h5 class="modal-title">Scan QR Code Kehadiran</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body text-center">
        <div id="qr-reader" style="width: 100%; max-width: 400px; aspect-ratio: 1/1; margin: 0 auto;"></div>
      </div>
    </div>
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

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    let html5QrcodeScanner;

    function onScanSuccess(decodedText, decodedResult) {
        const pendaftarId = decodedText.match(/\d+/)?.[0]; // ambil angka dari QR

        if (!pendaftarId) {
            alert("QR tidak valid.");
            return;
        }

        html5QrcodeScanner.clear().then(() => {
            fetch('{{ route("admin.markPresent") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ pendaftar_id: pendaftarId })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message || "Berhasil ditandai hadir.");
                location.reload();
            })
            .catch(err => {
                alert("Gagal update status kehadiran.");
                console.error(err);
            });
        });
    }

    const modal = document.getElementById('qrScanModal');
    modal.addEventListener('shown.bs.modal', () => {
        html5QrcodeScanner = new Html5Qrcode("qr-reader");
        html5QrcodeScanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: 250 },
            onScanSuccess
        );
    });

    modal.addEventListener('hidden.bs.modal', () => {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                html5QrcodeScanner.clear();
            }).catch(err => console.error("Stop scan gagal", err));
        }
    });
</script>

@endsection
