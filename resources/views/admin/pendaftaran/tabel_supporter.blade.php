@extends('layouts.apk')

@section('title', 'Data Supporter')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4">Supporter - Event: {{ $eventData->nama_event }}</h4>

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover text-center">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Instansi</th>
                        <th>Email</th>
                        <th>No HP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supporter as $index => $s)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $s->supporter->nama ?? '-' }}</td>
                            <td>{{ $s->supporter->instansi ?? '-' }}</td>
                            <td>{{ $s->supporter->email ?? '-' }}</td>
                            <td>{{ $s->supporter->no_hp ?? '-' }}</td>
                            <td>
                                <a href="{{ route('pendaftaran.supporter.edit', $s->supporter->id) }}" class="btn btn-sm btn-warning">Edit</a>

                                <form action="{{ route('pendaftaran.supporter.destroy', $s->supporter->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus supporter ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Tidak ada supporter terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
