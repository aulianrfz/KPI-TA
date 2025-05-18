@extends('layouts.apk')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Sub Kategori</h4>
        <a href="{{ route('subkategori.create') }}" class="btn btn-primary">+ Tambah Data</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('subkategori.index') }}" class="mb-3">
        <div class="input-group" style="max-width: 400px;">
            <select name="kategori_id" class="form-select">
                <option value="">Semua Kategori</option>
                @foreach(\App\Models\KategoriLomba::all() as $kategori)
                    <option value="{{ $kategori->id }}" {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                        {{ $kategori->nama_kategori }}
                    </option>
                @endforeach
            </select>
            <button class="btn btn-outline-secondary" type="submit">
                <i class="fa fa-search"></i> Cari
            </button>
            @if(request('kategori_id'))
                <a href="{{ route('subkategori.index') }}" class="btn btn-outline-danger">
                    <i class="fa fa-times"></i> Reset
                </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Nama SubKategori</th>
                    <th>Jurusan</th>
                    <th>Maks Peserta</th>
                    <th>Biaya</th>
                    <th>Foto</th>
                    <th style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subkategoris as $index => $subkategori)
                <tr>
                    <td>{{ $subkategoris->firstItem() + $index }}</td>
                    <td>{{ $subkategori->kategori->nama_kategori ?? '-' }}</td>
                    <td>{{ $subkategori->nama_lomba }}</td>
                    <td>{{ $subkategori->jurusan }}</td>
                    <td>{{ $subkategori->maks_peserta }}</td>
                    <td>Rp {{ number_format($subkategori->biaya_pendaftaran, 0, ',', '.') }}</td>
                    <td>
                        @if($subkategori->foto_kompetisi)
                            <img src="{{ asset('storage/' . $subkategori->foto_kompetisi) }}" width="70">
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('subkategori.edit', $subkategori->id) }}" class="btn btn-sm btn-warning me-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('subkategori.destroy', $subkategori->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end">
        {{ $subkategoris->withQueryString()->links() }}
    </div>
</div>
@endsection
