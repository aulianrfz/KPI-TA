<!-- Form Input Peserta -->
<div class="card mb-4">
    <div class="card-body">
        <input type="hidden" name="id_subkategori" value="{{ $subKategori->id }}">

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="nama_{{ $index }}" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama_{{ $index }}" name="peserta[{{ $index }}][nama]" required>
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
                <input type="text" class="form-control" id="hp_{{ $index }}" name="peserta[{{ $index }}][hp]" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="provinsi_{{ $index }}" class="form-label">Provinsi</label>
                <select name="peserta[{{ $index }}][provinsi_id]" class="form-select" id="provinsi_{{ $index }}" required>
                    <option value="">- Pilih Provinsi -</option>
                    @foreach ($provinsi as $prov)
                        <option value="{{ $prov->id }}">{{ $prov->nama_provinsi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="institusi_{{ $index }}" class="form-label">Institusi</label>
                <select name="peserta[{{ $index }}][institusi_id]" class="form-select" id="institusi_{{ $index }}" required>
                    <option value="">- Pilih Institusi -</option>
                    @foreach ($institusi as $inst)
                        <option value="{{ $inst->id }}">{{ $inst->nama_institusi }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="jurusan_{{ $index }}" class="form-label">Jurusan</label>
                <select name="peserta[{{ $index }}][jurusan_id]" class="form-select" id="jurusan_{{ $index }}" required>
                    <option value="">- Pilih Jurusan -</option>
                    @foreach ($jurusan as $jur)
                        <option value="{{ $jur->id }}">{{ $jur->nama_jurusan }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Upload KTP</label>
                <input type="file" name="peserta[{{ $index }}][ktp]" class="form-control" accept="image/*" required>
            </div>
        </div>

        <!-- Tanda Tangan -->
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

<!-- Signature Pad Script -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<script>
    // Object untuk menampung semua SignaturePad
    var signaturePads = {};

    // Setelah halaman siap
    window.onload = function () {
        // Ambil semua canvas dengan ID mulai "signature-pad-"
        document.querySelectorAll("canvas[id^='signature-pad-']").forEach(function (canvas) {
            var index = canvas.id.split('-').pop(); // Ambil index dari ID
            var pad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)', // opsional: transparan
            });

            // Simpan ke object
            signaturePads[index] = pad;

            // Setiap selesai tanda tangan
            pad.onEnd = function () {
                var dataUrl = pad.toDataURL();
                document.getElementById('signature_' + index).value = dataUrl;
            };
        });
    };

    // Fungsi hapus tanda tangan
    function clearSignature(index) {
        if (signaturePads[index]) {
            signaturePads[index].clear();
            document.getElementById('signature_' + index).value = '';
        }
    }
</script>
