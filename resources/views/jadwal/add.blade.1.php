@extends('layouts.apk')

@section('content')
    @if(session('error_force'))
        <div id="errorModal" class="modal-overlay">
            <div class="modal-content">
                <p>{{ session('error_force') }}</p>
                <form method="POST" action="{{ route('jadwal.add') }}">
                    @csrf
                    @foreach(old() as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $item)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <input type="hidden" name="force" value="1">

                    <button type="submit" class="btn btn-primary">Lanjutkan</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Batal</button>
                </form>
            </div>
        </div>
    @endif

    <div class="container">
        <h2>Tambah Jadwal - {{ $nama_jadwal }} | Tahun {{ $tahun }} | Versi {{ $version }}</h2>

        <form action="{{ route('jadwal.add') }}" method="POST">
            @csrf
            <input type="hidden" name="nama_jadwal" value="{{ $nama_jadwal }}">
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <input type="hidden" name="version" value="{{ $version }}">

            <div class="form-group">
                <label>Sub Kategori</label>
                <select name="mata_lomba_id" class="form-control">
                    <option value="">- Pilih Sub Kategori -</option>
                    @foreach($mata_lomba as $item)
                        <option value="{{ $item->id }}" {{ old('mata_lomba_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->nama_lomba }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tambahan: Tanggal --}}
            <div class="form-group">
                <label>Tanggal</label>
                <select name="tanggal_dropdown" class="form-control" onchange="toggleCustomDate(this.value)">
                    <option value="">-- Pilih Tanggal --</option>
                    @foreach($tanggal_unik as $tanggal)
                        <option value="{{ $tanggal }}">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</option>
                    @endforeach
                    <option value="lainnya">Tanggal Lainnya</option>
                </select>

                <input type="date" name="tanggal" id="customDate" class="form-control mt-2" style="display: none;">
            </div>

            <div class="form-group">
                <label>Waktu Mulai</label>
                <input type="time" name="waktu_mulai" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Waktu Selesai</label>
                <input type="time" name="waktu_selesai" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Kegiatan</label>
                <textarea name="kegiatan" class="form-control" rows="3">{{ old('kegiatan') }}</textarea>
            </div>

            <div class="form-group">
                <label>Venue</label>
                <select name="venue_id" class="form-control">
                    <option value="">- Pilih Venue -</option>
                    @foreach($venue as $item)
                        <option value="{{ $item->id }}" {{ old('venue_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Peserta</label>
                <div id="peserta-wrapper">
                    @php
                        $oldPeserta = old('peserta_id', []);
                    @endphp

                    @if(count($oldPeserta) > 0)
                        @foreach($oldPeserta as $index => $pesertaId)
                            <div class="peserta-group mb-2" style="position: relative; max-width: 300px;">
                                <select name="peserta_id[]" class="form-control">
                                    <option value="">- Pilih Peserta -</option>
                                    @foreach($peserta as $item)
                                        <option value="{{ $item->id }}" {{ (isset($pesertaId) && $pesertaId == $item->id) ? 'selected' : '' }}>
                                            {{ $item->nama_peserta }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="remove-peserta" title="Hapus peserta"
                                    style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                                           cursor:pointer; color:red; font-weight:bold; font-size:18px; user-select:none;">&times;</span>
                            </div>
                        @endforeach
                    @else
                        <div class="peserta-group mb-2">
                            <select name="peserta_id[]" class="form-control">
                                <option value="" selected disabled>Pilih Peserta</option>
                                @foreach($peserta as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama_peserta }}</option>
                                @endforeach
                            </select>
                            <span class="remove-peserta" title="Hapus peserta"
                                style="cursor:pointer; color:red; font-weight:bold; font-size:18px; margin-left:5px;">&times;</span>

                        </div>
                    @endif
                </div>
                <button type="button" class="btn btn-secondary btn-sm mt-2" id="add-peserta">Tambah Peserta</button>
            </div>

            <div class="form-group">
                <label>Tim</label>
                <div id="tim-wrapper">
                    @php
                        $oldTim = old('tim_id', []);
                    @endphp

                    @if(count($oldTim) > 0)
                        @foreach($oldTim as $index => $timId)
                            <div class="tim-group mb-2">
                                <select name="tim_id[]" class="form-control">
                                    <option value="">- Pilih Tim -</option>
                                    @foreach($tim as $item)
                                        <option value="{{ $item->id }}" {{ (isset($timId) && $timId == $item->id) ? 'selected' : '' }}>
                                            {{ $item->nama_tim }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-danger btn-sm mt-1 remove-tim">Hapus</button>
                            </div>
                        @endforeach
                    @else
                        <div class="tim-group mb-2" style="position: relative; max-width: 300px;">
                            <select name="tim_id[]" class="form-control" style="padding-right: 30px;">
                                <option value="" selected disabled>Pilih Tim</option>
                                @foreach($tim as $item)
                                    <option value="{{ $item->id }}" {{ (isset($timId) && $timId == $item->id) ? 'selected' : '' }}>
                                        {{ $item->nama_tim }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="remove-tim" title="Hapus tim"
                                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                                            cursor:pointer; color:red; font-weight:bold; font-size:18px; user-select:none;">&times;</span>
                        </div>
                    @endif
                </div>
                <button type="button" class="btn btn-secondary btn-sm mt-2" id="add-tim">Tambah Tim</button>
            </div>

            <div class="form-group">
                <label>Juri</label>
                <select name="juri_id" class="form-control">
                    <option value="">- Pilih Juri -</option>
                    @foreach($juri as $item)
                        <option value="{{ $item->id }}" {{ old('juri_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
        </form>

    </div>

    <script>
        function closeModal() {
            document.getElementById('errorModal').style.display = 'none';
        }
    </script>


    <script>
        function closeModal() {
            document.getElementById('errorModal').style.display = 'none';
        }

        // Peserta dynamic add/remove
        document.getElementById('add-peserta').addEventListener('click', function () {
            let wrapper = document.getElementById('peserta-wrapper');
            let newGroup = document.createElement('div');
            newGroup.classList.add('peserta-group', 'mb-2');
            newGroup.innerHTML = `
                <select name="peserta_id[]" class="form-control">
                    <option value="">- Pilih Peserta -</option>
                    @foreach($peserta as $item)
                        <option value="{{ $item->id }}">{{ $item->nama_peserta }}</option>
                    @endforeach
                </select>
                <span class="remove-peserta" title="Hapus peserta" style="cursor:pointer; color:red; font-weight:bold; font-size:18px; margin-left:5px;">&times;</span>
            `;
            wrapper.appendChild(newGroup);
            toggleRemoveButtons('peserta');
        });

        document.getElementById('peserta-wrapper').addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('remove-peserta')) {
                e.target.parentNode.remove();
                toggleRemoveButtons('peserta');
            }
        });


        // Tim dynamic add/remove
        document.getElementById('add-tim').addEventListener('click', function () {
            let wrapper = document.getElementById('tim-wrapper');
            let newGroup = document.createElement('div');
            newGroup.classList.add('tim-group', 'mb-2');
            newGroup.innerHTML = `
                <select name="tim_id[]" class="form-control">
                    <option value="">- Pilih Tim -</option>
                    @foreach($tim as $item)
                        <option value="{{ $item->id }}">{{ $item->nama_tim }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-danger btn-sm mt-1 remove-tim">Hapus</button>
            `;
            wrapper.appendChild(newGroup);
            toggleRemoveButtons('tim');
        });

        document.getElementById('tim-wrapper').addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('remove-tim')) {
                e.target.parentNode.remove();
                toggleRemoveButtons('tim');
            }
        });

        function toggleRemoveButtons(type) {
            let groups = document.querySelectorAll(`.${type}-group`);
            groups.forEach((group) => {
                let btn = group.querySelector(`.remove-${type}`);
                if (groups.length > 1) {
                    btn.style.display = 'inline'; // span muncul
                } else {
                    btn.style.display = 'none';   // sembunyikan kalau hanya 1
                }
            });
        }


        // Initialize remove buttons visibility on page load
        window.onload = function () {
            toggleRemoveButtons('peserta');
            toggleRemoveButtons('tim');
        }

        // Tanggal custom date toggle
        function toggleCustomDate(val) {
            const customDateInput = document.getElementById('customDate');
            if (val === 'lainnya' || val === '') {
                customDateInput.style.display = 'block';
            } else {
                customDateInput.style.display = 'none';
                customDateInput.value = val; // set value jika dari dropdown
            }
        }
    </script>

    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .modal-content button {
            margin: 10px 10px 0 10px;
            padding: 8px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #38c172;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-danger {
            background-color: #e3342f;
            color: white;
            border: none;
        }

        .remove-peserta,
        .remove-tim {
            cursor: pointer;
            color: red;
            font-weight: bold;
            font-size: 18px;
            margin-left: 5px;
            user-select: none;
        }

        .peserta-group,
        .tim-group {
            position: relative;
            max-width: 300px;
            /* sesuaikan lebar */
        }

        .peserta-group select,
        .tim-group select {
            padding-right: 30px;
            /* beri ruang di kanan agar tanda silang tidak tertutup */
        }

        .remove-peserta,
        .remove-tim {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: red;
            font-weight: bold;
            font-size: 18px;
            user-select: none;
        }
    </style>


@endsection