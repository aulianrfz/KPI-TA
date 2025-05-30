<div class="card mb-4">
    <div class="card-body">
        <input type="hidden" name="id_mataLomba" value="{{ $mataLomba->id }}">

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="nama_peserta_{{ $index }}" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama_peserta_{{ $index }}" name="peserta[{{ $index }}][nama_peserta]" required>
            </div>
            <div class="col-md-6">
                <label for="nim_{{ $index }}" class="form-label">NIM</label>
                <input type="text" class="form-control" id="nim_{{ $index }}" name="peserta[{{ $index }}][nim]" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="email_{{ $index }}" class="form-label">Email</label>
                <input type="email" class="form-control" id="email_{{ $index }}" name="peserta[{{ $index }}][email]" required>
            </div>
            <div class="col-md-6">
                <label for="hp_{{ $index }}" class="form-label">No. HP</label>
                <input type="text" class="form-control" id="no_hp_{{ $index }}" name="peserta[{{ $index }}][no_hp]" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="provinsi_{{ $index }}" class="form-label">Provinsi</label>
                <select name="peserta[{{ $index }}][provinsi]" class="form-select" id="provinsi_{{ $index }}" required>
                    <option value="">- Pilih Provinsi -</option>
                    @foreach ($provinsi as $prov)
                        <option value="{{ $prov->nama_provinsi }}">{{ $prov->nama_provinsi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="institusi_{{ $index }}" class="form-label">Institusi</label>
                <select name="peserta[{{ $index }}][institusi]" class="form-select" id="institusi_{{ $index }}" required>
                    <option value="">- Pilih Institusi -</option>
                    @foreach ($institusi as $inst)
                        <option value="{{ $inst->nama_institusi }}">{{ $inst->nama_institusi }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-3">
             <div class="col-md-6">
                <label for="prodi_{{ $index }}" class="form-label">Prodi</label>
                <select name="peserta[{{ $index }}][prodi]" class="form-select" id="prodi_{{ $index }}" required>
                    <option value="">- Pilih Prodi -</option>
                    @foreach ($prodi as $prods)
                        <option value="{{ $prods->nama_jurusan }}">{{ $prods->nama_jurusan }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Upload KTP</label>
                <input type="file" name="peserta[{{ $index }}][ktp]" class="form-control" accept="image/*" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <label class="form-label">Tanda Tangan</label>
                <div class="border p-2">
                    <canvas id="signature-pad-{{ $index }}" width="600" height="150" style="border:1px solid #ccc; width:100%; height:150px;"></canvas>
                </div>
                <input type="hidden" name="peserta[{{ $index }}][signature]" id="signature_{{ $index }}" required>
                <button type="button" class="btn btn-danger btn-sm mt-2" onclick="clearSignature({{ $index }})">Hapus Tanda Tangan</button>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<script>
    var signaturePads = {};
    window.onload = function () {
        document.querySelectorAll("canvas[id^='signature-pad-']").forEach(function (canvas) {
            var index = canvas.id.split('-').pop();
            var pad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)',
            });

            signaturePads[index] = pad;

            pad.onEnd = function () {
                var dataUrl = pad.toDataURL();
                document.getElementById('signature_' + index).value = dataUrl;
            };
        });
    };

    function clearSignature(index) {
        if (signaturePads[index]) {
            signaturePads[index].clear();
            document.getElementById('signature_' + index).value = '';
        }
    }
</script>
