@extends('layouts.apk')

@section('title', 'Atur Posisi Nama Sertifikat')

@section('content')
<div class="container my-5">
    <h3 class="fw-bold mb-4">Atur Posisi Nama - {{ $event->nama_event }}</h3>

    <div class="position-relative border shadow rounded" style="width: 1122px; height: 794px; margin: auto;">
        <img src="{{ asset('storage/' . $template->nama_file) }}"
             class="img-fluid"
             id="templateImage"
             style="width: 100%; height: 100%; object-fit: cover;">

        <div id="namePreview"
             style="position: absolute;
                    font-weight: bold;
                    color: red;
                    font-size: 24px;
                    cursor: move;
                    left: {{ $template->posisi_x }}px;
                    top: {{ $template->posisi_y }}px;
                    font-family: {{ $template->font ?? 'sans-serif' }};">
            Nama Peserta
        </div>
    </div>

    <form method="POST" action="{{ route('sertifikat.simpan', $event->id) }}" class="mt-4 text-center">
        @csrf
        <input type="hidden" name="x" id="xPos" value="{{ $template->posisi_x }}">
        <input type="hidden" name="y" id="yPos" value="{{ $template->posisi_y }}">

        <div class="mb-3">
            <label for="font" class="form-label fw-semibold">Pilih Font</label>
            @php
            $fontGroups = [
                'Sans-serif' => [
                    'Arial, sans-serif' => 'Arial',
                    'Helvetica, sans-serif' => 'Helvetica',
                    'Tahoma, sans-serif' => 'Tahoma',
                    'Verdana, sans-serif' => 'Verdana',
                    'Impact, sans-serif' => 'Impact',
                ],
                'Serif' => [
                    '"Times New Roman", serif' => 'Times New Roman',
                    'Georgia, serif' => 'Georgia',
                ],
                'Monospace' => [
                    '"Courier New", monospace' => 'Courier New',
                    'monospace' => 'Monospace (Default)',
                ],
                'Cursive & Script' => [
                    '"Comic Sans MS", cursive' => 'Comic Sans MS',
                    'cursive' => 'Cursive (Default)',
                ],
                'Default System' => [
                    'sans-serif' => 'Sans-serif (Default)',
                    'serif' => 'Serif (Default)',
                ],
            ];

            @endphp
            <select name="font" id="font" class="form-select text-center" style="max-width: 350px; margin: auto;">
                @foreach($fontGroups as $groupLabel => $fonts)
                    <optgroup label="{{ $groupLabel }}">
                        @foreach($fonts as $value => $label)
                            <option value="{{ $value }}"
                                style="font-family: {{ $value }};"
                                {{ $template->font === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success mt-2">
            <i class="bi bi-check-circle me-1"></i> Simpan Posisi
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const namePreview = document.getElementById('namePreview');
    const container = document.querySelector('.position-relative');
    const xInput = document.getElementById('xPos');
    const yInput = document.getElementById('yPos');
    const fontSelect = document.getElementById('font');

    let isDragging = false;

    namePreview.addEventListener('mousedown', function (e) {
        isDragging = true;
        e.preventDefault();
    });

    document.addEventListener('mouseup', function () {
        isDragging = false;
    });

    document.addEventListener('mousemove', function (e) {
        if (!isDragging) return;

        const rect = container.getBoundingClientRect();
        let x = e.clientX - rect.left;
        let y = e.clientY - rect.top;

        x = Math.max(0, Math.min(x, container.offsetWidth - namePreview.offsetWidth));
        y = Math.max(0, Math.min(y, container.offsetHeight - namePreview.offsetHeight));

        namePreview.style.left = x + 'px';
        namePreview.style.top = y + 'px';

        xInput.value = Math.floor(x);
        yInput.value = Math.floor(y);
    });

    fontSelect.addEventListener('change', function () {
        namePreview.style.fontFamily = this.value;
    });
});
</script>
@endsection
