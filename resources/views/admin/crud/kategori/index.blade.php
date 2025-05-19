@extends('layouts.apk')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Data Kategori</h4>
        <a href="{{ route('kategori.create') }}" class="btn btn-primary">+ Tambah Kategori</a>
    </div>

    <form method="GET" action="{{ route('kategori.index') }}" class="mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <select name="event_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Event</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                            {{ $event->nama_event }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-secondary" type="submit">
                    <i class="fa fa-search"></i> Cari
                </button>
            </div>
            @if(request('search') || request('event_id'))
                <div class="col-auto">
                    <a href="{{ route('kategori.index') }}" class="btn btn-outline-danger">
                        <i class="fa fa-times"></i> Reset
                    </a>
                </div>
            @endif
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
                                <a href="{{ route('mataLomba.index', ['kategori_id' => $kategori->id]) }}" class="btn btn-info btn-sm">
                                    <i class="fa fa-eye"></i> Sub Kategori
                                </a>
                                <a href="{{ route('kategori.edit', $kategori->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('kategori.destroy', $kategori->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin mau hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Hapus</button>
                                </form>
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

    <div class="mt-3">
        {{ $kategorislomba->withQueryString()->links() }}
    </div>
</div>
@endsection
