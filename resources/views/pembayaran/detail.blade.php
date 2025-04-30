@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4 fw-bold">Detail Pembayaran</h4>

    <div class="card">
        <div class="card-body">
            <h5 class="fw-bold">{{ $peserta->subKategori->name }}</h5>
            <p>Tim: {{ $peserta->nama_tim }}</p>
            <p>Nama Ketua: {{ $peserta->nama }}</p>
            <p>Biaya Pendaftaran: <strong>Rp {{ number_format($peserta->subKategori->biaya_pendaftaran, 0, ',', '.') }}</strong></p>

            {{-- Tambahkan tombol atau form pembayaran di sini --}}
            <a href="#" class="btn btn-success mt-3">Bayar Sekarang</a>
        </div>
    </div>
</div>
@endsection
