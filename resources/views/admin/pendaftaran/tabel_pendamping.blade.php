@extends('layouts.apk')

@section('title', 'Data Pendamping')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4">Pendamping - Event: {{ $eventData->nama_event }}</h4>

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover text-center">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Instansi</th>
                        <th>Email</th>
                        <th>No HP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendamping as $index => $p)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $p->pembimbing->nama_lengkap ?? '-' }}</td>
                            <td>{{ $p->pembimbing->nip ?? '-' }}</td>
                            <td>{{ $p->pembimbing->instansi ?? '-' }}</td>
                            <td>{{ $p->pembimbing->email ?? '-' }}</td>
                            <td>{{ $p->pembimbing->no_hp ?? '-' }}</td>
                            <td>
                                <a href="{{ route('pendaftaran.pembimbing.edit', $p->pembimbing->id) }}" class="btn btn-sm btn-warning">Edit</a>

                                <form action="{{ route('pendaftaran.pembimbing.destroy', $p->pembimbing->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pendamping ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Tidak ada pendamping terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
