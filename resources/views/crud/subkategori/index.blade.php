@extends('layouts.app')

@include('layouts.navbar')

@section('content')
<div class="container">
    <h1>Data Sub Kategori</h1>
    <a href="{{ route('subkategori.create') }}" class="btn btn-primary mb-3">Tambah Sub Kategori</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Kategori</th>
                <th>Nama SubKategori</th>
                <th>Jurusan</th>
                <th>Maks Peserta</th>
                <th>Biaya</th>
                <th>Foto Kompetisi</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subkategoris as $index => $subkategori)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $subkategori->kategori->name }}</td>
                <td>{{ $subkategori->name }}</td>
                <td>{{ $subkategori->jurusan }}</td>
                <td>{{ $subkategori->maks_peserta }}</td>
                <td>Rp {{ number_format($subkategori->biaya_pendaftaran, 0, ',', '.') }}</td>
                <td>
                    @if($subkategori->foto_kompetisi)
                        <img src="{{ asset('storage/' . $subkategori->foto_kompetisi) }}" width="80">
                    @else
                        -
                    @endif
                </td>
                <td>
                    <a href="{{ route('subkategori.edit', $subkategori->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('subkategori.destroy', $subkategori->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin mau hapus?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@include('layouts.footer')

@endsection
