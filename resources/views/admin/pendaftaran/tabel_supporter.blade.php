@extends('layouts.apk')

@section('title', 'Data Supporter')

@section('content')
    <h4 class="fw-bold mb-4" style="color: #0367A6;">Supporter - Event: {{ $eventData->nama_event }}</h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover text-center align-middle">
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

                                <form id="form-delete-{{ $s->supporter->id }}"
                                      action="{{ route('pendaftaran.supporter.destroy', $s->supporter->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger"
                                        onclick="confirmHapusSupporter('{{ $s->supporter->id }}')">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Tidak ada supporter terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function confirmHapusSupporter(id) {
        Swal.fire({
            title: 'Hapus Supporter?',
            text: 'Data supporter ini akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-delete-' + id).submit();
            }
        });

        return false;
    }
</script>
@endsection
