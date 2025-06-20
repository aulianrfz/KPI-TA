@extends('layouts.app')

@section('content')

<div class="container mt-5">
    <h2 class="fw-bold">Registration</h2>
    <hr style="width: 230px; border-top: 2px solid #000;">
    <h4 class="text-center">{{ $mataLomba->nama_lomba }}</h4>

    <form action="{{ route('pendaftaran.store') }}" method="POST" enctype="multipart/form-data" id="pendaftaran-form">
        @csrf
        <input type="hidden" name="id_mataLomba" value="{{ $mataLomba->id }}">
        <input type="hidden" name="maksPeserta" value="{{ $mataLomba->maks_peserta }}">

        @if ($mataLomba->maks_peserta == 1)
            @include('user.pendaftaran.formindividu', ['index' => 0])
        @else
            <div class="mb-4">
                <label class="form-label fw-bold">Nama Tim</label>
                <input type="text" name="nama_tim" class="form-control" placeholder="Masukkan Nama Tim" value="{{ old('nama_tim') }}" required>
            </div>

            <div id="peserta-container">
                @for ($i = 0; $i < $mataLomba->min_peserta; $i++)
                    @include('user.pendaftaran.formkelompok', ['index' => $i, 'provinsi' => $provinsi, 'institusi' => $institusi, 'prodi' => $prodi])
                @endfor
            </div>

            @if ($mataLomba->min_peserta < $mataLomba->maks_peserta)
                <div class="text-end mb-4">
                    <button type="button" class="btn btn-outline-primary" id="tambah-peserta-btn">
                        <i class="bi bi-plus-circle"></i> Tambah Peserta
                    </button>
                </div>
            @endif

            <div class="text-center mb-5">
                <button type="submit" class="btn btn-success btn-lg px-5">Submit</button>
            </div>
        @endif
    </form>
</div>


@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dataProvinsi = @json($provinsi);
    const dataInstitusi = @json($institusi);
    const dataProdi = @json($prodi);

    let indexPeserta = {{ $mataLomba->min_peserta }};
    const maksPeserta = {{ $mataLomba->maks_peserta }};
    let signaturePads = {};

    document.querySelectorAll("canvas[id^='signature-pad-']").forEach(function (canvas) {
        const index = canvas.id.split('-').pop();
        const pad = new SignaturePad(canvas, { backgroundColor: 'rgba(255,255,255,0)' });
        signaturePads[index] = pad;
        pad.onEnd = function () {
            document.getElementById('signature_' + index).value = pad.toDataURL();
        };
    });

    const tambahPesertaBtn = document.getElementById('tambah-peserta-btn');
    if (tambahPesertaBtn) {
        tambahPesertaBtn.addEventListener('click', function () {
           if (indexPeserta >= maksPeserta) {
            Swal.fire({
                icon: 'warning',
                title: 'Maksimum Peserta Tercapai',
                text: 'Anda tidak dapat menambah peserta lebih dari batas maksimum.',
                confirmButtonText: 'OK'
            });
            return;
        }

            const container = document.getElementById('peserta-container');
            const newPeserta = generatePesertaForm(indexPeserta, dataProvinsi, dataInstitusi, dataProdi);
            container.insertAdjacentHTML('beforeend', newPeserta);

            const canvas = document.getElementById(`signature-pad-${indexPeserta}`);
            const pad = new SignaturePad(canvas, { backgroundColor: 'rgba(255,255,255,0)' });
            signaturePads[indexPeserta] = pad;
            pad.onEnd = function () {
                document.getElementById(`signature_${indexPeserta}`).value = pad.toDataURL();
            };

            indexPeserta++;
        });
    }

    window.clearSignature = function (index) {
        if (signaturePads[index]) {
            signaturePads[index].clear();
            document.getElementById(`signature_${index}`).value = '';
        }
    };

    function generatePesertaForm(index, provinsi, institusi, prodi) {
        const provinsiOptions = provinsi.map(p => `<option value="${p.nama_provinsi}">${p.nama_provinsi}</option>`).join('');
        const institusiOptions = institusi.map(i => `<option value="${i.nama_institusi}">${i.nama_institusi}</option>`).join('');
        const prodiOptions = prodi.map(p => `<option value="${p.nama_jurusan}">${p.nama_jurusan}</option>`).join('');

        return `
        <div class="peserta-form mt-3" data-index="${index}">
            <h5 class="fw-bold">Peserta ${index + 1}</h5>

            <div class="mb-3">
                <label>Posisi</label>
                <select name="peserta[${index}][posisi]" class="form-control" required>
                    <option value="">-- Pilih Posisi --</option>
                    <option value="Ketua">Ketua</option>
                    <option value="Anggota">Anggota</option>
                </select>
            </div>

            <div class="card mb-4">
                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Nama</label>
                            <input type="text" class="form-control" name="peserta[${index}][nama_peserta]" value="{{ old('peserta[${index}][nama_peserta]') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label>NIM</label>
                            <input type="text" class="form-control" name="peserta[${index}][nim]" value="{{ old('peserta[${index}][nim]') }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Email</label>
                            <input type="email" class="form-control" name="peserta[${index}][email]" value="{{ old('peserta[${index}][email]') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label>No HP</label>
                            <input type="text" class="form-control" name="peserta[${index}][no_hp]" value="{{ old('peserta[${index}][no_hp]') }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Provinsi Institusi</label>
                            <select name="peserta[${index}][provinsi]" class="form-select" required>
                                <option value="">- Pilih Provinsi -</option>
                                ${provinsiOptions}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Institusi</label>
                            <select name="peserta[${index}][institusi]" class="form-select" required>
                                <option value="">- Pilih Institusi -</option>
                                ${institusiOptions}
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Prodi</label>
                            <select name="peserta[${index}][prodi]" class="form-select" required>
                                <option value="">- Pilih Prodi -</option>
                                ${prodiOptions}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Upload KTP</label>
                            <input type="file" name="peserta[${index}][ktp]" class="form-control" accept="image/*" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Tanda Tangan</label>
                        <div class="border p-2">
                            <canvas id="signature-pad-${index}" width="600" height="150" style="border:1px solid #ccc; width:100%; height:150px;"></canvas>
                        </div>
                        <input type="hidden" name="peserta[${index}][signature]" id="signature_${index}" required>
                        <button type="button" class="btn btn-danger btn-sm mt-2" onclick="clearSignature(${index})">Hapus Tanda Tangan</button>
                    </div>

                </div>
            </div>
        </div>`;
    }
});
</script>
@endpush
