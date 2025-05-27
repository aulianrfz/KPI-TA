@extends('layouts.apk')

@section('content')
<div class="container">
    <h3 class="text-center font-weight-bold mb-4">Penjadwalan Sedang Diproses</h3>

    <!-- Step Indicator -->
    <div class="d-flex justify-content-center mb-4">
        <div class="step active">1</div>
        <div class="line"></div>
        <div class="step active">2</div>
        <div class="line"></div>
        <div class="step active">3</div>
    </div>

    <div class="card p-4 text-center">
        <h5 class="mb-3">Jadwal <strong>{{ $namaJadwal }}</strong> sedang diproses di background.</h5>
        <p>Silakan kembali ke halaman daftar jadwal untuk melihat hasilnya nanti.</p>

        <a href="{{ route('jadwal.index') }}" class="btn btn-primary mt-3">Kembali ke Jadwal</a>
    </div>
</div>

<style>
    .step {
        width: 30px;
        height: 30px;
        background-color: #007bff;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    .line {
        width: 60px;
        height: 4px;
        background-color: #007bff;
        margin: 0 10px;
        align-self: center;
    }
</style>
@endsection
