@extends('layouts.apk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold" style="color: #0367A6;">Tambah Footer Link</h4>
    <a href="{{ route('links.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
</div>

<div class="card p-4">
    <form action="{{ route('links.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="label" class="form-label">Label</label>
            <input type="text" name="label" id="label" class="form-control" value="{{ old('label') }}" required>
        </div>

        <div class="mb-3">
            <label for="url" class="form-label">URL</label>
            <input type="text" name="url" id="url" class="form-control" value="{{ old('url') }}" required>
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <select name="type" id="type" class="form-select" required>
                <option value="">-- Pilih Type --</option>
                <option value="link" {{ old('type') == 'link' ? 'selected' : '' }}>Link</option>
                <option value="social" {{ old('type') == 'social' ? 'selected' : '' }}>Social</option>
            </select>
        </div>

        <!-- Field icon, default hidden -->
        <div class="mb-3" id="iconField" style="display: none;">
            <label class="form-label">Icon (Opsional)</label>

            <div class="mb-2">
                <input type="file" name="icon_file" id="icon_file" class="form-control">
                {{-- <button type="button" id="removeFile" class="btn btn-sm btn-outline-danger mt-1">Hapus File</button> --}}
            </div>

            <div>
                <input type="text" name="icon_url" id="icon_url" placeholder="Atau URL icon" class="form-control" value="{{ old('icon_url') }}">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Link</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('type');
    const iconField = document.getElementById('iconField');
    const iconFile = document.getElementById('icon_file');
    const iconUrl = document.getElementById('icon_url');
    const removeFileBtn = document.getElementById('removeFile');

    function toggleIconField() {
        if (typeSelect.value === 'social') {
            iconField.style.display = 'block';
        } else {
            iconField.style.display = 'none';
            iconFile.value = '';
            iconUrl.value = '';
        }
    }

    toggleIconField(); // cek saat load
    typeSelect.addEventListener('change', toggleIconField);

    // hanya boleh isi salah satu
    iconFile.addEventListener('change', function () {
        if (iconFile.files.length > 0) {
            iconUrl.value = '';
        }
    });

    iconUrl.addEventListener('input', function () {
        if (iconUrl.value.length > 0) {
            iconFile.value = '';
        }
    });

    // tombol hapus file
    removeFileBtn.addEventListener('click', function () {
        iconFile.value = '';
    });
});
</script>
@endsection
