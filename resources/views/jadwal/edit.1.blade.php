@extends('layouts.apk')

@section('content')
    @if(session('error_force'))
        <div id="errorModal" class="modal-overlay">
            <div class="modal-content">
                <p>{{ session('error_force') }}</p>
                <form method="POST" action="{{ route('jadwal.update', $agenda->id) }}">
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
        <h2>Edit Jadwal - {{ $jadwal->nama_jadwal }} | Tanggal
            {{ \Carbon\Carbon::parse($agenda->tanggal)->translatedFormat('d F Y') }}
        </h2>

        <form action="{{ route('jadwal.update', $agenda->id) }}" method="POST">
            @csrf
            @method('PUT')

            <input type="hidden" name="agenda_id" value="{{ $agenda->id }}">

            <div class="form-group">
                <label>Sub Kategori</label>
                <select name="mata_lomba_id" class="form-control">
                    <option value="">- Pilih Sub Kategori -</option>
                    @foreach($mata_lomba as $item)
                        <option value="{{ $item->id }}" {{ $agenda->mata_lomba_id == $item->id ? 'selected' : '' }}>
                            {{ $item->nama_lomba }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Tanggal</label>
                <select name="tanggal_dropdown" class="form-control" onchange="toggleCustomDate(this.value)">
                    <option value="">-- Pilih Tanggal --</option>
                    @foreach($tanggal_unik as $tanggal)
                        <option value="{{ $tanggal }}" {{ $agenda->tanggal == $tanggal ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                        </option>
                    @endforeach
                    <option value="lainnya" {{ !in_array($agenda->tanggal, $tanggal_unik->toArray()) ? 'selected' : '' }}>
                        Tanggal Lainnya</option>
                </select>

                <input type="date" name="tanggal" id="customDate" class="form-control mt-2"
                    style="{{ !in_array($agenda->tanggal, $tanggal_unik->toArray()) ? 'display:block;' : 'display:none;' }}"
                    value="{{ !in_array($agenda->tanggal, $tanggal_unik->toArray()) ? $agenda->tanggal : '' }}">
            </div>

            <div class="form-group">
                <label>Waktu Mulai</label>
                <input type="time" name="waktu_mulai" class="form-control" required value="{{ $agenda->waktu_mulai }}">
            </div>

            <div class="form-group">
                <label>Waktu Selesai</label>
                <input type="time" name="waktu_selesai" class="form-control" required value="{{ $agenda->waktu_selesai }}">
            </div>

            <div class="form-group">
                <label>Kegiatan</label>
                <textarea name="kegiatan" class="form-control" rows="3">{{ $agenda->kegiatan }}</textarea>
            </div>

            <div class="form-group">
                <label>Venue</label>
                <select name="venue_id" class="form-control">
                    <option value="">- Pilih Venue -</option>
                    @foreach($venue as $item)
                        <option value="{{ $item->id }}" {{ $agenda->venue_id == $item->id ? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Peserta</label>
                <div id="peserta-wrapper">
                    @if(count(old('peserta_id', $peserta_terpilih)) > 0)
                        @foreach(old('peserta_id', $peserta_terpilih) as $pesertaId)
                            <div class="peserta-group mb-2" style="position: relative; max-width: 300px;">
                                <select name="peserta_id[]" class="form-control">
                                    <option value="">- Pilih Peserta -</option>
                                    @foreach($peserta as $item)
                                        <option value="{{ $item->id }}" {{ $pesertaId == $item->id ? 'selected' : '' }}>
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
                    @if(count(old('tim_id', $tim_terpilih)) > 0)
                        @foreach(old('tim_id', $tim_terpilih) as $timId)
                            <div class="tim-group mb-2" style="position: relative; max-width: 300px;">
                                <select name="tim_id[]" class="form-control">
                                    <option value="">- Pilih Tim -</option>
                                    @foreach($tim as $item)
                                        <option value="{{ $item->id }}" {{ $timId == $item->id ? 'selected' : '' }}>
                                            {{ $item->nama_tim }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="remove-tim" title="Hapus tim"
                                    style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                                                                                        cursor:pointer; color:red; font-weight:bold; font-size:18px; user-select:none;">&times;</span>
                            </div>
                        @endforeach
                    @else
                        <div class="tim-group mb-2" style="position: relative; max-width: 300px;">
                            <select name="tim_id[]" class="form-control" style="padding-right: 30px;">
                                <option value="" selected disabled>Pilih Tim</option>
                                @foreach($tim as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama_tim }}</option>
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
                        <option value="{{ $item->id }}" {{ $agenda->juri_id == $item->id ? 'selected' : '' }}>
                            {{ $item->nama_juri }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Jadwal</button>
        </form>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', (event) => {
            const modal = document.getElementById('errorModal');
            if (modal) {
                modal.style.display = 'block'; // pastikan modal terlihat
            }
        });

        function closeModal() {
            document.getElementById('errorModal').style.display = 'none';
        }

        function toggleCustomDate(value) {
            const customDate = document.getElementById('customDate');
            if (value === 'lainnya') {
                customDate.style.display = 'block';
                customDate.required = true;
            } else {
                customDate.style.display = 'none';
                customDate.required = false;
                customDate.value = '';
            }
        }

        document.getElementById('add-peserta').addEventListener('click', function () {
            let wrapper = document.getElementById('peserta-wrapper');
            let div = document.createElement('div');
            div.classList.add('peserta-group', 'mb-2');
            div.style.position = 'relative';
            div.style.maxWidth = '300px';

            div.innerHTML = `
                            <select name="peserta_id[]" class="form-control">
                                <option value="" selected disabled>Pilih Peserta</option>
                                @foreach($peserta as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama_peserta }}</option>
                                @endforeach
                            </select>
                            <span class="remove-peserta" title="Hapus peserta"
                                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                                       cursor:pointer; color:red; font-weight:bold; font-size:18px; user-select:none;">&times;</span>
                        `;

            wrapper.appendChild(div);
        });

        document.getElementById('peserta-wrapper').addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-peserta')) {
                e.target.parentElement.remove();
            }
        });

        document.getElementById('add-tim').addEventListener('click', function () {
            let wrapper = document.getElementById('tim-wrapper');
            let div = document.createElement('div');
            div.classList.add('tim-group', 'mb-2');
            div.style.position = 'relative';
            div.style.maxWidth = '300px';

            div.innerHTML = `
                            <select name="tim_id[]" class="form-control">
                                <option value="" selected disabled>Pilih Tim</option>
                                @foreach($tim as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama_tim }}</option>
                                @endforeach
                            </select>
                            <span class="remove-tim" title="Hapus tim"
                                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                                       cursor:pointer; color:red; font-weight:bold; font-size:18px; user-select:none;">&times;</span>
                        `;

            wrapper.appendChild(div);
        });

        document.getElementById('tim-wrapper').addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-tim')) {
                e.target.parentElement.remove();
            }
        });
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