@extends('layouts.apk')

@section('content')
<div class="container mt-4">
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Edit Daftar Hadir</h2>
    <form action="{{ route('kehadiran.update', $pendaftar->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-3">
                <label for="nama_peserta" class="form-label">Nama Peserta</label>
                <input type="text" name="nama_peserta" class="form-control" value="{{ $pendaftar->peserta->nama_peserta ?? '-' }}" readonly>
            </div>

            <div class="mb-3">
                <label for="institusi" class="form-label">Institusi</label>
                <input type="text" name="institusi" class="form-control" value="{{ $pendaftar->peserta->institusi ?? '-' }}" readonly>
            </div>

            <div class="mb-3">
                <label for="mata_lomba" class="form-label">Mata Lomba</label>
                <input type="text" name="mata_lomba" class="form-control" value="{{ $pendaftar->mata_lomba->nama_mata_lomba ?? '-' }}" readonly>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text" name="email" class="form-control" value="{{ $pendaftar->peserta->email ?? '-' }}" readonly>
            </div>

            <div class="mb-3">
                <label for="no_hp" class="form-label">No HP</label>
                <input type="text" name="no_hp" class="form-control" value="{{ $pendaftar->peserta->no_hp ?? '-' }}" readonly>
            </div>

            <div class="col-span-2 mb-3">
                <label class="form-label font-semibold">Status Kehadiran</label>
                <select name="status" class="form-control rounded-lg border-gray-300 shadow-sm focus:ring focus:ring-blue-200">
                    <option value="Hadir" {{ optional($pendaftar->kehadiran)->status == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="Tidak Hadir" {{ optional($pendaftar->kehadiran)->status == 'Tidak Hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                </select>
            </div>
        </div>

    <div class="flex justify-end gap-2 mt-6">
        <a href="{{ route('kehadiran.index') }}" class="btn btn-primary">Kembali</a>
        <button type="submit" class="btn btn-success">Simpan</button>
    </div>

    </form>
</div>
@endsection
