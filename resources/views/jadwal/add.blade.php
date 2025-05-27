@extends('layouts.apk')

@section('content')
    @if(session('error_force'))
        <div id="errorModal" class="modal-overlay">
            <div class="modal-content">
                <p>{{ session('error_force') }}</p>
                <form method="POST" action="{{ route('jadwal.add') }}">
                    @csrf
                    @foreach(old() as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
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
                <select name="mata_lomba_id" class="form-control" required>
                    @foreach($mata_lomba as $item)
                        <option value="{{ $item->id }}">{{ $item->nama_lomba }}</option>
                    @endforeach
                </select>
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
                <label>Venue</label>
                <select name="venue_id" class="form-control" required>
                    @foreach($venue as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Peserta</label>
                <select name="peserta_id" class="form-control" required>
                    @foreach($peserta as $item)
                        <option value="{{ $item->id }}">{{ $item->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Juri</label>
                <select name="juri_id" class="form-control" required>
                    @foreach($juri as $item)
                        <option value="{{ $item->id }}">{{ $item->nama }}</option>
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
            background-color: #e3342f;
            color: white;
        }
    </style>


@endsection