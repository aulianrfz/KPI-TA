@extends('layouts.apk')

@section('content')
<div class="container mt-4">
    <h2>Edit Data Pembimbing</h2>
    <form action="{{ route('pendaftaran.pembimbing.update', $pembimbing->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Nama Lengkap</label>
            <input type="text" class="form-control" name="nama_lengkap" value="{{ $pembimbing->nama_lengkap }}" required>
        </div>

        <div class="mb-3">
            <label>NIP</label>
            <input type="text" class="form-control" name="nip" value="{{ $pembimbing->nip }}">
        </div>

        <div class="mb-3">
        <label for="instansi" class="form-label">Instansi</label>
            <select name="instansi" class="form-select" required>
                <option value="">- Pilih Instansi -</option>
                @foreach ($institusi as $inst)
                    <option value="{{ $inst->nama_institusi }}" {{ $pembimbing->instansi == $inst->nama_institusi ? 'selected' : '' }}>
                        {{ $inst->nama_institusi }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Jabatan</label>
            <input type="text" class="form-control" name="jabatan" value="{{ $pembimbing->jabatan }}">
        </div>

        <div class="mb-3">
            <label>No HP</label>
            <input type="text" class="form-control" name="no_hp" value="{{ $pembimbing->no_hp }}" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" name="email" value="{{ $pembimbing->email }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
