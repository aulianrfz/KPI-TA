@extends('layouts.apk')

@section('content')
    <style>
        /* General Page Styles */
        .form-page-title-container {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            /* Adjusted from 2rem to better fit typical spacing */
        }

        .form-page-title {
            color: #3A3B7B;
            /* Warna biru tua/ungu, konsisten dengan halaman sebelumnya */
            font-weight: 600;
            font-size: 1.75rem;
            /* Ukuran judul utama */
            margin-bottom: 0;
            /* Hapus margin bawah default dari h2 */
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

    <div class="container">
        {{-- Judul Halaman dan Tombol Kembali --}}
        <div class="form-page-title-container">
            {{-- Menggunakan $jadwal->id untuk route jadwal.detail --}}
            <a href="{{ route('jadwal.detail', ['id' => $jadwal->id]) }}" class="btn btn-link p-0 me-3"
                title="Kembali ke Detail Jadwal">
                <i class="fas fa-arrow-left btn-back-icon"></i>
            </a>
            <h2 class="form-page-title">Edit Jadwal</h2>
        </div>

        {{-- Modal untuk error_force --}}
        @if(session('error_force'))
            <div id="errorModalCustom" class="modal-overlay-custom" style="display: flex;"> {{-- Langsung tampil jika ada
                session --}}
                <div class="modal-content-custom">
                    <p>{!! nl2br(e(session('error_force'))) !!}</p>
                    <form method="POST" action="{{ route('jadwal.update', $agenda->id) }}">
                        @csrf
                        @method('PUT') {{-- Pastikan method PUT untuk update --}}
                        {{-- Loop untuk mempertahankan old input --}}
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
                        <input type="hidden" name="force" value="1">
                        {{-- Sertakan juga agenda_id dan field lain yang mungkin diperlukan dari form utama --}}
                        <input type="hidden" name="agenda_id" value="{{ old('agenda_id', $agenda->id) }}">
                        <input type="hidden" name="mata_lomba_id" value="{{ old('mata_lomba_id', $agenda->mata_lomba_id) }}">
                        <input type="hidden" name="tanggal_dropdown"
                            value="{{ old('tanggal_dropdown', $agenda->tanggal == old('tanggal_dropdown', $agenda->tanggal) && !old('lainnya') ? $agenda->tanggal : (old('lainnya') ? 'lainnya' : '')) }}">
                        <input type="hidden" name="tanggal" value="{{ old('tanggal', $agenda->tanggal) }}">
                        <input type="hidden" name="waktu_mulai" value="{{ old('waktu_mulai', $agenda->waktu_mulai) }}">
                        <input type="hidden" name="waktu_selesai" value="{{ old('waktu_selesai', $agenda->waktu_selesai) }}">
                        <input type="hidden" name="kegiatan" value="{{ old('kegiatan', $agenda->kegiatan) }}">
                        <input type="hidden" name="venue_id" value="{{ old('venue_id', $agenda->venue_id) }}">
                        <input type="hidden" name="juri_id" value="{{ old('juri_id', $agenda->juri_id) }}">

                        <button type="submit" class="btn btn-primary">Lanjutkan</button>
                        <button type="button" onclick="document.getElementById('errorModalCustom').style.display = 'none';"
                            class="btn btn-secondary">Batal</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="form-card">
            <form action="{{ route('jadwal.update', $agenda->id) }}" method="POST">
                @csrf
                @method('PUT')

                <input type="hidden" name="agenda_id" value="{{ $agenda->id }}">
                {{-- Informasi Jadwal Utama (jika perlu ditampilkan, read-only) --}}
                <p class="mb-3">
                    <small class="text-muted">Mengedit entri untuk jadwal: <strong>{{ $jadwal->nama_jadwal }}</strong>
                        (Versi {{ $jadwal->version }}, Tahun {{ $jadwal->tahun }})
                        <br>Tanggal Awal: {{ \Carbon\Carbon::parse($agenda->tanggal)->translatedFormat('d F Y') }}</small>
                </p>
                <hr class="mb-4">


                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="mata_lomba_id" class="form-label form-label-styled">Kategori Lomba</label>
                            <select name="mata_lomba_id" id="mata_lomba_id"
                                class="form-select form-select-custom-bg @error('mata_lomba_id') is-invalid @enderror">
                                <option value="">- Pilih Kategori Lomba -</option>
                                @foreach($mata_lomba as $item)
                                    <option value="{{ $item->id }}" {{ old('mata_lomba_id', $agenda->mata_lomba_id) == $item->id ? 'selected' : '' }}>
                                        {{ $item->nama_lomba }}
                                    </option>
                                @endforeach
                            </select>
                            @error('mata_lomba_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="kegiatan" class="form-label form-label-styled">Kegiatan</label>
                            <input type="text" name="kegiatan" id="kegiatan"
                                class="form-control form-control-custom-bg @error('kegiatan') is-invalid @enderror"
                                value="{{ old('kegiatan', $agenda->kegiatan) }}" placeholder="Nama Kegiatan">
                            @error('kegiatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="waktu_mulai" class="form-label form-label-styled">Waktu Mulai</label>
                            <input type="text" name="waktu_mulai" id="waktu_mulai"
                                class="form-control form-control-custom-bg @error('waktu_mulai') is-invalid @enderror"
                                placeholder="HH:mm" maxlength="5" autocomplete="off" required
                                value="{{ old('waktu_mulai', $agenda->waktu_mulai) }}">
                            @error('waktu_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="waktu_selesai" class="form-label form-label-styled">Waktu Selesai</label>
                            <input type="text" name="waktu_selesai" id="waktu_selesai"
                                class="form-control form-control-custom-bg @error('waktu_selesai') is-invalid @enderror"
                                placeholder="HH:mm" maxlength="5" autocomplete="off" required
                                value="{{ old('waktu_selesai', $agenda->waktu_selesai) }}">
                            @error('waktu_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <!-- <p class="warning-text mb-3"><small>* Jadwal akan otomatis bergeser ke bawah jika terjadi bentrok waktu pada tanggal dan venue yang sama.</small></p> -->


                <div class="mb-3">
                    <label for="tanggal_dropdown" class="form-label form-label-styled">Tanggal</label>
                    <select name="tanggal_dropdown" id="tanggal_dropdown"
                        class="form-select form-select-custom-bg @error('tanggal') is-invalid @enderror @error('tanggal_dropdown') is-invalid @enderror"
                        onchange="toggleCustomDate(this.value)">
                        <option value="">-- Pilih Tanggal --</option>
                        @foreach($tanggal_unik as $tanggal_option)
                            <option value="{{ $tanggal_option }}" {{ old('tanggal', $agenda->tanggal) == $tanggal_option && old('tanggal_dropdown') != 'lainnya' ? 'selected' : (old('tanggal_dropdown') == $tanggal_option ? 'selected' : '') }}>
                                {{ \Carbon\Carbon::parse($tanggal_option)->translatedFormat('d F Y') }}
                            </option>
                        @endforeach
                        <option value="lainnya" {{ old('tanggal_dropdown') == 'lainnya' || (!in_array(old('tanggal', $agenda->tanggal), $tanggal_unik->toArray()) && !old('tanggal_dropdown')) ? 'selected' : '' }}>
                            Tanggal Lainnya
                        </option>
                    </select>
                    @error('tanggal_dropdown') <div class="invalid-feedback">{{ $message }}</div> @enderror

                    <input type="date" name="tanggal" id="customDate"
                        class="form-control form-control-custom-bg mt-2 @error('tanggal') is-invalid @enderror"
                        style="{{ old('tanggal_dropdown') == 'lainnya' || (!in_array(old('tanggal', $agenda->tanggal), $tanggal_unik->toArray()) && !old('tanggal_dropdown')) ? 'display:block;' : 'display:none;' }}"
                        value="{{ old('tanggal', $agenda->tanggal) }}">
                    @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>


                <div class="mb-3">
                    <label for="venue_id" class="form-label form-label-styled">Venue</label>
                    <select name="venue_id" id="venue_id"
                        class="form-select form-select-custom-bg @error('venue_id') is-invalid @enderror">
                        <option value="">- Pilih Venue -</option>
                        @foreach($venue as $item)
                            <option value="{{ $item->id }}" {{ old('venue_id', $agenda->venue_id) == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('venue_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Peserta --}}
                <div class="mb-3">
                    <label class="form-label form-label-styled">Peserta</label>
                    <div id="peserta-wrapper">
                        @php $old_peserta = old('peserta_id', $peserta_terpilih ?: []); @endphp
                        @if(count($old_peserta) > 0)
                            @foreach($old_peserta as $index => $pesertaId)
                                <div class="dynamic-field-group mb-2">
                                    <select name="peserta_id[]" class="form-select form-select-custom-bg">
                                        <option value="">- Pilih Peserta -</option>
                                        @foreach($peserta as $item)
                                            <option value="{{ $item->id }}" {{ $pesertaId == $item->id ? 'selected' : '' }}>
                                                {{ $item->nama_peserta }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($index >= 0 || count($old_tim) >= 1) {{-- Tombol hapus hanya jika bukan yang pertama ATAU
                                        ada lebih dari 1 --}}
                                        <span class="remove-dynamic-field remove-peserta" title="Hapus peserta">&times;</span>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="dynamic-field-group mb-2">
                                <select name="peserta_id[]" class="form-select form-select-custom-bg">
                                    <option value="" selected>- Pilih Peserta -</option>
                                    @foreach($peserta as $item)
                                        <option value="{{ $item->id }}">{{ $item->nama_peserta }}</option>
                                    @endforeach
                                </select>
                                {{-- Tombol hapus tidak ditampilkan untuk field pertama yang kosong --}}
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mt-2" id="add-peserta">Tambah Peserta</button>
                </div>

                {{-- Tim --}}
                <div class="mb-3">
                    <label class="form-label form-label-styled">Tim</label>
                    <div id="tim-wrapper">
                        @php $old_tim = old('tim_id', $tim_terpilih ?: []); @endphp
                        @if(count($old_tim) > 0)
                            @foreach($old_tim as $index => $timId)
                                <div class="dynamic-field-group mb-2">
                                    <select name="tim_id[]" class="form-select form-select-custom-bg">
                                        <option value="">- Pilih Tim -</option>
                                        @foreach($tim as $item)
                                            <option value="{{ $item->id }}" {{ $timId == $item->id ? 'selected' : '' }}>
                                                {{ $item->nama_tim }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($index >= 0 || count($old_tim) >= 1)
                                        <span class="remove-dynamic-field remove-tim" title="Hapus tim">&times;</span>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="dynamic-field-group mb-2">
                                <select name="tim_id[]" class="form-select form-select-custom-bg">
                                    <option value="" selected>- Pilih Tim -</option>
                                    @foreach($tim as $item)
                                        <option value="{{ $item->id }}">{{ $item->nama_tim }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mt-2" id="add-tim">Tambah Tim</button>
                </div>

                <div class="mb-3">
                    <label for="juri_id" class="form-label form-label-styled">Juri</label>
                    <select name="juri_id" id="juri_id"
                        class="form-select form-select-custom-bg @error('juri_id') is-invalid @enderror">
                        <option value="">- Pilih Juri -</option>
                        @foreach($juri as $item)
                            <option value="{{ $item->id }}" {{ old('juri_id', $agenda->juri_id) == $item->id ? 'selected' : '' }}>
                                {{ $item->nama_juri }}
                            </option>
                        @endforeach
                    </select>
                    @error('juri_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-actions">
                    {{-- Menggunakan $jadwal->id untuk route jadwal.detail --}}
                    <a href="{{ route('jadwal.detail', ['id' => $jadwal->id]) }}"
                        class="btn btn-custom-cancel me-2">Batal</a>
                    <button type="submit" class="btn btn-custom-save">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fungsi untuk modal error_force (jika masih menggunakan custom modal)
        // window.addEventListener('DOMContentLoaded', (event) => {
        //     const modal = document.getElementById('errorModalCustom');
        //     if (modal && modal.style.display === 'flex') { // Cek jika modal seharusnya tampil
        //         // Tidak perlu aksi tambahan jika sudah display:flex dari inline style PHP
        //     }
        // });
        // Fungsi closeModal sudah diganti dengan inline onclick pada tombol batal modal

        function toggleCustomDate(value) {
            const customDateInput = document.getElementById('customDate');
            const tanggalDropdown = document.getElementById('tanggal_dropdown');

            if (value === 'lainnya') {
                customDateInput.style.display = 'block';
                customDateInput.required = true;
                // Jika dropdown sebelumnya punya nilai valid dan bukan 'lainnya', jangan set nilai customDate dari dropdown
                // Biarkan value dari old('tanggal') atau $agenda->tanggal yang sudah di-set di input
            } else {
                customDateInput.style.display = 'none';
                customDateInput.required = false;
                customDateInput.value = ''; // Kosongkan jika bukan 'lainnya'
                // Jika user memilih tanggal dari dropdown, update nilai input 'tanggal' yang tersembunyi (jika ada)
                // atau pastikan controller menghandle 'tanggal_dropdown' jika bukan 'lainnya'
            }
        }
        // Panggil saat load untuk memastikan state awal benar berdasarkan old input atau data agenda
        document.addEventListener('DOMContentLoaded', function () {
            const initialTanggalDropdownValue = document.getElementById('tanggal_dropdown').value;
            toggleCustomDate(initialTanggalDropdownValue);

            // Logika untuk Peserta
            document.getElementById('add-peserta').addEventListener('click', function () {
                const wrapper = document.getElementById('peserta-wrapper');
                const newIndex = wrapper.getElementsByClassName('dynamic-field-group').length;
                const div = document.createElement('div');
                div.classList.add('dynamic-field-group', 'mb-2');

                let selectHTML = `<select name="peserta_id[]" class="form-select form-select-custom-bg">
                                    <option value="" selected>- Pilih Peserta -</option>`;
                @foreach($peserta as $item)
                    selectHTML += `<option value="{{ $item->id }}">{{ $item->nama_peserta }}</option>`;
                @endforeach
                selectHTML += `</select>`;

                // Tambah tombol hapus hanya jika ini bukan field pertama yang ditambahkan secara dinamis
                // atau jika sudah ada field sebelumnya (termasuk yang dari old())
                const removeButtonHTML = `<span class="remove-dynamic-field remove-peserta" title="Hapus peserta">&times;</span>`;

                div.innerHTML = selectHTML + removeButtonHTML;
                wrapper.appendChild(div);
            });

            document.getElementById('peserta-wrapper').addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-peserta')) {
                    // Hanya hapus jika ada lebih dari satu field peserta atau jika ini bukan field pertama yang kosong
                    const parentWrapper = e.target.closest('#peserta-wrapper');
                    if (parentWrapper.getElementsByClassName('dynamic-field-group').length > 1) {
                        e.target.parentElement.remove();
                    } else {
                        // Jika ini satu-satunya field, kosongkan nilainya saja
                        const selectElement = e.target.parentElement.querySelector('select');
                        if (selectElement) {
                            selectElement.value = "";
                        }
                    }
                }
            });

            // Logika untuk Tim (mirip dengan Peserta)
            document.getElementById('add-tim').addEventListener('click', function () {
                const wrapper = document.getElementById('tim-wrapper');
                const newIndex = wrapper.getElementsByClassName('dynamic-field-group').length;
                const div = document.createElement('div');
                div.classList.add('dynamic-field-group', 'mb-2');

                let selectHTML = `<select name="tim_id[]" class="form-select form-select-custom-bg">
                                    <option value="" selected>- Pilih Tim -</option>`;
                @foreach($tim as $item)
                    selectHTML += `<option value="{{ $item->id }}">{{ $item->nama_tim }}</option>`;
                @endforeach
                selectHTML += `</select>`;

                const removeButtonHTML = `<span class="remove-dynamic-field remove-tim" title="Hapus tim">&times;</span>`;

                div.innerHTML = selectHTML + removeButtonHTML;
                wrapper.appendChild(div);
            });

            document.getElementById('tim-wrapper').addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-tim')) {
                    const parentWrapper = e.target.closest('#tim-wrapper');
                    if (parentWrapper.getElementsByClassName('dynamic-field-group').length > 1) {
                        e.target.parentElement.remove();
                    } else {
                        const selectElement = e.target.parentElement.querySelector('select');
                        if (selectElement) {
                            selectElement.value = "";
                        }
                    }
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function autoFormatTime(input) {
                input.addEventListener('input', function (e) {
                    let value = this.value.replace(/[^0-9]/g, ''); // hanya angka
                    if (value.length > 4) {
                        value = value.slice(0, 4); // maksimal 4 angka
                    }

                    if (value.length >= 3) {
                        this.value = value.slice(0, 2) + ':' + value.slice(2);
                    } else {
                        this.value = value;
                    }
                });
            }

            autoFormatTime(document.getElementById('waktu_mulai'));
            autoFormatTime(document.getElementById('waktu_selesai'));
        });
    </script>
@endsection