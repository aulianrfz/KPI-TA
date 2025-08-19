@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm rounded-4 p-4">
                <h4 class="fw-bold mb-4"  style="color: #0367A6">Ajukan Pengajuan Baru</h4>

                <form method="POST" action="{{ route('pengajuan.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="jenis" class="form-label">Jenis Pengajuan</label>
                        <select name="jenis" id="jenisSelect" class="form-select @error('jenis') is-invalid @enderror" required onchange="toggleCustomJenis(this)">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Pendaftaran Lomba" {{ old('jenis') == 'Pendaftaran Lomba' ? 'selected' : '' }}>Pendaftaran Peserta Lomba</option>
                            <option value="Pengajuan Retur" {{ old('jenis') == 'Pengajuan Retur' ? 'selected' : '' }}>Pengajuan Retur</option>
                            <option value="Lainnya" {{ old('jenis') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('jenis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 d-none" id="customJenisContainer">
                        <label for="custom_jenis" class="form-label">Jenis Lainnya</label>
                        <input type="text" name="jenis" class="form-control" placeholder="Tulis jenis pengajuan" value="{{ old('jenis') == 'Lainnya' ? '' : old('jenis') }}">
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi / Keterangan Tambahan</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="4" placeholder="Tambahkan keterangan (jika perlu)">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('pengajuan.index') }}" class="btn btn-outline-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Ajukan</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleCustomJenis(select) {
        const container = document.getElementById('customJenisContainer');
        if (select.value === 'Lainnya') {
            container.classList.remove('d-none');
            container.querySelector('input').setAttribute('name', 'jenis');
        } else {
            container.classList.add('d-none');
            container.querySelector('input').removeAttribute('name');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('jenisSelect');
        toggleCustomJenis(select);
    });
</script>
@endpush
@endsection
