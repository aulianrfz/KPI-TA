@extends('layouts.app')

@section('content')

    <style>
        .nav-tabs {
            border-bottom: 1px solid #dee2e6;
        }

        .nav-tabs .nav-item .nav-link {
            border: none;
            color: #6c757d;
            background-color: transparent;
        }

        .nav-tabs .nav-item .nav-link.active {
            color: #007bff;
            font-weight: bold;
            border-bottom: 3px solid #007bff;
        }

        .nav-tabs .nav-item .nav-link:hover {
            color: #0056b3;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #invoice-to-print,
            #invoice-to-print * {
                visibility: visible;
            }

            #invoice-to-print {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
            }

            .no-print,
            .no-print * {
                display: none !important;
            }
        }
    </style>

    <div class="container py-4">
        <div class="no-print">
            <h4 class="fw-bold mb-3">CREATIVE DANCE</h4>
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <div class="alert alert-warning text-center fw-semibold">
                Silakan selesaikan pembayaran sebelum <strong>{{ $batas_pembayaran ?? 'DD.MM.YYYY' }}</strong>
            </div>
            <ul class="nav nav-tabs justify-content-center mb-4" id="invoiceTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="invoice-tab" data-bs-toggle="tab" data-bs-target="#invoice"
                        type="button" role="tab">Invoice</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tahapan-tab" data-bs-toggle="tab" data-bs-target="#tahapan" type="button"
                        role="tab">Teknis Pembayaran</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="bayar-tab" data-bs-toggle="tab" data-bs-target="#bayar" type="button"
                        role="tab">Bayar</button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="invoiceTabsContent">
            <div class="tab-pane fade show active" id="invoice" role="tabpanel">
                <div id="invoice-to-print" class="card card-body p-4 p-md-5"
                    style="background-color: #EAF2FF; color: #333;">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h2 class="fw-bolder mb-1" style="color: #000;">INVOICE</h2>
                            <p class="mb-1" style="font-size: 1rem; font-weight: 500; color: #333;">
                                {{ ucfirst(strtolower($tipe)) }}
                            </p>
                            <p class="text-muted mb-0 mt-3">
                                @if ($tipe === 'peserta')
                                    #{{ $peserta->pendaftar->id ?? '-' }}
                                @else
                                    #{{ $pembimbing->pendaftaran->first()->id ?? '-' }}
                                @endif
                            </p>


                        </div>
                        <div class="text-end">
                            <h5 class="fw-bold" style="color: #000;">KOMPETISI PARIWISATA INDONESIA</h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-8">
                            <h5 class="fw-bold mb-3">Information Details</h5>
                            <div class="mb-4">
                                @if ($tipe === 'peserta')
                                    <p class="mb-1"><span class="fw-bold" style="width: 150px; display: inline-block;">Nama
                                            Institusi</span>: {{ $peserta->institusi }}</p>
                                    <p class="mb-1"><span class="fw-bold" style="width: 150px; display: inline-block;">Cabang
                                            Lomba</span>: {{ $peserta->pendaftar?->mataLomba?->nama_lomba ?? '-' }}</p>
                                    @if ($tipe === 'peserta' && $peserta?->tim?->isNotEmpty())
                                        <p class="mb-1"><span class="fw-bold" style="width: 150px; display: inline-block;">Nama
                                                Tim</span>: {{ $peserta->tim->first()->nama_tim }}</p>
                                    @endif
                                @else
                                    <p class="mb-1"><span class="fw-bold"
                                            style="width: 150px; display: inline-block;">Event</span>:
                                        {{ $pembimbing->pendaftaran->first()?->event?->nama_event ?? '-' }}
                                    </p>
                                @endif

                            </div>
                            <div>
                                <h5 class="fw-bold mb-3">
                                    {{ ($tipe === 'peserta' && $peserta?->tim?->isNotEmpty()) ? 'Tim Details' : 'Personal Details' }}
                                </h5>
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    @if ($tipe === 'peserta')
                                        <div>
                                            <p class="fw-bold mb-0">Ketua</p>
                                            <span>{{ $peserta->nama_peserta }}</span>
                                        </div>
                                        <div class="text-end">
                                            <p class="mb-0">{{ $peserta->email }}</p>
                                            <p class="mb-0">{{ $peserta->no_hp }}</p>
                                        </div>
                                    @else
                                        <div>
                                            <p class="fw-bold mb-0">Nama</p>
                                            <span>{{ $pembimbing->nama_lengkap }}</span>
                                        </div>
                                        <div class="text-end">
                                            <p class="mb-0">{{ $pembimbing->email }}</p>
                                            <p class="mb-0">{{ $pembimbing->no_hp }}</p>
                                        </div>
                                    @endif
                                </div>
                                @php $counter = 1; @endphp
                                @if ($pesertaSatuInvoice->isNotEmpty())
                                    @foreach ($pesertaSatuInvoice as $anggota)
                                        @if($anggota->id != $peserta->id)
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <p class="fw-bold mb-0">Anggota {{ $counter++ }}</p>
                                                    <span>{{ $anggota->nama_peserta }}</span>
                                                </div>
                                                <div class="text-end">
                                                    <p class="mb-0">{{ $anggota->email }}</p>
                                                    <p class="mb-0">{{ $anggota->no_hp }}</p>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-4 mt-4 mt-lg-0">
                            <div class="card shadow-sm border-0 text-dark">
                                <div class="card-header bg-white pb-0">
                                    <h5 class="fw-bolder">Bill details</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $biaya_pendaftaran = $tipe === 'peserta' ? ($peserta->pendaftar->mataLomba->biaya_pendaftaran ?? 0) : 0;
                                        $jumlahPeserta = $tipe === 'peserta' ? ($peserta->tim->first()?->peserta->count() ?? 1) : 1;
                                        $total = $biaya_pendaftaran;
                                    @endphp
                                    <div class="d-flex justify-content-between mb-2"><span
                                            class="text-muted">Kategori</span><span>{{ $peserta->pendaftar?->mataLomba?->nama_lomba ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Harga
                                            Satuan</span><span>Rp{{ number_format($biaya_pendaftaran, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between"><span class="text-muted">Jumlah
                                            Peserta</span><span>{{ $jumlahPeserta }}</span></div>
                                </div>
                                <div class="card-footer bg-white border-0 pt-0">
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between fw-bolder fs-5">
                                        <span>Total</span><span>Rp{{ number_format($total, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-4 no-print">
                        <button class="btn btn-danger" onclick="window.print()"><i class="bi bi-file-earmark-pdf-fill"></i>
                            Download PDF</button>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tahapan" role="tabpanel">
                <div class="card card-body">
                    <div class="row g-4 g-lg-5">
                        <div class="col-lg-5">
                            <h5 class="fw-bolder mb-3">1. Tujuan Transfer Pembayaran</h5>
                            <div class="card bg-light border">
                                <div class="card-body">
                                    <p class="mb-2">Silakan lakukan transfer ke rekening berikut:</p>

                                    {{-- <img
                                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2e/BRI_2020.svg/1280px-BRI_2020.svg.png"
                                        alt="Logo Bank" class="mb-3" style="max-height: 25px;"> --}}

                                    <div class="mb-2">
                                        <small class="text-muted d-block">Bank</small>
                                        <span class="fw-bold fs-6">{{ $rekening->nama_bank ?? '-' }}</span>
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted d-block">Atas Nama</small>
                                        <span class="fw-bold fs-6">{{ $rekening->nama_rekening ?? '-' }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Nomor Rekening</small>
                                        <div class="input-group">
                                            <span class="fw-bold fs-5"
                                                id="nomor-rekening">{{ $rekening->no_rekening ?? '-' }}</span>
                                            <button class="btn btn-sm btn-outline-secondary ms-3" type="button"
                                                onclick="copyToClipboard('{{ $rekening->no_rekening ?? '' }}', this)">
                                                <i class="bi bi-clipboard"></i> Salin
                                            </button>
                                        </div>
                                    </div>
                                    <hr>
                                    <p class="mb-0 text-muted" style="font-size: 0.9em;">Pastikan nama dan nomor rekening
                                        sudah benar sebelum melakukan transfer.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <h5 class="fw-bolder mb-3">2. Langkah-langkah Selanjutnya</h5>
                            <ol class="list-unstyled">
                                <li class="d-flex mb-4">
                                    <i class="bi bi-cloud-arrow-up-fill fs-3 text-primary me-3"></i>
                                    <div><strong class="d-block">Unggah Bukti Pembayaran</strong>Setelah transfer berhasil,
                                        buka tab "Bayar" untuk mengunggah bukti pembayaran Anda.</div>
                                </li>
                                <li class="d-flex mb-4">
                                    <i class="bi bi-patch-check-fill fs-3 text-success me-3"></i>
                                    <div><strong class="d-block">Tunggu Verifikasi</strong>Tim kami akan memverifikasi
                                        pembayaran Anda dalam waktu 1-2 hari kerja.</div>
                                </li>
                                <li class="d-flex mb-4">
                                    <i class="bi bi-envelope-check-fill fs-3 text-info me-3"></i>
                                    <div><strong class="d-block">Pembayaran Diterima</strong>Jika valid, status pembayaran
                                        akan berubah dan QR Code untuk acara akan dikirim melalui email.</div>
                                </li>
                                <li class="d-flex">
                                    <i class="bi bi-headset fs-3 text-muted me-3"></i>
                                    <div><strong class="d-block">Butuh Bantuan?</strong>Jika mengalami kendala, jangan ragu
                                        untuk menghubungi panitia melalui kontak yang tersedia.</div>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="bayar" role="tabpanel">
                <div class="card card-body">
                    <form
                        action="{{ route('pembayaran.upload', ['tipe' => strtolower($tipe), 'id' => $tipe === 'peserta' ? $peserta->id : $pembimbing->id]) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="bukti" class="form-label fw-semibold">Upload Bukti Pembayaran</label>
                            <p class="text-muted" style="font-size: 14px;">File bukti pembayaran maksimal 2MB (.jpg, .png,
                                .pdf)</p>
                            <div class="mb-3 text-center">
                                <label for="bukti_pembayaran" class="form-label d-block"><i class="bi bi-cloud-upload"
                                        style="font-size: 48px; color: #007bff;"></i></label>
                                <input type="file" name="bukti" id="bukti" class="form-control" required>
                            </div>
                            @error('bukti')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                            <div class="mb-3"><label for="bank" class="form-label">Bank Pengirim</label><input type="text"
                                    class="form-control" id="bank" name="bank" required></div>
                            <div class="mb-3"><label for="nama_pengirim" class="form-label">Nama Pengirim
                                    (Opsional)</label><input type="text" class="form-control" id="nama_pengirim"
                                    name="nama_pengirim"></div>
                            <div class="mb-4"><label for="catatan" class="form-label">Catatan (Opsional)</label><textarea
                                    class="form-control" id="catatan" name="catatan" rows="2"></textarea></div>
                            <label for="bukti" class="form-label fw-semibold">Ceklis jika diperlukan</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="kwitansi_individu"
                                    id="kwitansi_individu" value="1">
                                <label class="form-check-label" for="kwitansi_individu">
                                    Butuh Kwitansi per Individu
                                </label>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="kwitansi_cap_basah"
                                    id="kwitansi_cap_basah" value="1">
                                <label class="form-check-label" for="kwitansi_cap_basah">
                                    Butuh Kwitansi Dengan Cap dan Tanda Tangan Basah
                                </label>
                            </div>
                        </div>
                        <div class="text-center"><button type="submit" class="btn btn-success px-4">Submit</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text, buttonElement) {
            navigator.clipboard.writeText(text).then(function () {
                const originalIcon = buttonElement.innerHTML;
                buttonElement.innerHTML = '<i class="bi bi-check-lg"></i> Tersalin!';
                buttonElement.classList.remove('btn-outline-secondary');
                buttonElement.classList.add('btn-success');
                setTimeout(function () {
                    buttonElement.innerHTML = originalIcon;
                    buttonElement.classList.remove('btn-success');
                    buttonElement.classList.add('btn-outline-secondary');
                }, 2000);
            }).catch(function (err) {
                console.error('Gagal menyalin teks: ', err);
                alert('Gagal menyalin nomor rekening.');
            });
        }
    </script>
@endsection