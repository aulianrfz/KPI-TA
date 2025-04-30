@extends('layouts.app')
@include('layouts.navbar')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-3">CREATIVE DANCE</h4>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="alert alert-danger text-center fw-semibold">
        Please complete this payment before {{ $batas_pembayaran ?? 'DD.MM.YYYY' }}
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" id="invoiceTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="invoice-tab" data-bs-toggle="tab" data-bs-target="#invoice" type="button" role="tab">Invoice</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tahapan-tab" data-bs-toggle="tab" data-bs-target="#tahapan" type="button" role="tab">Teknis Pembayaran</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="bayar-tab" data-bs-toggle="tab" data-bs-target="#bayar" type="button" role="tab">Bayar</button>
        </li>
    </ul>

    <div class="tab-content" id="invoiceTabsContent">

        <!-- Invoice Tab -->
        <div class="tab-pane fade show active" id="invoice" role="tabpanel">
            <div class="p-4 rounded" style="background-color:rgb(179, 210, 241);">
                <div class="row text-white">
                    <!-- Left Side Info -->
                    <div class="col-md-8">
                        <h5 class="fw-bold" style="color: #000;">INVOICE</h5>
                        <h6 class="fw-bold" style="color: #000; margin-bottom: 20px;">Information Details</h6>
                        @if ($peserta->nama_tim)
                            <p class="invoice-item" style="color: #333; margin-bottom: 5px;">{{ $peserta->nama_tim }}</p>
                        @endif
                        <p class="invoice-item" style="color: #333; margin-bottom: 5px;">{{ $peserta->nama }}</p>
                        <p class="invoice-item" style="color: #333; margin-bottom: 5px;">{{ $institusi->nama_institusi }}</p>
                        <p class="invoice-item" style="color: #333; margin-bottom: 5px;">{{ $peserta->email }}</p>
                        <p class="invoice-item" style="color: #333; margin-bottom: 5px;">{{ $peserta->hp }}</p>
                    </div>


                    <!-- Right Bill Info -->
                    <div class="col-md-4">
                        <div class="bg-white text-dark p-3 rounded">
                            <h6 class="fw-bold">Bill details</h6>
                            <hr>
                            <p>Kategori: {{ $peserta->subKategori->jenis_lomba ?? '-' }}</p>
                            @if ($peserta->tim) <p>Tim: {{ $peserta->tim }}</p> @endif
                            <p>Jumlah Peserta: {{ $peserta->jumlah_peserta ?? 1 }}</p>

                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total</span>
                                <span>Rp{{ number_format($subkategori->biaya_pendaftaran ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="text-end mt-2">
                            <a href="#" class="btn btn-danger"><i class="bi bi-printer"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tahapan Pembayaran Tab -->
        <div class="tab-pane fade" id="tahapan" role="tabpanel">
            <div class="card card-body">
                <ol>
                    <li>Pembayaran dilakukan melalui:
                        <br>Transfer Bank ke rekening (Nama Bank, Nomor Rekening, Atas Nama).
                    </li>
                    <li>Mohon unggah bukti pembayaran setelah melakukan transfer.</li>
                    <li>
                        Bukti pembayaran akan diperiksa oleh panitia dalam waktu 1–2 hari kerja.
                        <br>Status pembayaran akan berubah menjadi Sudah Dibayar.
                    </li>
                    <li>
                        Jika pembayaran telah diverifikasi:
                        <br>- Status pembayaran akan berubah menjadi Sudah Dibayar.
                        <br>- Pendaftar dapat mengunduh kwitansi resmi dari sistem (jika meminta).
                        <br>- Pendaftar mendapatkan QR Code melalui email.
                    </li>
                    <li>
                        Jika ada kendala atau pembayaran tidak tervalidasi:
                        <br>Pendaftar akan menerima notifikasi dan harus mengunggah ulang bukti pembayaran jika diperlukan.
                    </li>
                    <li>
                        Untuk pertanyaan lebih lanjut, silakan hubungi admin.
                    </li>
                </ol>
            </div>
        </div>

        <!-- Bayar Tab -->
        <div class="tab-pane fade" id="bayar" role="tabpanel">
            <div class="card card-body">
                <p>Form atau upload bukti bayar akan ditaruh di sini.</p>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('pembayaran.bayar', $peserta->id) }}" class="btn text-white px-5 py-2" style="background-color: #2CC384;">
            Next
        </a>
    </div>
</div>

@include('layouts.footer')
@endsection
