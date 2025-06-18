@extends('layouts.apk')

@section('content')
<style>
    /* Style untuk Card agar sesuai dengan UI */
    .card-stepper {
        border-radius: 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: none;
        padding: 2rem;
    }

    /* CSS untuk Stepper */
    .stepper-wrapper { display: flex; justify-content: space-between; margin-bottom: 2.5rem; position: relative; }
    .stepper-item { position: relative; display: flex; flex-direction: column; align-items: center; flex: 1; text-align: center; }
    .stepper-item .step-counter { height: 2.5rem; width: 2.5rem; border-radius: 50%; background: #ffffff; border: 2px solid #e0e0e0; display: flex; justify-content: center; align-items: center; font-weight: bold; color: #e0e0e0; z-index: 2; }
    .stepper-item::after { content: ''; position: absolute; top: 1.25rem; left: 50%; height: 2px; width: 100%; background-color: #e0e0e0; z-index: 1; }
    .stepper-item:last-child::after { display: none; }
    .stepper-item.active .step-counter { background-color: #0d6efd; border-color: #0d6efd; color: #ffffff; }
    .stepper-item.active::after { background-color: #0d6efd; }
    .footer-action { display: flex; justify-content: flex-end; margin-top: 1.5rem; }

    /* ====================================================== */
    /* == CSS PERBAIKAN FINAL UNTUK ALIGNMENT TOOLBAR      == */
    /* ====================================================== */
    #calendar { border: none; }
    .fc .fc-toolbar.fc-header-toolbar { margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #dee2e6; }

    /* 1. Atur container utama (chunk) dengan flexbox */
    .fc .fc-toolbar-chunk {
        display: flex;
        align-items: center; /* KUNCI UTAMA: Sejajarkan semua item di dalamnya secara vertikal */
    }

    /* 2. Beri style pada Judul, pastikan line-height-nya konsisten */
    .fc .fc-toolbar-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #333;
        margin: 0 0.5rem; /* Beri sedikit jarak */
        line-height: 1;   /* Samakan line-height */
    }

    /* 3. Atur Tombol Navigasi secara spesifik (INI BAGIAN PALING PENTING) */
    .fc .fc-prev-button,
    .fc .fc-next-button {
        /* Jadikan tombol sebagai flex container untuk menengahkan icon di dalamnya */
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        
        /* Beri ukuran eksplisit agar kotak tombol sama besar */
        width: 35px;
        height: 35px;
        
        background: none !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        color: #555 !important;
        border-radius: 50%; /* Opsi: buat tombol jadi lingkaran agar lebih rapi */
        transition: background-color 0.2s;
    }

    /* Opsi: Beri hover effect agar interaktif */
    .fc .fc-prev-button:hover,
    .fc .fc-next-button:hover {
        background-color: #f0f0f0 !important;
    }

    /* 4. Atur icon di dalam tombol */
    .fc-icon-chevron-left::before,
    .fc-icon-chevron-right::before {
        font-family: monospace; /* Gunakan font yang karakternya lebih seimbang */
        font-size: 1.5rem;      /* Sesuaikan ukuran icon */
        font-weight: bold;
        line-height: 1;         /* Pastikan icon pas di tengah */
    }
    
    .fc-icon-chevron-left::before { content: '<'; }
    .fc-icon-chevron-right::before { content: '>'; }
    /* ====================================================== */
    /* == AKHIR DARI CSS PERBAIKAN                         == */
    /* ====================================================== */

    .fc .fc-today-button { background: none !important; border: none !important; color: #555 !important; font-size: 0.9em !important; text-transform: capitalize !important; padding: 0 !important; }
    .fc .fc-today-button:disabled { color: #aaa !important; }
    .fc .fc-col-header-cell { background-color: #f8f9fa; border: none; padding: 1rem 0; font-weight: 600; text-transform: uppercase; color: #6c757d; }
    .fc .fc-daygrid-day, .fc .fc-col-header-cell { border: 1px solid #f0f0f0; }
    .fc th { border-bottom: none !important; }
    .fc .fc-day-other { background-color: #fff; background-image: repeating-linear-gradient( 45deg, #f9f9f9, #f9f9f9 5px, #ffffff 5px, #ffffff 10px ); }
    .fc .fc-day-other .fc-daygrid-day-number { color: #ccc !important; }
    .fc-daygrid-day-number { color: black !important; text-decoration: none !important; }
</style>

{{-- Sisa dari file blade Anda (HTML, Modal, Script) tetap sama --}}
<div class="container py-5">
    <div class="row d-flex justify-content-center">
        <div class="col-md-10 col-lg-9">
            <div class="card card-stepper">
                <div class="card-body">
                    <h2 class="text-center fw-bold mb-4">Buat Jadwal</h2>
                    <div class="stepper-wrapper">
                        <div class="stepper-item active"><div class="step-counter">1</div></div>
                        <div class="stepper-item"><div class="step-counter">2</div></div>
                        <div class="stepper-item"><div class="step-counter">3</div></div>
                        <div class="stepper-item"><div class="step-counter">4</div></div>
                    </div>
                    <form action="{{ route('jadwal.create.step2') }}" method="POST" id="jadwalForm">
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger mb-4">
                                <strong class="d-block">Whoops! Terjadi kesalahan.</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="form-group mb-4">
                            <label for="nama_jadwal" class="form-label fw-bold">Nama Jadwal</label>
                            <input type="text" name="nama_jadwal" id="nama_jadwal"
                                class="form-control @error('nama_jadwal') is-invalid @enderror" required
                                value="{{ old('nama_jadwal') }}" placeholder="Contoh: KPI 15">
                            @error('nama_jadwal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <label class="form-label fw-bold">Pilih Waktu Event</label>
                        <div id="calendar" class="mb-4"></div>
                        <div id="hiddenInputs"></div>
                        <div class="footer-action">
                            <button type="submit" class="btn btn-primary btn-lg px-4">Next</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="timeModal" tabindex="-1" aria-labelledby="timeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered d-flex justify-content-center" role="document">
        <form id="timeForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Input Waktu untuk <span id="modalDateDisplay"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row">
                    <input type="hidden" id="modalDate">
                    <div class="form-group col-md-6">
                        <label for="waktuMulai">Waktu Mulai</label>
                        <input type="text" id="waktuMulai" class="form-control" required placeholder="HH:mm" maxlength="5" autocomplete="off">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="waktuSelesai">Waktu Selesai</label>
                        <input type="text" id="waktuSelesai" class="form-control" required placeholder="HH:mm" maxlength="5" autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const hiddenInputs = document.getElementById('hiddenInputs');
        const oldTanggal = @json(old('tanggal', []));
        const oldWaktuMulai = @json(old('waktu_mulai', []));
        const oldWaktuSelesai = @json(old('waktu_selesai', []));
        let selectedEvents = {};

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            selectable: true,
            headerToolbar: {
                left: 'today',
                center: 'prev,title,next',
                right: ''
            },
            buttonText: {
                today: 'Today',
            },
            dateClick: function(info) {
                const dateStr = info.dateStr;
                if (info.dayEl.classList.contains('fc-day-other')) { return; }
                if (selectedEvents[dateStr]) {
                    calendar.getEventById(dateStr)?.remove();
                    delete selectedEvents[dateStr];
                    document.getElementById('input-' + dateStr)?.remove();
                    return;
                }
                document.getElementById('modalDate').value = dateStr;
                document.getElementById('waktuMulai').value = '';
                document.getElementById('waktuSelesai').value = '';
                document.getElementById('modalDateDisplay').textContent = dateStr;
                new bootstrap.Modal(document.getElementById('timeModal')).show();
            }
        });

        function addEventToCalendar(date, start, end) {
            selectedEvents[date] = { start: start, end: end };
            calendar.addEvent({
                id: date,
                start: date,
                display: 'background',
                color: '#dbeaff'
            });
            hiddenInputs.insertAdjacentHTML('beforeend', `<div id="input-${date}"><input type="hidden" name="tanggal[]" value="${date}"><input type="hidden" name="waktu_mulai[]" value="${start}"><input type="hidden" name="waktu_selesai[]" value="${end}"></div>`);
        }

        for (let i = 0; i < oldTanggal.length; i++) {
            addEventToCalendar(oldTanggal[i], oldWaktuMulai[i], oldWaktuSelesai[i]);
        }
        document.getElementById('timeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const date = document.getElementById('modalDate').value;
            const waktuMulai = document.getElementById('waktuMulai').value;
            const waktuSelesai = document.getElementById('waktuSelesai').value;
            if (!waktuMulai || !waktuSelesai) return alert("Waktu tidak boleh kosong");
            if (!isValidTimeFormat(waktuMulai) || !isValidTimeFormat(waktuSelesai)) { return alert("Format waktu salah. Gunakan format HH:mm"); }
            if (selectedEvents[date]) {
                calendar.getEventById(date)?.remove();
                document.getElementById('input-' + date)?.remove();
            }
            addEventToCalendar(date, waktuMulai, waktuSelesai);
            bootstrap.Modal.getInstance(document.getElementById('timeModal')).hide();
        });
        calendar.render();
        function isValidTimeFormat(value) {
            const regex = /^([01]\d|2[0-3]):([0-5]\d)$/;
            return regex.test(value);
        }
        function restrictTimeInput(input) {
            input.addEventListener('input', function() {
                let raw = this.value.replace(/\D/g, '');
                if (raw.length >= 3) {
                    this.value = raw.slice(0, 2) + ':' + raw.slice(2, 4);
                } else { this.value = raw; }
            });
            input.addEventListener('blur', function() {
                if (this.value === '') return;
                if (!isValidTimeFormat(this.value)) {
                    alert("Format waktu salah. Gunakan format HH:mm");
                    this.focus();
                }
            });
        }
        restrictTimeInput(document.getElementById('waktuMulai'));
        restrictTimeInput(document.getElementById('waktuSelesai'));
    });
</script>

@endsection