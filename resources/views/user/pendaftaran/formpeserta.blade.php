@extends('layouts.app')

@section('content')

<div class="container mt-5">
    <h2 class="fw-bold">Pendaftaran</h2>
    <hr style="width: 230px; border-top: 2px solid #000;">
    <h4 class="text-center">{{ $mataLomba->nama_lomba }}</h4>

    <form action="{{ route('pendaftaran.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id_mataLomba" value="{{ $mataLomba->id }}">
        <input type="hidden" name="maksPeserta" value="{{ $mataLomba->maks_peserta }}">

        @if ($mataLomba->maks_peserta == 1)
            @include('user.pendaftaran.formindividu', ['index' => 0])
        @else
            <input type="text" name="nama_tim" class="form-control mb-3" placeholder="Nama Tim" required>
            @for ($i = 0; $i < $mataLomba->maks_peserta; $i++)
                <h5>Peserta {{ $i+1 }}</h5>
                <label>Posisi</label>
                <select name="peserta[{{ $i }}][posisi]" class="form-control mb-3" required>
                    <option value="">-- Pilih Posisi --</option>
                    <option value="Ketua">Ketua</option>
                    <option value="Anggota">Anggota</option>
                </select>
                @include('user.pendaftaran.formkelompok', ['index' => $i])
            @endfor
        @endif

        <button type="submit" class="btn btn-success mt-4">Submit</button>
    </form>
</div>

@include('layouts.footer')

@endsection
