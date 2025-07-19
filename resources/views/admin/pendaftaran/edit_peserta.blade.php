@extends('layouts.apk')

@section('content')
<div class="container mt-4">
    <h2 class="text-bold">Edit Peserta</h2>
    <hr style="width: 200px; border-top: 2px solid #000;">

    <form action="{{ route('pendaftaran.peserta.update', $peserta->id) }}" method="POST" enctype="multipart/form-data" id="editPesertaForm">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Peserta</label>
                        <input type="text" class="form-control" name="nama_peserta" value="{{ $peserta->nama_peserta }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NIM</label>
                        <input type="text" class="form-control" name="nim" value="{{ $peserta->nim }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="{{ $peserta->email }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">No HP</label>
                        <input type="text" class="form-control" name="no_hp" value="{{ $peserta->no_hp }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Provinsi Institusi</label>
                        <select name="provinsi" class="form-select" required>
                            <option value="">- Pilih Provinsi -</option>
                            @foreach ($provinsi as $prov)
                                <option value="{{ $prov->nama_provinsi }}" {{ $peserta->provinsi == $prov->nama_provinsi ? 'selected' : '' }}>
                                    {{ $prov->nama_provinsi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Institusi</label>
                        <select name="institusi" class="form-select" required>
                            <option value="">- Pilih Institusi -</option>
                            @foreach ($institusi as $inst)
                                <option value="{{ $inst->nama_institusi }}" {{ $peserta->institusi == $inst->nama_institusi ? 'selected' : '' }}>
                                    {{ $inst->nama_institusi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Program Studi</label>
                    <select name="prodi" class="form-select" required>
                        <option value="">- Pilih Prodi -</option>
                        @foreach ($prodi as $jur)
                            <option value="{{ $jur->nama_jurusan }}" {{ $peserta->prodi == $jur->nama_jurusan ? 'selected' : '' }}>
                                {{ $jur->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Upload KTM Baru (Opsional)</label>
                    <input type="file" class="form-control" name="url_ktm" accept="image/*,application/pdf">
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanda Tangan Baru (Opsional)</label>
                    <div class="border p-2">
                        <canvas id="signature-pad" width="600" height="150" style="border:1px solid #ccc; width:100%; height:150px;"></canvas>
                    </div>
                    <input type="hidden" name="signature" id="signature">
                    <button type="button" id="clear-signature" class="btn btn-danger btn-sm mt-2">Hapus Tanda Tangan</button>
                </div>

            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary px-4">Update</button>
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

        const form = document.getElementById('editPesertaForm');
        form.addEventListener('submit', function (e) {
            if (!signaturePad.isEmpty()) {
                signatureInput.value = signaturePad.toDataURL();
            }
        });
    });
</script>
@endsection
