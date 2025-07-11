@extends('layouts.apk')

@section('title', 'Detail Pengajuan')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm rounded-4 p-4">
        <h5 class="fw-bold mb-4" style="color: #0367A6">Detail Pengajuan</h5>

        <div class="row mb-3">
            <div class="col-md-3 fw-semibold">Username</div>
            <div class="col-md-9">{{ $pengajuan->user->first_name }} {{ $pengajuan->user->last_name }}</div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3 fw-semibold">Jenis Pengajuan</div>
            <div class="col-md-9">{{ $pengajuan->jenis }}</div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3 fw-semibold">Deskripsi Pengajuan</div>
            <div class="col-md-9">{{ $pengajuan->deskripsi ?? '-' }}</div>
        </div>

        <div class="card-footer bg-white text-end">
            <a href="{{ route('admin.pengajuan.index') }}" class="btn btn-success">
                <i class="bi bi-arrow-left-circle me-1"></i> Tutup
            </a>
        </div>
    </div>
</div>
@endsection
