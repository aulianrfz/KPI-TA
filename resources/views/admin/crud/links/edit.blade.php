@extends('layouts.apk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold" style="color: #0367A6;">Edit Footer Link</h4>
    <a href="{{ route('links.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
</div>

<div class="card p-4">
    <form action="{{ route('links.update', $link->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="label" class="form-label">Label</label>
            <input type="text" name="label" id="label" class="form-control" value="{{ old('label', $link->label) }}" required>
        </div>

        <div class="mb-3">
            <label for="url" class="form-label">URL</label>
            <input type="text" name="url" id="url" class="form-control" value="{{ old('url', $link->url) }}" required>
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <input type="text" class="form-control" value="{{ ucfirst($link->type) }}" disabled>
        </div>

        <!-- Field icon -->
        <div class="mb-3" id="iconField" style="display: none;">
            <label class="form-label">Icon (Opsional)</label>

            <div class="mb-2">
                <input type="file" name="icon_file" id="icon_file" class="form-control">
                @if($link->icon && !filter_var($link->icon, FILTER_VALIDATE_URL))
                    <div class="mt-1">
                        <img src="{{ asset($link->icon) }}" alt="icon" style="width:24px;height:24px;">
                    </div>
                @endif
            </div>

            <div>
                <input type="text" name="icon_url" id="icon_url" placeholder="Atau URL icon" class="form-control"
                    value="{{ old('icon_url', filter_var($link->icon, FILTER_VALIDATE_URL) ? $link->icon : '') }}">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Update Link</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const iconField = document.getElementById('iconField');
    const iconFile = document.getElementById('icon_file');
    const iconUrl = document.getElementById('icon_url');

    // field icon muncul jika type social atau logo
    const type = "{{ $link->type }}";
    if (type === 'social' || type === 'logo') {
        iconField.style.display = 'block';
    }

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
});
</script>
@endsection
