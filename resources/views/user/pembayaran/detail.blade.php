@extends('layouts.app')

@section('content')

<div class="container py-4">
    <h4 class="fw-bold mb-3">CREATIVE DANCE</h4>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="alert alert-warning text-center fw-semibold">
        Silakan selesaikan pembayaran sebelum <strong>{{ $batas_pembayaran ?? 'DD.MM.YYYY' }}</strong>
    </div>

    <ul class="nav nav-tabs mb-4" id="invoiceTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="invoice-tab" data-bs-toggle="tab" data-bs-target="#invoice" type="button" role="tab">
                Invoice
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tahapan-tab" data-bs-toggle="tab" data-bs-target="#tahapan" type="button" role="tab">
                Teknis Pembayaran
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="bayar-tab" data-bs-toggle="tab" data-bs-target="#bayar" type="button" role="tab">
                Bayar
            </button>
        </li>
    </ul>

    <div class="tab-content" id="invoiceTabsContent">
        <!-- INVOICE -->
        <div class="tab-pane fade show active" id="invoice" role="tabpanel">
            <div class="p-4 rounded bg-light border">
                <div class="row">
                    <!-- Info peserta -->
                    <div class="col-md-8">
                        <h5 class="fw-bold mb-3">INVOICE</h5>
                        @if ($pesertaSatuInvoice->isNotEmpty())
                            <h6>Anggota Tim:</h6>
                            <ul>
                                @foreach ($pesertaSatuInvoice as $anggota)
                                    <li>{{ $anggota->nama_peserta }}</li>
                                    <p class="mb-1"><strong>Institusi:</strong> {{ $peserta->institusi }}</p>
                                    <p class="mb-1"><strong>Email:</strong> {{ $peserta->email }}</p>
                                    <p class="mb-1"><strong>No HP:</strong> {{ $peserta->no_hp }}</p>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <!-- Detail biaya -->
                    <div class="col-md-4">
                        <div class="bg-white p-3 rounded shadow-sm">
                            <h6 class="fw-bold">Detail Pembayaran</h6>
                            <hr>
                            <p><strong>Kategori:</strong> {{ $peserta->pendaftar?->mataLomba?->nama_lomba ?? '-' }}</p>

                            @if ($peserta->tim->isNotEmpty())
                                <p><strong>Tim:</strong> {{ $peserta->tim->first()->nama_tim }}</p>
                            @endif

                            <p><strong>Jumlah Peserta:</strong> {{ $peserta->tim->first()?->peserta->count() ?? 1 }}</p>

                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total</span>
                                <span>Rp{{ number_format($mataLomba->biaya_pendaftaran ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="text-end mt-2">
                            <button class="btn btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer"></i> Cetak
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tahapan" role="tabpanel">
            <div class="card card-body">
                <ol class="mb-0">
                    <li>Pembayaran dilakukan melalui transfer ke rekening berikut:<br>
                        <strong>Nama Bank:</strong> [Isi Nama Bank]<br>
                        <strong>Nomor Rekening:</strong> [Isi Nomor Rekening]<br>
                        <strong>Atas Nama:</strong> [Nama Pemilik Rekening]
                    </li>
                    <li>Unggah bukti pembayaran melalui tab "Bayar".</li>
                    <li>Bukti akan diverifikasi dalam 1–2 hari kerja.</li>
                    <li>Setelah validasi, status berubah menjadi <strong>Sudah Dibayar</strong> dan QR Code dikirim via email.</li>
                    <li>Jika gagal validasi, Anda akan diminta mengunggah ulang bukti pembayaran.</li>
                    <li>Hubungi admin jika mengalami kendala lebih lanjut.</li>
                </ol>
            </div>
        </div>

        <div class="tab-pane fade" id="bayar" role="tabpanel">
            <div class="card card-body">
                <form action="{{ route('pembayaran.upload', $peserta->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="bukti" class="form-label fw-semibold">Upload Bukti Pembayaran</label>
                        <p class="text-muted" style="font-size: 14px;">File bukti pembayaran maksimal 2MB (.jpg, .png, .pdf)</p>
                        <div class="mb-3 text-center">
                            <label for="bukti_pembayaran" class="form-label d-block">
                                <i class="bi bi-cloud-upload" style="font-size: 48px; color: #007bff;"></i>
                            </label>
                            <input type="file" name="bukti" id="bukti" class="form-control" required>
                        </div>
                        @error('bukti')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        <div class="mb-3">
                            <label for="bank" class="form-label">Bank</label>
                            <input type="text" class="form-control" id="bank" name="bank" required>
                        </div>

                        <div class="mb-3">
                            <label for="nama_pengirim" class="form-label">Nama Pengirim (Opsional)</label>
                            <input type="text" class="form-control" id="nama_pengirim" name="nama_pengirim">
                        </div>

                        <div class="mb-4">
                            <label for="catatan" class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-success px-4">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- <div class="text-center mt-4">
        <a href="{{ route('pembayaran.bayar', $peserta->id) }}" class="btn text-white px-5 py-2" style="background-color: #2CC384;">
            Selanjutnya
        </a>
    </div> -->
</div>

@include('layouts.footer')
@endsection
