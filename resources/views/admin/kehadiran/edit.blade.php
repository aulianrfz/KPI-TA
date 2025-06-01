@extends('layouts.apk')

@section('content')
<div class="container mt-4">
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Edit Daftar Hadir</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    <form action="{{ route('kehadiran.update', $pendaftar->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-3">
                <label for="nama_peserta" class="form-label">Nama Peserta</label>
                <input type="text" class="form-control" value="{{ $pendaftar->peserta->nama_peserta ?? '-' }}" readonly>
            </div>

            <div class="mb-3">
                <label for="institusi" class="form-label">Institusi</label>
                <input type="text" class="form-control" value="{{ $pendaftar->peserta->institusi ?? '-' }}" readonly>
            </div>

            <div class="mb-3">
                <label for="mata_lomba" class="form-label">Mata Lomba</label>
                <input type="text" class="form-control" value="{{ $pendaftar->mataLomba->nama_lomba ?? '-' }}" readonly>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text" class="form-control" value="{{ $pendaftar->peserta->email ?? '-' }}" readonly>
            </div>

            <div class="mb-3">
                <label for="no_hp" class="form-label">No HP</label>
                <input type="text" class="form-control" value="{{ $pendaftar->peserta->no_hp ?? '-' }}" readonly>
            </div>

             <div class="col-span-2 mb-3">
                <label class="form-label font-semibold">Status Kehadiran</label>

                @php
                    $status = optional($pendaftar->kehadiran)->status;
                @endphp

                <select name="status"
                    class="form-control rounded-lg border-gray-300 shadow-sm focus:ring focus:ring-blue-200"
                    {{ $status === 'Hadir' ? 'disabled' : '' }}>
                    
                    @if ($status === 'Hadir')
                        <option value="Hadir" selected>Hadir</option>
                        <option value="Tidak Hadir">Tidak Hadir</option>
                    @else
                        <option value="Tidak Hadir" selected>Tidak Hadir</option>
                        <option value="Hadir">Hadir</option>
                    @endif
                </select>

                @if ($status === 'Hadir')
                    <p class="text-sm text-red-600 mt-1">Peserta ini sudah melakukan kehadiran. Status tidak dapat diubah.</p>
                @else
                    <p class="text-sm text-gray-600 mt-1">Peserta belum hadir. Status saat ini: <strong>Tidak Hadir</strong>.</p>
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <a href="{{ route('kehadiran.mata-lomba', ['mataLombaId' => $pendaftar->mata_lomba_id]) }}" class="btn btn-primary">Kembali</a>

            @if(optional($pendaftar->kehadiran)->status != 'Hadir')
                <button type="submit" class="btn btn-success">Simpan</button>
            @endif
        </div>
    </form>
</div>
@endsection
