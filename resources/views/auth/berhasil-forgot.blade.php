@extends('layouts.app')

@section('content')

<div class="container py-5 text-center">
    <div class="my-5">
        <img src="https://img.icons8.com/color/96/000000/checked--v1.png" alt="Success Icon" class="mb-4" />
        <h3 class="text-success fw-bold">Berhasil!</h3>
        <p class="text-muted">ink reset password telah dikirim ke email Anda.</p>
        <a href="{{ route('login') }}" class="btn btn-primary mt-4 px-5">Ok</a>
    </div>
</div>


@endsection
