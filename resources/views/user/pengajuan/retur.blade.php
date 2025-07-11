@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-flex align-items-center mb-4">
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-circle me-3 shadow-sm" style="width: 42px; height: 42px;">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <h4 class="fw-bold mb-0 text-dark"  style="color: #0367A6">Syarat dan Keterangan Retur</h4>
            </div>

            <div class="card border-0 shadow rounded-4 p-4">
                <ol class="fs-6 text-secondary" style="line-height: 1.9;">
                    <li>
                        Pembatalan <strong>H-Pelaksanaan lomba sampai H-34</strong> sebelum pelaksanaan lomba, maka biaya yang sudah dibayarkan <span class="text-danger fw-semibold">tidak bisa dikembalikan</span>.
                    </li>
                    <li>
                        Pembatalan maksimal <strong>H-14 lomba sampai H-30 hari</strong> sebelum pelaksanaan lomba, maka biaya yang sudah dibayarkan akan dikembalikan <span class="text-primary fw-semibold">50%</span> dari biaya pendaftaran yang sudah dibayarkan.
                    </li>
                    <li>
                        Pembatalan di atas <strong>H-30 hari</strong> sebelum pelaksanaan lomba, maka biaya yang sudah dibayarkan akan dikembalikan <span class="text-success fw-semibold">75%</span> dari biaya pendaftaran yang sudah dibayarkan.
                    </li>
                </ol>
            </div>

        </div>
    </div>
</div>
@endsection
