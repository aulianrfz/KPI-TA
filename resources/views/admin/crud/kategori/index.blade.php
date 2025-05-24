@extends('layouts.apk')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Data Kategori</h4>
        <a href="{{ route('kategori.create') }}" class="btn btn-primary">+ Tambah Kategori</a>
    </div>


    <form method="GET" action="{{ route('kategori.index') }}" class="mb-3">
        <div class="d-flex justify-content-start align-items-center gap-2 flex-wrap">
            <div class="position-relative" style="width: 300px;">
                <input
                    type="text"
                    name="search"
                    class="form-control rounded-pill ps-4"
                    placeholder="Cari berdasarkan nama kategori"
                    value="{{ request('search') }}"
                >
            </div>
            <button type="submit" class="btn btn-success">
            <i class="fa fa-search"></i>
            </button>
        </div>
    </form>


    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">Nama Kategori</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategorislomba as $kategori)
                        <tr>
                            <td class="align-middle text-center">{{ $kategori->nama_kategori }}</td>
                            <td class="align-middle text-center">
                                <a href="{{ route('kategori.edit', $kategori->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <form action="{{ route('kategori.destroy', $kategori->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin mau hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> </button>
                                </form>
                                <a href="{{ route('mataLomba.index', ['kategori_id' => $kategori->id]) }}" class="btn btn-info btn-sm">
                                    <i class="fa fa-eye"></i> Sub Kategori
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center">Belum ada data kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-end align-items-center mt-3 gap-2">
        <span class="small text-muted mb-0">
            Page {{ $kategorislomba->currentPage() }} of {{ $kategorislomba->lastPage() }}
        </span>
        @if ($kategorislomba->onFirstPage())
            <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">‹</span>
        @else
            <a href="{{ $kategorislomba->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">‹</a>
        @endif
        @if ($kategorislomba->hasMorePages())
            <a href="{{ $kategorislomba->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">›</a>
        @else
            <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">›</span>
        @endif
    </div>
</div>
@endsection
