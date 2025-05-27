@extends('layouts.app')

@section('content')

<div class="container py-5 text-center">
    <div class="my-5">
        <img src="https://img.icons8.com/color/96/000000/checked--v1.png" alt="Success Icon" class="mb-4" />
        <h3 class="text-success fw-bold">Pendaftaran Berhasil !!</h3>
        <p class="text-muted">Mohon tunggu untuk proses pengiriman QR Code melalui email.</p>
        <a href="{{ route('event.list', 1) }}" class="btn btn-primary mt-4 px-5">Kembali</a>
    </div>
</div>

@include('layouts.footer')

@endsection
