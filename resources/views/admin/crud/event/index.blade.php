@extends('layouts.apk')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Data Event</h4>
        <a href="{{ route('listevent.create') }}" class="btn btn-primary">+ Tambah Event</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Nama Event</th>
                <th>Penyelenggara</th>
                <th>Mulai Dilaksanakan Pada Tanggal</th>
                <th>Foto</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $index => $event)
            <tr>
                <td>{{ $events->firstItem() + $index }}</td>
                <td>{{ $event->nama_event }}</td>
                <td>{{ $event->penyelenggara }}</td>
                <td>{{ $event->tanggal }}</td>
                <td>
                    @if($event->foto)
                        <img src="{{ asset('storage/' . $event->foto) }}" width="80">
                    @else -
                    @endif
                </td>
                <td>
                    <a href="{{ route('listevent.edit', $event->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                    <form action="{{ route('listevent.destroy', $event->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus event ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-end mt-3">
        {{ $events->links() }}
    </div>
</div>
@endsection
