@extends('layouts.apk')

@section('content')
    <div class="container">
        <h2 class="fw-bold mb-4 text-primary">Kelola Rekening Pembayaran - {{ $event->nama }}</h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('rek-pembayaran.store', $event->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Bank</label>
                <input type="text" name="nama_bank" class="form-control"
                    value="{{ old('nama_bank', $rekening->nama_bank ?? '') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nomor Rekening</label>
                <input type="text" name="no_rekening" class="form-control"
                    value="{{ old('no_rekening', $rekening->no_rekening ?? '') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Atas Nama</label>
                <input type="text" name="nama_rekening" class="form-control"
                    value="{{ old('nama_rekening', $rekening->nama_rekening ?? '') }}" required>
            </div>

            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="{{ url('/rek-pembayaran') }}" class="btn btn-secondary">Back</a>
        </form>

    </div>
@endsection