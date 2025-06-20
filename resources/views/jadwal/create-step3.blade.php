@extends('layouts.apk')

@section('content')
    {{-- 1. Tambahkan CSS untuk Flatpickr --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        /* ... (semua style dari jawaban sebelumnya tetap sama) ... */
        body {
            background-color: #f4f7f6;
        }

        .card-stepper {
            background-color: #fff;
            border-radius: 1.5rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
            border: none;
            padding: 2.5rem;
        }

        .stepper-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3.5rem;
            position: relative;
        }

        .stepper-item {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .stepper-item::before {
            content: "";
            position: absolute;
            top: 15px;
            left: -50%;
            right: 50%;
            height: 3px;
            background-color: #e0e0e0;
            z-index: 1;
        }

        .stepper-item:first-child::before {
            content: none;
        }

        .stepper-item.active::before {
            background-color: #0d6efd;
        }

        .step-counter {
            height: 30px;
            width: 30px;
            border-radius: 50%;
            background: #e0e0e0;
            border: 3px solid #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            color: #fff;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .stepper-item.active .step-counter {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .form-control,
        .form-select {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #fff;
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .form-label-custom {
            font-weight: 500;
            color: #343a40;
        }

        .btn-submit {
            background-color: #17a2b8;
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #138496;
            transform: translateY(-2px);
        }

        .btn-previous {
            background-color: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 0.75rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .btn-previous:hover {
            background-color: #f8f9fa;
            border-color: #6c757d;
            color: #343a40;
        }
    </style>

    <div class="container py-5">
        <div class="row d-flex justify-content-center">
            <div class="col-xl-10 col-lg-11">
                <div class="card-stepper">
                    <div class="card-body">

                        <h2 class="text-center fw-bold mb-3">Buat Jadwal</h2>

                        <div class="stepper-wrapper">
                            <div class="stepper-item active">
                                <div class="step-counter">1</div>
                            </div>
                            <div class="stepper-item active">
                                <div class="step-counter">2</div>
                            </div>
                            <div class="stepper-item active">
                                <div class="step-counter">3</div>
                            </div>
                            <div class="stepper-item">
                                <div class="step-counter">4</div>
                            </div>
                        </div>

                        <h4 class="fw-bold mb-4 mt-5">Constraint Tambahan</h4>

                        <div class="row mb-4 gx-3">
                            <div class="col-12">
                                <div class="p-3 rounded shadow-sm"
                                    style="background-color: #e9f7fc; border-left: 5px solid #17a2b8;">
                                    <div class="row align-items-center">
                                        <label class="form-label-custom fw-bold mb-1 text-primary">Terapkan Semua</label>
                                        <div class="col-md-3">
                                            <select id="select-all-hari" class="form-select form-select-sm">
                                                <option value="">Pilih Hari</option>
                                                @foreach ($jadwalHarian as $jadwal)
                                                    <option value="{{ $jadwal['tanggal'] }}">
                                                        {{ \Carbon\Carbon::parse($jadwal['tanggal'])->translatedFormat('l, d M Y') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" id="select-all-mulai"
                                                class="form-control form-control-sm time-picker" placeholder="Mulai">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" id="select-all-selesai"
                                                class="form-control form-control-sm time-picker" placeholder="Selesai">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" id="select-all-saving" class="form-control form-control-sm"
                                                placeholder="Saving Time">
                                        </div>
                                        <div class="col-md-3 d-grid gap-2">
                                            <button type="button" class="btn btn-sm btn-info text-white"
                                                onclick="applyToAll()">
                                                Terapkan
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="clearAll()">
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <form action="{{ route('jadwal.store') }}" method="POST">
                            @csrf

                            @foreach ($mataLomba as $lomba)
                                <div class="row align-items-center mb-3 gx-3">
                                    <div class="col-md-3">
                                        <label class="form-label-custom mb-0">{{ $lomba['nama_mata_lomba'] }}</label>
                                    </div>
                                    <div class="col-md-3">
                                        <!-- <select class="form-select" name="hari[{{ $lomba['mata_lomba_id'] }}]">
                                                                                            <option value="" selected>Pilih Hari</option>
                                                                                            @foreach ($jadwalHarian as $jadwal)
                                                                                                <option value="{{ $jadwal['tanggal'] }}">
                                                                                                    {{ \Carbon\Carbon::parse($jadwal['tanggal'])->translatedFormat('l, d M Y') }}
                                                                                                </option>
                                                                                            @endforeach
                                                                                        </select> -->
                                        <select class="form-select hari-select" name="hari[{{ $lomba['mata_lomba_id'] }}]">
                                            <option value="">Pilih Hari</option>
                                            @foreach ($jadwalHarian as $jadwal)
                                                <option value="{{ $jadwal['tanggal'] }}">
                                                    {{ \Carbon\Carbon::parse($jadwal['tanggal'])->translatedFormat('l, d M Y') }}
                                                </option>
                                            @endforeach
                                        </select>

                                    </div>
                                    <div class="col-md-2">
                                        {{-- 2. Ubah type="time" menjadi type="text" dan tambahkan class="time-picker" --}}
                                        <!-- <input type="text" class="form-control time-picker"
                                                                                                                    name="waktu_mulai[{{ $lomba['mata_lomba_id'] }}]" placeholder="Mulai"> -->
                                        <input type="text" class="form-control time-picker mulai-select"
                                            name="waktu_mulai[{{ $lomba['mata_lomba_id'] }}]" placeholder="Mulai">
                                    </div>
                                    <div class="col-md-2">
                                        {{-- Lakukan hal yang sama di sini --}}
                                        <!-- <input type="text" class="form-control time-picker"
                                                                                                            name="waktu_selesai[{{ $lomba['mata_lomba_id'] }}]" placeholder="Selesai"> -->
                                        <input type="text" class="form-control time-picker selesai-select" placeholder="Selesai"
                                            name="waktu_selesai[{{ $lomba['mata_lomba_id'] }}]">
                                    </div>
                                    <div class="col-md-2">
                                        <!-- <input type="number" class="form-control"
                                                                                                    name="saving_time[{{ $lomba['mata_lomba_id'] }}]" placeholder="Saving Time"> -->
                                        <input type="number" class="form-control saving-select"
                                            name="saving_time[{{ $lomba['mata_lomba_id'] }}]" placeholder="Saving Time">
                                    </div>
                                    <!-- <div class="col-md-2">
                                                                                                                                <input type="number" class="form-control" name="round[{{ $lomba['mata_lomba_id'] }}]"
                                                                                                                                    placeholder="Round" min="1">
                                                                                                                            </div> -->
                                </div>
                            @endforeach

                            <div class="d-flex justify-content-between mt-5">
                                <a href="javascript:history.back()" class="btn btn-previous">Previous step</a>
                                <button type="submit" class="btn btn-submit">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Tambahkan script untuk Flatpickr di akhir --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Inisialisasi Flatpickr pada semua input dengan class .time-picker
        flatpickr(".time-picker", {
            enableTime: true,    // Mengaktifkan pilihan waktu
            noCalendar: true,    // Menonaktifkan pilihan tanggal
            dateFormat: "H:i",   // Format yang disimpan (H=24 jam, i=menit)
            time_24hr: true      // Memaksa tampilan UI menggunakan format 24 jam
        });
    </script>

    <script>
        flatpickr(".time-picker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });

        function applyToAll() {
            const selectedHari = document.getElementById('select-all-hari').value;
            const selectedMulai = document.getElementById('select-all-mulai').value;
            const selectedSelesai = document.getElementById('select-all-selesai').value;
            const selectedSaving = document.getElementById('select-all-saving').value;

            document.querySelectorAll('.hari-select').forEach(el => {
                if (selectedHari) el.value = selectedHari;
            });

            document.querySelectorAll('.mulai-select').forEach(el => {
                if (selectedMulai) el._flatpickr.setDate(selectedMulai, true);
            });

            document.querySelectorAll('.selesai-select').forEach(el => {
                if (selectedSelesai) el._flatpickr.setDate(selectedSelesai, true);
            });

            document.querySelectorAll('.saving-select').forEach(el => {
                if (selectedSaving) el.value = selectedSaving;
            });
        }

        function clearAll() {
            document.getElementById('select-all-hari').value = '';
            document.getElementById('select-all-mulai').value = '';
            document.getElementById('select-all-selesai').value = '';
            document.getElementById('select-all-saving').value = '';

            document.querySelectorAll('.hari-select').forEach(el => el.value = '');
            document.querySelectorAll('.mulai-select').forEach(el => el._flatpickr.clear());
            document.querySelectorAll('.selesai-select').forEach(el => el._flatpickr.clear());
            document.querySelectorAll('.saving-select').forEach(el => el.value = '');
        }

    </script>

@endsection