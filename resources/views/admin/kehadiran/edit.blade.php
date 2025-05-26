@extends('layouts.apk')

@section('content')
<div class="max-w-xl mx-auto p-6 bg-white rounded-xl shadow mt-10">
    <h2 class="text-lg font-bold mb-4">Edit Pendaftar</h2>
    <form action="{{ route('pendaftar.update', $pendaftar->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block mb-1">Nama Peserta</label>
            <input type="text" name="nama_peserta" class="form-input w-full" value="{{ $pendaftar->peserta->nama_peserta }}">
        </div>

        <div class="mb-4">
            <label class="block mb-1">Institusi</label>
            <input type="text" name="institusi" class="form-input w-full" value="{{ $pendaftar->peserta->institusi }}">
        </div>

        <div class="mb-4">
            <label class="block mb-1">Kategori</label>
            <input type="text" name="kategori" class="form-input w-full" value="{{ $pendaftar->matalomba->kategori->nama_kategori }}">
        </div>

        <div class="mb-4">
            <label class="block mb-1">Mata Lomba</label>
            <input type="text" name="mata_lomba" class="form-input w-full" value="{{ $pendaftar->mataLomba->nama_lomba }}">
        </div>

        <div class="mb-4">
            <label class="block mb-1">Status Kehadiran</label>
            <input type="text" name="status" class="form-input w-full" value="{{ $pendaftar->kehadiran->status }}">
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('kehadiran.index') }}" class="bg-gray-200 px-4 py-2 rounded">Batal</a>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
        </div>
    </form>
</div>
@endsection
