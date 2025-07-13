@extends('layouts.apk')

@section('title', 'Data Pendamping')

@section('content')
    <h4 class="fw-bold mb-4" style="color: #0367A6;">Pendamping - Event: {{ $eventData->nama_event }}</h4>

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

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4 p-4 text-center w-100">
                <div class="mb-3 bg-primary bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center mx-auto" style="width: 70px; height: 70px;">
                    <i class="bi bi-person-badge-fill text-primary fs-2"></i>
                </div>
                <h6 class="text-muted mb-1">Total Pendamping</h6>
                <h2 class="fw-bold text-dark">{{ $totalPendamping }}</h2>
            </div>
        </div>
    </div>

<form action="{{ route('admin.pendaftaran.pendamping', ['event' => $eventData->id]) }}" method="GET" class="mb-4">
    <div class="row gy-2 gx-3 align-items-center justify-content-between">
        {{-- Search --}}
        <div class="col-md-6">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-lg" placeholder="🔍 Cari nama, email, atau NIP...">
        </div>

        {{-- Filter Sort + Button --}}
        <div class="col-md-5">
            <div class="row g-2">
                <div class="col-md-6">
                    <select name="sort" class="form-select form-select-lg">
                        <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>📅 Terbaru</option>
                        <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>📁 Terlama</option>
                    </select>
                </div>
                <div class="col-md-3 d-grid">
                    <button class="btn btn-lg btn-primary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                <div class="col-md-3 d-grid">
                    <a href="{{ route('admin.pendaftaran.pendamping', ['event' => $eventData->id]) }}" class="btn btn-lg btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>


    <div class="card shadow-sm border-0 mb-5">
        <div class="card-body table-responsive">
            <table class="table table-hover table-bordered text-center align-middle">
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
                            <td>{{ $pendamping->firstItem() + $index }}</td>
                            <td>{{ $p->pembimbing->nama_lengkap ?? '-' }}</td>
                            <td>{{ $p->pembimbing->nip ?? '-' }}</td>
                            <td>{{ $p->pembimbing->instansi ?? '-' }}</td>
                            <td>{{ $p->pembimbing->email ?? '-' }}</td>
                            <td>{{ $p->pembimbing->no_hp ?? '-' }}</td>
                            <td>
                                <a href="{{ route('pendaftaran.pembimbing.edit', $p->pembimbing->id) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form id="form-delete-{{ $p->pembimbing->id }}" 
                                      action="{{ route('pendaftaran.pembimbing.destroy', $p->pembimbing->id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmHapusPendamping('{{ $p->pembimbing->id }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted fst-italic">Belum ada data pendamping.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-end mt-3">
                {{ $pendamping->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmHapusPendamping(pembimbingId) {
            Swal.fire({
                title: 'Hapus Pendamping?',
                text: 'Data pendamping akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-delete-' + pembimbingId).submit();
                }
            });
        }
    </script>
@endsection
