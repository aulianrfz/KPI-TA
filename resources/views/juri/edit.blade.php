@extends('layouts.apk')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous">

<style>
    .form-page-title-container {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .form-page-title {
        color: #3A3B7B;
        font-weight: 600;
        font-size: 1.75rem;
        margin-bottom: 0;
    }
    .btn-back-icon {
        font-size: 1.25rem;
        color: #6c757d;
    }
    .btn-back-icon:hover {
        color: #3A3B7B;
    }
    .form-card {
        background-color: #ffffff;
        border-radius: 0.75rem;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
    }
    .form-label-styled {
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: #495057;
    }
    .form-control-custom-bg,
    .form-select-custom-bg {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 0.375rem;
    }
    .form-control-custom-bg:focus,
    .form-select-custom-bg:focus {
        background-color: #ffffff;
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .btn-custom-cancel {
        background-color: #dc3545;
        color: #ffffff;
        border-color: #dc3545;
        padding: 0.5rem 1rem;
    }
    .btn-custom-cancel:hover {
        background-color: #bb2d3b;
        border-color: #b02a37;
    }
    .btn-custom-save {
        background-color: #198754;
        color: #ffffff;
        border-color: #198754;
        padding: 0.5rem 1rem;
    }
    .btn-custom-save:hover {
        background-color: #157347;
        border-color: #146c43;
    }
    .form-actions {
        margin-top: 2rem;
        text-align: right;
    }
</style>

<div class="container">
    <div class="form-page-title-container">
        <a href="{{ route('juri.index') }}" class="btn btn-link p-0 me-3" title="Kembali ke Daftar Juri">
            <i class="fas fa-arrow-left btn-back-icon"></i>
        </a>
        <h2 class="form-page-title">Edit Juri</h2>
    </div>

    <div class="form-card">
        <form action="{{ route('juri.update', $juri->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="nama" class="form-label form-label-styled">Nama Juri</label>
                <input 
                    type="text" 
                    name="nama" 
                    id="nama"
                    class="form-control form-control-custom-bg @error('nama') is-invalid @enderror"
                    value="{{ old('nama', $juri->nama) }}" 
                    placeholder="Masukkan nama juri"
                    required>
                @error('nama')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="jabatan" class="form-label form-label-styled">Jabatan</label>
                <input 
                    type="text" 
                    name="jabatan"
                    id="jabatan"
                    class="form-control form-control-custom-bg @error('jabatan') is-invalid @enderror"
                    value="{{ old('jabatan', $juri->jabatan) }}" 
                    placeholder="Masukkan jabatan juri"
                    required>
                @error('jabatan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="mata_lomba_id" class="form-label form-label-styled">Sub Kategori Lomba</label>
                <select 
                    name="mata_lomba_id" 
                    id="mata_lomba_id" 
                    class="form-select form-select-custom-bg @error('mata_lomba_id') is-invalid @enderror" 
                    required>
                    <option value="">-- Pilih Sub Kategori --</option>
                    @foreach($subKategoris as $sub)
                        <option 
                            value="{{ $sub->id }}" 
                            {{ old('mata_lomba_id', $juri->mata_lomba_id) == $sub->id ? 'selected' : '' }}
                        >
                            {{ $sub->nama_lomba }}
                        </option>
                    @endforeach
                </select>
                @error('mata_lomba_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('juri.index') }}" class="btn btn-custom-cancel me-2">Batal</a>
                <button type="submit" class="btn btn-custom-save">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
