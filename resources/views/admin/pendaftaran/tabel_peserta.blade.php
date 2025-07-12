@extends('layouts.apk')
@section('title', 'Data Peserta')
@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4">Peserta - Event: {{ $eventData->nama_event }}</h4>
    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover text-center">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Peserta</th>
                        <th>NIM</th>
                        <th>Institusi</th>
                        <th>Jenis</th>
                        <th>No HP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendaftar as $index => $p)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $p->peserta->nama_peserta ?? '-' }}</td>
                            <td>{{ $p->peserta->nim ?? '-' }}</td>
                            <td>{{ $p->peserta->institusi ?? '-' }}</td>
                            <td>{{ $p->peserta->jenis_peserta ?? '-' }}</td>
                            <td>{{ $p->peserta->no_hp ?? '-' }}</td>
                            <td>
                                <a href="{{ route('pendaftaran.peserta.edit', $p->peserta->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('pendaftaran.peserta.destroy', $p->peserta->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
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
    </div>
</div>
@endsection