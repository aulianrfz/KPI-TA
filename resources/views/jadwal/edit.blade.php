@extends('layouts.apk')

@section('content')
    {{-- Error Modal --}}
    @if(session('error_force'))
        <div id="errorModal" class="modal-overlay">
            <div class="modal-content">
                <p>{{ session('error_force') }}</p>
                <form method="POST" action="{{ route('jadwal.update', $jadwal->id) }}">
                    @csrf
                    @method('PUT')

                    {{-- Hidden input untuk data yang sudah dikirim --}}
                    <input type="hidden" name="mata_lomba_id" value="{{ old('mata_lomba_id') }}">
                    <input type="hidden" name="waktu_mulai" value="{{ old('waktu_mulai') }}">
                    <input type="hidden" name="waktu_selesai" value="{{ old('waktu_selesai') }}">
                    <input type="hidden" name="venue_id" value="{{ old('venue_id') }}">
                    <input type="hidden" name="peserta_id" value="{{ old('peserta_id') }}">
                    <input type="hidden" name="juri_id" value="{{ old('juri_id') }}">
                    <input type="hidden" name="force" value="1">

                    <button type="submit" class="btn btn-primary">Lanjutkan</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Batal</button>
                </form>
            </div>
        </div>
    @endif


    <div class="container">
        <h2>Edit Jadwal</h2>

        <form method="POST" action="{{ route('jadwal.update', $jadwal->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Sub Kategori</label>
                <select name="mata_lomba_id" class="form-control">
                    @foreach($mata_lomba as $sub)
                        <option value="{{ $sub->id }}" {{ $jadwal->mata_lomba_id == $sub->id ? 'selected' : '' }}>
                            {{ $sub->nama_lomba }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="waktu_mulai">Waktu Mulai</label>
                <input type="time" name="waktu_mulai" class="form-control"
                    value="{{ old('waktu_mulai', $jadwal->waktu_mulai) }}" required>
            </div>

            <div class="form-group">
                <label for="waktu_selesai">Waktu Selesai</label>
                <input type="time" name="waktu_selesai" class="form-control"
                    value="{{ old('waktu_selesai', $jadwal->waktu_selesai) }}" required>
            </div>


            <div class="mb-3">
                <label>Venue</label>
                <select name="venue_id" class="form-control">
                    @foreach($venue as $venue)
                        <option value="{{ $venue->id }}" {{ $jadwal->venue_id == $venue->id ? 'selected' : '' }}>
                            {{ $venue->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Peserta</label>
                <select name="peserta_id" class="form-control">
                    @foreach($peserta as $peserta)
                        <option value="{{ $peserta->id }}" {{ $jadwal->peserta_id == $peserta->id ? 'selected' : '' }}>
                            {{ $peserta->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Juri</label>
                <select name="juri_id" class="form-control">
                    @foreach($juri as $juri)
                        <option value="{{ $juri->id }}" {{ $jadwal->juri_id == $juri->id ? 'selected' : '' }}>
                            {{ $juri->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-success">Update Jadwal</button>
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>

    <script>
        function closeModal() {
            document.getElementById('errorModal').style.display = 'none';
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
            /* background gelap transparan */
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
            background-color: #e3342f;
            color: white;
        }
    </style>


@endsection