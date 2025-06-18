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
    </style>

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

    <div class="container pt-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0" style="color:#3A3B7B;">Switch Jadwal - {{ $nama_jadwal }} <small
                    class="text-muted">({{ $tahun }} - Versi {{ $version }})</small></h3>
            <a href="{{ route('jadwal.detail', [$nama_jadwal, $tahun, $version]) }}"
                class="btn btn-sm btn-outline-secondary" title="Kembali">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        @if($jadwals->isEmpty())
            <div class="alert alert-warning">Tidak ada data untuk jadwal ini.</div>
        @else
            <form method="POST" action="{{ route('jadwal.switch.proses') }}" id="switchForm">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0"> {{-- konsistenin margin dan align-middle --}}
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
                                        <input type="checkbox" name="switch_ids[]" value="{{ $jadwal->id }}"
                                            class="form-check-input switch-checkbox">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- Tambahkan pagination --}}
                    <div class="d-flex flex-column align-items-center my-3">
                        <div class="text-muted small mb-2">
                            Menampilkan {{ $jadwals->firstItem() }} sampai {{ $jadwals->lastItem() }} dari total
                            {{ $jadwals->total() }} data
                        </div>
                        {{ $jadwals->links('pagination::bootstrap-5') }}
                    </div>

                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary">Proses Switch</button>
                </div>
            </form>

        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const STORAGE_KEY = 'switchSelections';
            const checkboxes = document.querySelectorAll('.switch-checkbox');

            // restore selection dari localStorage
            const selected = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
            checkboxes.forEach(cb => {
                if (selected.includes(cb.value)) {
                    cb.checked = true;
                }
            });

            // handle toggle
            function updateCheckboxState() {
                // Ambil dari localStorage lalu update dengan nilai yang berubah di halaman ini
                let stored = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
                const pageChecked = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);

                // Gabungkan hasil dari page ini ke penyimpanan
                const combined = Array.from(new Set([...stored.filter(id => !Array.from(checkboxes).some(cb => cb.value === id)), ...pageChecked]));

                // Simpan ulang
                localStorage.setItem(STORAGE_KEY, JSON.stringify(combined));

                // Atur batas maksimal 2 checkbox yang boleh aktif
                if (combined.length >= 2) {
                    checkboxes.forEach(cb => {
                        if (!combined.includes(cb.value)) {
                            cb.disabled = true;
                        }
                    });
                } else {
                    checkboxes.forEach(cb => cb.disabled = false);
                }
            }

            // Evaluasi awal
            updateCheckboxState();

            // Saat ganti centang
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateCheckboxState);
            });

            // handle submit form
            const form = document.getElementById('switchForm');
            form.addEventListener('submit', function () {
                localStorage.removeItem(STORAGE_KEY);
            });
        });
    </script>


@endsection