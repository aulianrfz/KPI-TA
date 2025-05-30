@extends('layouts.app')

@section('content')

<div class="container mt-4">
    <h2 class="text-bold">Registration</h2>
    <hr style="width: 230px; border-top: 2px solid #000;">

    <form action="{{ route('pendaftaran.store') }}" method="POST" enctype="multipart/form-data" id="pendaftaranForm">
        @csrf

        <div class="card mb-4">
            <div class="card-body">

                <div class="row mb-3">
                <input type="hidden" name="id_mataLomba" value="{{ $mataLomba->id }}">
                    <div class="col-md-6">
                        <label for="nama_peserta" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="nama_peserta" name="peserta[0][nama_peserta]" required>
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
                        <input type="text" class="form-control" id="no_hp" name="peserta[0][no_hp]" required>
                        </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="provinsi" class="form-label">Provinsi</label>
                        <select name="peserta[0][provinsi]" class="form-select" required>
                            <option value="">- Pilih Provinsi -</option>
                            @foreach ($provinsi as $prov)
                                <option value="{{ $prov->nama_provinsi }}">{{ $prov->nama_provinsi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="institusi" class="form-label">Institusi</label>
                        <select name="peserta[0][institusi]" class="form-select" required>
                            <option value="">- Pilih Institusi -</option>
                            @foreach ($institusi as $inst)
                                <option value="{{ $inst->nama_institusi }}">{{ $inst->nama_institusi }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="prodi" class="form-label">Prodi</label>
                        <select name="peserta[0][prodi]" class="form-select" required>
                            <option value="">- Pilih Prodi -</option>
                            @foreach ($prodi as $prods)
                                <option value="{{ $prods->nama_jurusan }}">{{ $prods->nama_jurusan }}</option>
                            @endforeach
                        </select>
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

        const form = document.getElementById('pendaftaranForm');
        form.addEventListener('submit', function (e) {
            if (!signaturePad.isEmpty()) {
                signatureInput.value = signaturePad.toDataURL();
            } else {
                signatureInput.value = '';
                alert('Tanda tangan tidak boleh kosong!');
                e.preventDefault();
            }

            const provinsi = form.querySelector('select[name="peserta[0][provinsi_id]"]');
            const institusi = form.querySelector('select[name="peserta[0][institusi_id]"]');
            const prodi = form.querySelector('select[name="peserta[0][prodi]"]');

            if (!provinsi.value || !institusi.value || !prodi.value) {
                alert('Semua kolom wajib diisi!');
                e.preventDefault();
            }
        });
    });
</script>

@include('layouts.footer')

@endsection
