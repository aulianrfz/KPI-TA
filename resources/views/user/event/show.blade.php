@extends('layouts.app')

@section('content')

<style>
    .event-image {
        object-fit: cover;
        width: 100%;
        height: 100%;
        border-radius: 1rem;
    }

    .event-info-card {
        border-radius: 1rem;
    }

    .event-title {
        font-size: 1.5rem;
    }

    .event-icon-text {
        font-size: 0.9rem;
    }

    .event-btn {
        height: 50px;
        font-size: 1rem;
    }

    .event-description-text,
    .event-section-title {
        font-size: 1rem;
    }

    @media (max-width: 576px) {
        .event-image {
            width: 100%;
            height: auto;
        }

        .event-title {
            font-size: 1rem;
        }

        .event-icon-text {
            font-size: 0.7rem;
        }

        .event-btn {
            height: 40px;
            font-size: 0.8rem;
        }

        .modal-dialog {
            max-width: 92%;
            margin: 1rem auto;
        }

        .modal-content {
            padding: 0.5rem;
        }

        .modal-title {
            font-size: 1rem;
        }

        .modal-body,
        .modal-footer,
        .modal-header {
            font-size: 0.8rem;
        }

        .modal-footer .btn,
        .modal-body .btn,
        .modal-header .btn {
            font-size: 0.8rem !important;
        }

        .form-select,
        .form-label {
            font-size: 0.8rem;
        }

        .event-description-text,
        .event-section-title {
            font-size: 0.85rem;
        }
    }
</style>


<div class="container mt-5">
    <div class="row g-2 g-md-4 align-items-center">
        <div class="col-6 col-md-6">
            <img src="{{ $events->foto ? asset('storage/' . $events->foto) : asset('images/event.jpeg') }}"
                alt="Event Image" class="event-image">
        </div>

        <div class="col-6 col-md-6">
            <div class="card shadow-sm event-info-card mb-3">
                <div class="card-body text-center p-3">
                    <h5 class="fw-bold event-title mb-4">{{ $events->nama_event }}</h5>
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                        <small class="event-icon-text">{{ $events->penyelenggara }}</small>
                    </div>

                    @php
                        use Carbon\Carbon;

                        $start = Carbon::parse($events->tanggal);
                        $end = Carbon::parse($events->tanggal_akhir);

                        if ($start->month === $end->month && $start->year === $end->year) {
                            $tanggalFormatted = $start->day . '–' . $end->day . ' ' . $start->translatedFormat('F Y');
                        } else {
                            $tanggalFormatted = $start->translatedFormat('d F Y') . ' – ' . $end->translatedFormat('d F Y');
                        }
                    @endphp

                    <div class="d-flex align-items-center justify-content-center mb-4">
                        <i class="bi bi-calendar-date text-primary me-2"></i>
                        <small class="event-icon-text">{{ $tanggalFormatted }}</small>
                    </div>

                    @php
                        $today = \Carbon\Carbon::today();
                        $endDate = \Carbon\Carbon::parse($events->tanggal_akhir);
                        $canRegister = $today->lte($endDate);
                    @endphp

                    @if ($canRegister)
                        @auth
                            <button type="button" class="btn btn-success w-100 event-btn"
                                style="background-color: #2CC384; border-color: #2CC384;" data-bs-toggle="modal"
                                data-bs-target="#modalPilihPeran">
                                Daftar Sekarang
                            </button>
                        @else
                            <button class="btn btn-secondary w-100 event-btn" id="showLoginModalBtn">
                                Daftar
                            </button>
                        @endauth
                    @else
                        <button class="btn btn-secondary w-100 event-btn" id="showEventDateModalBtn">
                            Daftar
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <h5 class="fw-semibold mb-3 event-section-title">Tentang {{ $events->nama_event }}</h5>
        <p class="event-description-text">{{ $events->deskripsi }}</p>
    </div>

</div>

<div class="modal fade" id="loginAlertModal" tabindex="-1" aria-labelledby="loginAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h5 class="modal-title">Pemberitahuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Silakan login terlebih dahulu untuk melanjutkan.
            </div>
            <div class="modal-footer">
                <a href="{{ route('login') }}" class="btn btn-primary w-100">Login</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="eventDateAlertModal" tabindex="-1" aria-labelledby="eventDateAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h5 class="modal-title">Pendaftaran Tidak Tersedia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Maaf, pendaftaran tidak bisa dilakukan karena event sudah berakhir.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary w-100" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPilihPeran" tabindex="-1" aria-labelledby="modalPilihPeranLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h5 class="modal-title">Daftar Sebagai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="peran" class="form-label">Pilih Peran</label>
                    <select class="form-select" id="peran" required>
                        <option value="" disabled selected>-- Pilih --</option>
                        <option value="peserta">Peserta</option>
                        <option value="pembimbing">Pembimbing</option>
                        <option value="supporter">Supporter</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnLanjutkan" class="btn btn-success w-100">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const loginBtn = document.getElementById('showLoginModalBtn');
        const dateBtn = document.getElementById('showEventDateModalBtn');
        const lanjutBtn = document.getElementById('btnLanjutkan');

        if (loginBtn) {
            loginBtn.addEventListener('click', () => {
                const modal = new bootstrap.Modal(document.getElementById('loginAlertModal'));
                modal.show();
            });
        }

        if (dateBtn) {
            dateBtn.addEventListener('click', () => {
                const modal = new bootstrap.Modal(document.getElementById('eventDateAlertModal'));
                modal.show();
            });
        }

        if (lanjutBtn) {
            lanjutBtn.addEventListener('click', () => {
                const peran = document.getElementById('peran').value;
                if (!peran) return alert('Silakan pilih peran terlebih dahulu.');

                const eventId = "{{ $events->id }}";
                let url = "";

                switch (peran) {
                    case 'peserta':
                        url = "{{ route('event.list', ['eventId' => $events->id]) }}";
                        break;
                    case 'pembimbing':
                        url = `/pembimbing/daftar/${eventId}`;
                        break;
                    case 'supporter':
                        url = `/supporter/daftar/${eventId}`;
                        break;
                }

                window.location.href = url;
            });
        }
    });
</script>

@endsection
