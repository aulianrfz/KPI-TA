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
                            <div class="mb-2">
                                <i class="bi bi-people-fill fs-2 text-info"></i>
                            </div>
                            <h6 class="text-secondary mb-1">Tim</h6>
                            <h4 class="fw-bold">{{ $timCount }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card shadow-sm border-0 rounded-4 text-center py-3 h-100">
                        <div class="card-body">
                            <div class="mb-2">
                                <i class="bi bi-person-fill fs-2 text-primary"></i>
                            </div>
                            <h6 class="text-secondary mb-1">Individu</h6>
                            <h4 class="fw-bold">{{ $individuCount }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card shadow-sm border-0 rounded-4 text-center py-3 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Peserta On-site</h6>
                                <h4 class="fw-bold text-success">{{ $pesertaOnSite }}</h4>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 3rem; height: 3rem;">
                                <i class="bi bi-person-check-fill fs-2 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card shadow-sm border-0 rounded-4 text-center py-3 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Belum Daftar Ulang</h6>
                                <h4 class="fw-bold text-danger">{{ $belumDaftarUlang }}</h4>
                            </div>
                            <div class="bg-danger bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 3rem; height: 3rem;">
                                <i class="bi bi-person-dash-fill fs-2 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card shadow-sm border-0 rounded-4 p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Total Peserta</h6>
                                <h4 class="fw-bold">{{ $totalPeserta }}</h4>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 3rem; height: 3rem;">
                                <i class="bi bi-person-circle fs-2 text-primary"></i>
                            </div>
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

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-3 gap-3">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="w-100 w-md-50">
            <div class="input-group">
                <input type="text" name="search" class="form-control border" placeholder="Cari peserta"
                    style="border-color: #0367A6;" value="{{ request('search') }}">
                <span class="input-group-text" style="background-color: #0367A6; color: white;">
                    <i class="bi bi-search"></i>
                </span>
            </div>
        </form>

        <div class="d-flex flex-column flex-md-row align-items-start gap-2 w-100 w-md-50 justify-content-md-end">
            <form method="GET" action="{{ route('admin.dashboard') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <select name="sort" class="form-select" onchange="this.form.submit()">
                    <option value="asc" {{ request('sort') === 'asc' ? 'selected' : '' }}>A-Z</option>
                    <option value="desc" {{ request('sort') === 'desc' ? 'selected' : '' }}>Z-A</option>
                </select>
            </form>

            <a href="{{ route('admin.export', ['search' => request('search'), 'sort' => request('sort')]) }}"
                class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0" style="border-left: none; border-right: none;">
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
                                @if ($pendaftar->url_qrCode && $pendaftar->peserta)
                                    <a href="{{ route('admin.peserta.identitas', ['id' => $pendaftar->peserta->id]) }}" class="btn btn-sm" style="background-color: #A6C9E5; color: #0064B6;">Lihat</a>
                                @else
                                    <span class="text-muted">Tidak Ada</span>
                                @endif

                            </td>
                            <td>
                                @if ($pendaftar->kehadiran?->status === 'Hadir')
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
        <div class="d-flex justify-content-end align-items-center mt-3 gap-2">
            <span class="small text-muted mb-0">
                Page {{ $pendaftarList->currentPage() }} of {{ $pendaftarList->lastPage() }}
            </span>
            @if ($pendaftarList->onFirstPage())
                <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">‹</span>
            @else
                <a href="{{ $pendaftarList->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">‹</a>
            @endif
            @if ($pendaftarList->hasMorePages())
                <a href="{{ $pendaftarList->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">›</a>
            @else
                <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">›</span>
            @endif
        </div>
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
    let html5QrcodeScanner = null;

    function onScanSuccess(decodedText, decodedResult) {
    let pendaftarId = null;

    try {
        const url = new URL(decodedText);
        const segments = url.pathname.split('/');

        pendaftarId = segments.pop() || segments.pop();
    } catch (e) {
        alert("QR code tidak valid (bukan URL).");
        console.error("Error parsing QR code:", e);
        return;
    }

    if (!pendaftarId) {
        alert("QR code tidak valid: tidak ada parameter 'id'.");
        return;
    }

    fetch('{{ route("admin.markPresent") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ id: pendaftarId })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message || "Berhasil ditandai hadir.");
        location.reload();
    })
    .catch(err => {
        alert("Gagal update status kehadiran.");
        console.error(err);
        if (html5QrcodeScanner) {
            html5QrcodeScanner.resume();
        }
    });
}


    const modal = document.getElementById('qrScanModal');

    modal.addEventListener('shown.bs.modal', () => {
        if (!html5QrcodeScanner) {
            html5QrcodeScanner = new Html5Qrcode("qr-reader");
        }

        html5QrcodeScanner.start(
            { facingMode: { exact: "environment" } },
            { fps: 10, qrbox: 250 },
            onScanSuccess
        ).catch(err => {
            html5QrcodeScanner.start(
                { facingMode: "user" },
                { fps: 10, qrbox: 250 },
                onScanSuccess
            ).catch(error => {
                alert("Tidak dapat mengakses kamera.");
                console.error(error);
            });
        });
    });

    modal.addEventListener('hidden.bs.modal', () => {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                html5QrcodeScanner.clear();
            }).catch(console.error);
        }
    });
</script>


@endsection
