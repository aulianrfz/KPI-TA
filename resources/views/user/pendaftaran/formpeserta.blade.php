@extends('layouts.app')

@section('content')

@include('layouts.navbar')

<div class="container">
    <h2 class="text-bold">Registration</h2>
    <hr style="width: 230px; border-top: 2px solid #000;">
    <h4 class="text-center">{{ $mataLomba->nama_mataLomba }}</h4>

    <form action="{{ route('pendaftaran.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($maksPeserta == 1)
            @include('user.pendaftaran.formindividu', ['index' => 0])
        @else
            <input type="text" name="nama_tim" class="form-control mb-3" placeholder="Nama Tim" required>
            @for ($i = 0; $i < $maksPeserta; $i++)
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

        <button type="submit" class="btn btn-success btn-block mt-4">Submit</button>
    </form>
</div>

@include('layouts.footer')

@endsection
