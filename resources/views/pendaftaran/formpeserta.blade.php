@extends('layouts.app')

@section('content')

@include('layouts.navbar')

<div class="container">
    <h2 class="text-center">Registration</h2>
    <h4 class="text-center">{{ $subKategori->nama_subKategori }}</h4>

    <form action="{{ route('pendaftaran.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($maksPeserta == 1)
            @include('pendaftaran.formindividu', ['index' => 0])
        @else
            <input type="text" name="nama_tim" class="form-control mb-3" placeholder="Nama Tim" required>
            @for ($i = 0; $i < $maksPeserta; $i++)
                <h5>Peserta {{ $i+1 }}</h5>
                @include('pendaftaran.formkelompok', ['index' => $i])
            @endfor
        @endif

        <button type="submit" class="btn btn-success btn-block mt-4">Submit</button>
    </form>
</div>

@include('layouts.footer')

@endsection
