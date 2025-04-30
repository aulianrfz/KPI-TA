@extends('layouts.app')

@section('content')

@include('layouts.navbar')

<div class="container mt-4">
    <h4 class="mb-4">Form Pendaftaran Individu</h4>

    <form action="{{ route('pendaftaran.store') }}" method="POST" enctype="multipart/form-data" id="pendaftaranForm">
        @csrf

        <div class="card mb-4">
            <div class="card-body">

                <div class="row mb-3">
                <input type="hidden" name="id_subkategori" value="{{ $subKategori->id }}">
                    <div class="col-md-6">
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="nama" name="peserta[0][nama]" required>
                    </div>
                    <div class="col-md-6">
                        <label for="nim" class="form-label">NIM</label>
                        <input type="text" class="form-control" id="nim" name="peserta[0][nim]" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="peserta[0][email]" required>
                    </div>
                    <div class="col-md-6">
                        <label for="hp" class="form-label">No HP</label>
                        <input type="text" class="form-control" id="hp" name="peserta[0][hp]" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="provinsi" class="form-label">Provinsi</label>
                        <select name="peserta[0][provinsi_id]" class="form-select" required>
                            <option value="">- Pilih Provinsi -</option>
                            @foreach ($provinsi as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->nama_provinsi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="institusi" class="form-label">Institusi</label>
                        <select name="peserta[0][institusi_id]" class="form-select" required>
                            <option value="">- Pilih Institusi -</option>
                            @foreach ($institusi as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->nama_institusi }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="hp" class="form-label">Jurusan</label>
                    <input type="text" class="form-control" id="jurusan_id" name="peserta[0][jurusan]" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Upload KTP</label>
                        <input type="file" name="peserta[0][ktp]" class="form-control" accept="image/*" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tanda Tangan</label>
                        <div class="border p-2">
                            <canvas id="signature-pad" width="600" height="150" style="border:1px solid #ccc; width:100%; height:150px;"></canvas>
                        </div>
                        <input type="hidden" name="peserta[0][signature]" id="signature" required>
                        <button type="button" id="clear-signature" class="btn btn-danger btn-sm mt-2">Hapus Tanda Tangan</button>
                    </div>
                </div>

            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-success px-4">Submit Pendaftaran</button>
        </div>

    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('signature-pad');
        const signatureInput = document.getElementById('signature');
        const clearButton = document.getElementById('clear-signature');
        const signaturePad = new SignaturePad(canvas);

        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            signaturePad.clear();
        }

        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        clearButton.addEventListener('click', function () {
            signaturePad.clear();
        });

        // Set TTD ke input hidden sebelum submit
        const form = document.getElementById('pendaftaranForm');
        form.addEventListener('submit', function (e) {
            if (!signaturePad.isEmpty()) {
                signatureInput.value = signaturePad.toDataURL();
            } else {
                signatureInput.value = ''; // Add custom validation message if required
                alert('Tanda tangan tidak boleh kosong!');
                e.preventDefault();
            }

            // Validate other required fields before submitting the form
            const provinsi = form.querySelector('select[name="peserta[0][provinsi_id]"]');
            const institusi = form.querySelector('select[name="peserta[0][institusi_id]"]');
            const jurusan = form.querySelector('select[name="peserta[0][jurusan_id]"]');

            if (!provinsi.value || !institusi.value || !jurusan.value) {
                alert('Semua kolom wajib diisi!');
                e.preventDefault();
            }
        });
    });
</script>

@include('layouts.footer')

@endsection
