@extends('layouts.apk')

@section('content')

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 text-center py-4 px-2 h-100">
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
        <form method="GET" action="{{ route('dashboard.by-event', $event->id) }}" class="w-100 w-md-50">
            <div class="col-md-5 col-lg-10">
                <div class="position-relative">
                    <input
                        type="text"
                        name="search"
                        class="form-control rounded-pill ps-5"
                        placeholder="Cari peserta atau institusi..."
                        value="{{ request('search') }}"
                    >
                    <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                </div>
            </div>
        </form>

        <div class="d-flex flex-column flex-md-row align-items-start gap-2 w-100 w-md-50 justify-content-md-end">
            <a href="{{ route('admin.export', ['search' => request('search'), 'sort' => request('sort')]) }}"
                class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
            <form method="GET" action="{{ route('dashboard.by-event', $event->id) }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <select name="sort" class="form-select" onchange="this.form.submit()">
                    <option value="asc" {{ request('sort') === 'asc' ? 'selected' : '' }}>Terlama</option>
                    <option value="desc" {{ request('sort') === 'desc' ? 'selected' : '' }}>Terbaru</option>
                </select>
            </form>
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
                            <td>{{ \Carbon\Carbon::parse($pendaftar->updated_at)->translatedFormat('l, d-m-Y') }}</td>
                            <td>
                                @if ($pendaftar->url_qrCode && $pendaftar->peserta)
                                    <a href="{{ route('admin.peserta.identitas', ['id' => $pendaftar->peserta->id]) }}" class="btn btn-sm" style="background-color: #A6C9E5; color: #0064B6;">Lihat</a>
                                @else
                                    <span class="text-muted">Tidak Ada</span>
                                @endif

                            </td>
                            <td>
                                @if ($pendaftar->status_kehadiran === 'Hadir')
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

<div class="modal fade" id="scanResultModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4">
      <div class="modal-header">
        <h5 class="modal-title">Status Kehadiran</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body text-center">
        <p id="statusKehadiranText" class="fw-bold mb-3 text-success"></p>
        <p>Nama Peserta:</p>
        <h5 id="namaPesertaText" class="text-primary"></h5>
        <p>
        Nama Lomba:
        <span id="namaMataLombaText" class="text-primary"></span>
        </p>
        <img id="fotoKtmPreview" src="/images/default-ktm.png" alt="Foto KTM" class="img-fluid rounded" style="max-height: 250px;">
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="scanErrorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4">
      <div class="modal-header">
        <h5 class="modal-title text-danger">Data Tidak Ditemukan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body text-center">
        <p id="scanErrorText" class="fw-semibold text-danger mb-0">Maaf, data tidak ditemukan atau tidak valid.</p>
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
    let scanInProgress = false;

    function showErrorModal(message = "Maaf, data tidak ditemukan atau tidak valid.") {
        document.getElementById("scanErrorText").innerText = message;

        // Tutup modal scan QR
        const scanModal = bootstrap.Modal.getInstance(document.getElementById('qrScanModal'));
        if (scanModal) scanModal.hide();

        // Tampilkan modal error
        const errorModal = new bootstrap.Modal(document.getElementById('scanErrorModal'));
        errorModal.show();

        stopScanner();
    }

    function stopScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                html5QrcodeScanner.clear();
                html5QrcodeScanner = null;
                scanInProgress = false;
            }).catch(err => {
                console.error("Gagal stop scanner:", err);
                scanInProgress = false;
            });
        } else {
            scanInProgress = false;
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        if (scanInProgress) return;
        scanInProgress = true;

        let pendaftarId = null;

        try {
            const url = new URL(decodedText);
            const segments = url.pathname.split('/').filter(Boolean);
            pendaftarId = segments.pop();
        } catch (e) {
            showErrorModal("QR code tidak valid (bukan URL).");
            return;
        }

        if (!pendaftarId) {
            showErrorModal("QR code tidak valid: Data tidak sesuai.");
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
            const statusText = document.getElementById("statusKehadiranText");
            const namaText = document.getElementById("namaPesertaText");
            const namaLombaText = document.getElementById("namaMataLombaText");
            const fotoImg = document.getElementById("fotoKtmPreview");

            if (!data || data.error || !data.nama_peserta) {
                const errorMessage = data.error || "Data peserta tidak ditemukan.";
                showErrorModal(errorMessage);
                return;
            }

            namaText.innerText = data.nama_peserta || '-';
            namaLombaText.innerText = data.nama_lomba || '-';
            fotoImg.src = data.foto_ktm || '/images/default-ktm.png';

            if (data.message?.includes('sudah')) {
                statusText.innerText = "Peserta sudah ditandai hadir sebelumnya.";
                statusText.classList.remove('text-success');
                statusText.classList.add('text-warning');
            } else {
                statusText.innerText = "Kehadiran berhasil dicatat!";
                statusText.classList.remove('text-warning');
                statusText.classList.add('text-success');
            }

            bootstrap.Modal.getInstance(document.getElementById('qrScanModal')).hide();

            const resultModal = new bootstrap.Modal(document.getElementById('scanResultModal'));
            resultModal.show();

            stopScanner();
        })
        .catch(err => {
            console.error("Gagal memproses kehadiran:", err);
            showErrorModal("Gagal memproses kehadiran.");
        });
    }

    const modal = document.getElementById('qrScanModal');

    modal.addEventListener('shown.bs.modal', () => {
        const fotoImg = document.getElementById("fotoKtmPreview");
        fotoImg.src = '/images/default-ktm.png';

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
                console.error("Kamera tidak dapat diakses:", error);
                showErrorModal("Tidak dapat mengakses kamera.");
            });
        });
    });

    modal.addEventListener('hidden.bs.modal', stopScanner);

    document.getElementById('scanResultModal').addEventListener('hidden.bs.modal', function () {
        location.reload();
    });

    document.getElementById('scanErrorModal').addEventListener('hidden.bs.modal', function () {
        scanInProgress = false;
    });
</script>




@endsection
