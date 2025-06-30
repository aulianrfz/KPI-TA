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
            color: #000000;
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

        .btn-back-icon {
            font-size: 1.25rem;
            /* Ukuran ikon kembali */
            color: #6c757d;
            /* Warna abu-abu standar untuk ikon */
        }

        .btn-back-icon:hover {
            color: #3A3B7B;
            /* Warna hover konsisten dengan judul */
        }

        /* Card Styling */
        .form-card {
            background-color: #ffffff;
            border-radius: 0.75rem;
            padding: 2rem;
            /* Padding dalam card */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            /* Margin bawah untuk card */
        }

        /* Form Element Styling */
        .form-label-styled {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #495057;
            /* Warna label sedikit lebih gelap */
        }

        .form-control-custom-bg,
        .form-select-custom-bg {
            /* Tambahkan untuk select juga */
            background-color: #f8f9fa;
            /* Latar abu-abu muda untuk input/select */
            border: 1px solid #e9ecef;
            /* Border tipis */
            border-radius: 0.375rem;
            /* Bootstrap's default form-control radius */
        }

        .form-control-custom-bg:focus,
        .form-select-custom-bg:focus {
            background-color: #ffffff;
            border-color: #86b7fe;
            /* Warna border fokus Bootstrap */
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            /* Shadow fokus Bootstrap */
        }

        textarea.form-control-custom-bg {
            /* Pastikan textarea juga mendapat style */
            min-height: 100px;
            /* Tinggi minimal untuk textarea */
        }

        /* Warning Text */
        .warning-text {
            color: #dc3545;
            /* Merah untuk teks peringatan */
            font-size: 0.875em;
            /* Ukuran font lebih kecil */
            margin-top: 0.5rem;
            /* Margin atas untuk teks peringatan */
        }

        /* Button Styling */
        .btn-custom-cancel {
            background-color: #dc3545;
            /* Merah */
            color: white;
            border-color: #dc3545;
            padding: 0.5rem 1rem;
            /* Padding tombol */
        }

        .btn-custom-cancel:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
        }

        .btn-custom-save {
            background-color: #198754;
            /* Hijau */
            color: white;
            border-color: #198754;
            padding: 0.5rem 1rem;
        }

        .btn-custom-save:hover {
            background-color: #157347;
            border-color: #146c43;
        }

        .form-actions {
            /* Wrapper untuk tombol form */
            margin-top: 2rem;
            text-align: right;
            /* Tombol di kanan */
        }

        /* Modal Error Styling (Menyempurnakan yang ada) */
        .modal-overlay-custom {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            /* Defaultnya tersembunyi */
            justify-content: center;
            align-items: center;
            z-index: 1055;
            /* Di atas elemen lain, di bawah modal Bootstrap jika ada */
        }

        .modal-content-custom {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            max-width: 450px;
            width: 90%;
            text-align: center;
        }

        .modal-content-custom p {
            margin-bottom: 1.5rem;
            font-size: 1.05rem;
            /* Sedikit lebih besar dari default */
            color: #495057;
        }

        .modal-content-custom .btn {
            margin: 0.5rem;
            min-width: 100px;
            /* Lebar minimal tombol modal */
        }

        .modal-content-custom .btn-primary {
            /* Bootstrap primary color */
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .modal-content-custom .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        .modal-content-custom .btn-secondary {
            /* Bootstrap secondary color */
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .modal-content-custom .btn-secondary:hover {
            background-color: #5c636a;
            border-color: #565e64;
        }

        /* Styling untuk remove button di Peserta/Tim */
        .remove-dynamic-field {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #dc3545;
            /* Merah */
            font-weight: bold;
            font-size: 1.25rem;
            /* Sedikit lebih besar */
            user-select: none;
            line-height: 1;
        }

        .remove-dynamic-field:hover {
            color: #bb2d3b;
            /* Merah lebih gelap saat hover */
        }

        .dynamic-field-group {
            /* Untuk .peserta-group dan .tim-group */
            position: relative;
            /* max-width: 100%; Dihapus agar mengambil lebar penuh dari kolomnya */
        }

        .dynamic-field-group .form-select-custom-bg,
        /* Terapkan style ke select di dalam grup ini */
        .dynamic-field-group .form-control-custom-bg {
            padding-right: 35px;
            /* Ruang untuk tombol hapus */
        }


        /* Rata tengah untuk konten tabel di tbody */
        .table-hover tbody td {
            text-align: center;
            vertical-align: middle;
            /* Untuk alignment vertikal jika konten berbeda tinggi */
        }

        /* Untuk "Tampilkan ... entri" (selector: .dataTables_length) */
        .dataTables_length {
            padding-left: 1rem;
            /* Geser dari kiri */
            margin-bottom: 0.5rem;
            margin-top: 1rem;
        }

        /* Untuk tombol cari (selector: .dataTables_filter) */
        .dataTables_filter {
            padding-right: 1rem;
            /* Geser dari kanan */
            margin-bottom: 0.5rem;
            margin-top: 1rem;
            text-align: right;
        }

        /* Untuk "Menampilkan 1 sampai ..." (selector: .dataTables_info) */
        .dataTables_info {
            padding-left: 1rem;
        }

        /* Jika pagination terlalu mepet */
        .dataTables_paginate {
            padding-right: 1rem;
        }
    </style>

    {{-- Modal untuk mismatch venue --}}
    @if(session('venue_mismatch'))
        <div id="venueModalCustom" class="modal-overlay-custom" style="display: flex;">
            <div class="modal-content-custom">
                <p>{!! nl2br(e(session('venue_mismatch'))) !!}</p>
                <button type="button" onclick="document.getElementById('venueModalCustom').style.display = 'none';"
                    class="btn btn-primary">OK</button>
            </div>
        </div>
    @endif

    {{-- Modal untuk error_force --}}
    @if(session('error_force'))
        <div id="errorModalCustom" class="modal-overlay-custom" style="display: flex;"> {{-- Langsung tampil jika ada session
            --}}
            <div class="modal-content-custom">
                <p>{!! nl2br(e(session('error_force'))) !!}</p>
                <form method="POST" action="{{ route('jadwal.switch.proses') }}">
                    @csrf
                    <input type="hidden" name="force_switch" value="1">
                    @foreach(old() as $key => $value)
                        @if($key === '_token' || $key === '_method') @continue @endif
                        @if(is_array($value))
                            @foreach($value as $item_key => $item_value)
                                <input type="hidden" name="{{ $key }}[{{ $item_key }}]" value="{{ $item_value }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <button type="submit" class="btn btn-primary">Lanjutkan</button>
                    <button type="button" onclick="document.getElementById('errorModalCustom').style.display = 'none';"
                        class="btn btn-secondary">Batal</button>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal jika hanya satu agenda dipilih --}}
    <div id="onlyOneModal" class="modal-overlay-custom" style="display: none;">
        <div class="modal-content-custom">
            <p>Penukaran jadwal hanya dapat dilakukan jika dua agenda dipilih.</p>
            <button type="button" onclick="document.getElementById('onlyOneModal').style.display = 'none';"
                class="btn btn-primary">OK</button>
        </div>
    </div>


    <div class="container pt-3">
        <div class="d-flex align-items-center mb-3">
            <h3 class="mb-0 title-rundown">Switch Jadwal - {{ $nama_jadwal }} <small class="text-muted">({{ $tahun }} -
                    Versi {{ $version }})</small></h3>
            <a href="{{ route('jadwal.detail', [$nama_jadwal, $tahun, $version]) }}"
                class="btn btn-sm btn-outline-secondary" title="Kembali">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card shadow-sm">
            <div class="card-body p-0">

                @if($jadwals->isEmpty())
                    <div class="alert alert-warning">Tidak ada data untuk jadwal ini.</div>
                @else
                    <form method="POST" action="{{ route('jadwal.switch.proses') }}" id="switchForm">
                        @csrf
                        <div class="table-responsive">
                            <table id="switchTable" class="table table-hover align-middle mb-0">
                                <thead class="table-header-dark-custom">
                                    <tr>
                                        <th scope="col" style="width: 50px;" class="ps-3">No</th>
                                        <th scope="col" style="width: 15%;">Tanggal</th>
                                        <th scope="col" style="width: 15%;">Waktu</th>
                                        <th scope="col">Kategori Lomba</th>
                                        <th scope="col">Venue</th>
                                        <th scope="col">Kegiatan</th>
                                        <th>Peserta/Tim</th>
                                        <th>Juri</th>
                                        <th scope="col" style="width: 100px;" class="text-center pe-3">Pilih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jadwals as $jadwal)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $jadwal->tanggal ?? '-' }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($jadwal->waktu_mulai)->format('H.i') }} -
                                                {{ \Carbon\Carbon::parse($jadwal->waktu_selesai)->format('H.i') }}
                                            </td>
                                            <td>{{ $jadwal->mataLomba->nama_lomba ?? '-' }}</td>
                                            <td>{{ $jadwal->venue->name ?? '-' }}</td>
                                            <td>{{ $jadwal->kegiatan ?? '-' }}</td>
                                            <td>
                                                @php
                                                    $adaPeserta = $jadwal->peserta && $jadwal->peserta->count();
                                                    $adaTim = $jadwal->tim && $jadwal->tim->count();
                                                @endphp

                                                @if ($adaPeserta)
                                                    @foreach ($jadwal->peserta as $peserta)
                                                        {{ $peserta->nama_peserta }}<br>
                                                    @endforeach
                                                @endif

                                                @if ($adaTim)
                                                    @foreach ($jadwal->tim as $tim)
                                                        {{ $tim->nama_tim }}<br>
                                                    @endforeach
                                                @endif

                                                @if (!$adaPeserta && !$adaTim)
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $jadwal->juri->nama_juri ?? '-' }}</td>
                                            <td class="text-center pe-3">
                                                <input type="checkbox" class="form-check-input switch-checkbox"
                                                    value="{{ $jadwal->id }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{-- Tambahkan pagination --}}

                        </div>

                        <div class="mt-3 text-end pb-3 pe-3">
                            <button type="submit" class="btn btn-primary">Proses Switch</button>
                        </div>

                    </form>

                @endif
            </div>
        </div>
    </div>

    <!-- jQuery (wajib untuk versi ini) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <!-- Pakai versi default saja -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>


    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const STORAGE_KEY = 'switchSelections';
            const PAGE_KEY = 'lastPageSwitch';
            const pathNow = location.pathname;

            // 🧠 cek: kalau bukan kembali ke halaman yang sama → reset
            const lastPath = sessionStorage.getItem(PAGE_KEY);
            if (lastPath && lastPath !== pathNow) {
                sessionStorage.removeItem(STORAGE_KEY);
            }

            sessionStorage.setItem(PAGE_KEY, pathNow); // tandai halaman ini

            let selected = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]');
            const checkboxes = document.querySelectorAll('.switch-checkbox');

            checkboxes.forEach(cb => {
                if (selected.includes(cb.value)) cb.checked = true;
            });

            function updateCheckboxState() {
                checkboxes.forEach(cb => {
                    const val = cb.value;
                    if (cb.checked && !selected.includes(val)) {
                        selected.push(val);
                    }
                    if (!cb.checked && selected.includes(val)) {
                        selected = selected.filter(id => id !== val);
                    }
                });

                sessionStorage.setItem(STORAGE_KEY, JSON.stringify(selected));

                if (selected.length >= 2) {
                    checkboxes.forEach(cb => {
                        if (!selected.includes(cb.value)) cb.disabled = true;
                    });
                } else {
                    checkboxes.forEach(cb => cb.disabled = false);
                }
            }

            updateCheckboxState();
            checkboxes.forEach(cb => cb.addEventListener('change', updateCheckboxState));

            // Inisialisasi DataTables setelah DOM siap
            $('#switchTable').DataTable({
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "›",
                        previous: "‹"
                    },
                    zeroRecords: "Tidak ada data ditemukan",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                    infoFiltered: "(disaring dari _MAX_ total entri)"
                },
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                pagingType: "simple_numbers",
            });

            const form = document.getElementById('switchForm');
            form.addEventListener('submit', function (e) {
                if (selected.length !== 2) {
                    e.preventDefault(); // stop submit
                    document.getElementById('onlyOneModal').style.display = 'flex';
                    return;
                }

                selected.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'switch_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                sessionStorage.removeItem(STORAGE_KEY);
                sessionStorage.removeItem(PAGE_KEY);
            });


        })
    </script>




@endsection