@extends('layouts.apk')

@section('title', 'Lihat QR')

@section('content')
<div class="container mt-5">
        <div class="card-body-4">

            <h4 class="fw-bold mb-4">Lihat QR</h4>

            <div class="d-flex justify-content-center mb-4">
                <img src="{{ asset('storage/qr_codes/pendaftar_' . $pendaftar->id . '.png') }}"
                    alt="QR Code"
                    class="img-fluid"
                    style="max-width: 700px;">
            </div>
            <form action="{{ route('kehadiran.edit', $pendaftar->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Peserta</label>
                        <input type="text" class="form-control" value="{{ $pendaftar->peserta->nama_peserta ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Institusi</label>
                        <input type="text" class="form-control" value="{{ $pendaftar->peserta->institusi ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="{{ $pendaftar->peserta->email ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status QR Code</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="aktif" value="1" id="aktif" {{ $pendaftar->aktif ? 'checked' : '' }}>
                            <label class="form-check-label" for="aktif">Aktif</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="aktif" value="0" id="tidak_aktif" {{ !$pendaftar->aktif ? 'checked' : '' }}>
                            <label class="form-check-label" for="tidak_aktif">Tidak Aktif</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('kehadiran.mata-lomba', ['mataLombaId' => $pendaftar->mata_lomba_id]) }}" class="btn btn-primary">Kembali</a>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
