@extends('layouts.apk')
@section('title', 'Data Peserta')

@section('content')
<h4 class="fw-bold mb-4" style="color: #0367A6;">Peserta - Event: {{ $eventData->nama_event }}</h4>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row gx-3 gy-3">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4 p-4 h-100 d-flex flex-column justify-content-between" style="min-height: 220px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-2">Total Pendaftar</h6>
                    <h2 class="fw-bold mb-0">{{ $total }}</h2>
                </div>
                <div class="bg-primary bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 60px; height: 60px;">
                    <i class="bi bi-people-fill fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-2 col-6">
        <div class="card shadow-sm border-0 rounded-4 p-3 h-100 text-center d-flex flex-column justify-content-center" style="min-height: 220px;">
            <div class="mb-2 d-flex justify-content-center">
                <div class="bg-success bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-person-fill fs-4 text-success"></i>
                </div>
            </div>
            <h6 class="text-muted mb-1">Individu</h6>
            <h4 class="fw-bold">{{ $totalIndividu }}</h4>
        </div>
    </div>

    <div class="col-md-2 col-6">
        <div class="card shadow-sm border-0 rounded-4 p-3 h-100 text-center d-flex flex-column justify-content-center" style="min-height: 220px;">
            <div class="mb-2 d-flex justify-content-center">
                <div class="bg-success bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-people-fill fs-4 text-success"></i>
                </div>
            </div>
            <h6 class="text-muted mb-1">Tim</h6>
            <h4 class="fw-bold">{{ $timCount }}</h4>
        </div>
    </div>

    {{-- Pembayaran --}}
    <div class="col-md-4 d-flex flex-column gap-3">
        {{-- Belum Membayar --}}
        <div class="card shadow-sm border-0 rounded-4 p-3 d-flex justify-content-between align-items-center" style="min-height: 100px;">
            <div class="d-flex justify-content-between w-100 align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Belum Membayar</h6>
                    <h4 class="fw-bold text-danger">{{ $belumBayar }}</h4>
                </div>
                <div class="bg-danger bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-wallet-fill fs-4 text-danger"></i>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 p-3 d-flex justify-content-between align-items-center" style="min-height: 100px;">
            <div class="d-flex justify-content-between w-100 align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Sudah Membayar</h6>
                    <h4 class="fw-bold text-success">{{ $sudahBayar }}</h4>
                </div>
                <div class="bg-success bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-credit-card-2-front-fill fs-4 text-success"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 mt-4 flex-wrap gap-2">
    <form method="GET" class="d-flex align-items-center gap-2">
        <div class="input-group" style="width: 300px;">
            <span class="input-group-text bg-white border-end-0">
                <i class="fas fa-search text-muted"></i>
            </span>
            <input type="text" name="search" class="form-control border-start-0"
                   placeholder="Cari peserta..." value="{{ request('search') }}">
        </div>
    </form>

    <div class="dropdown">
        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            Urutkan
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('admin.pendaftaran.peserta', [$eventData->id, 'sort' => 'desc']) }}">Terbaru</a></li>
            <li><a class="dropdown-item" href="{{ route('admin.pendaftaran.peserta', [$eventData->id, 'sort' => 'asc']) }}">Terlama</a></li>
        </ul>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle text-center mb-0">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Nama Peserta</th>
                    <th>Tim/Individu</th>
                    <th>Tanggal & Waktu</th>
                    <th>Mata Lomba</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendaftar as $index => $p)
                    <tr>
                        <td>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $p->peserta->nama_peserta ?? '-' }}</td>
                        <td>{{ $p->peserta->jenis_peserta ?? '-' }}</td>
                        <td>
                            {{ $p->created_at->format('d/m/Y') }}<br>
                            <small class="text-muted">{{ $p->created_at->format('H:i') }}</small>
                        </td>
                        <td>{{ $p->mataLomba->nama_lomba ?? '-' }}</td>
                        <td>
                            <a href="{{ route('pendaftaran.peserta.edit', $p->peserta->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form id="form-delete-{{ $p->peserta->id }}" 
                                  action="{{ route('pendaftaran.peserta.destroy', $p->peserta->id) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="confirmHapus('{{ $p->peserta->id }}', '{{ $p->bergabung->posisi ?? 'Individu' }}', '{{ $p->peserta->tim_id ?? '' }}')">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-muted text-center py-3">Belum ada peserta terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmHapus(pesertaId, posisi, timId) {
        let title = '', text = '';

        if (posisi === 'Ketua') {
            title = 'Hapus Ketua Tim?';
            text = 'Menghapus Ketua akan menghapus seluruh anggota tim dengan Tim ID: ' + timId + '.';
        } else if (posisi === 'Anggota') {
            title = 'Hapus Anggota Tim?';
            text = 'Jika jumlah anggota jadi kurang dari minimum, penghapusan akan dibatalkan.';
        } else {
            title = 'Hapus Peserta Individu?';
            text = 'Data akan dihapus secara permanen.';
        }

        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-delete-' + pesertaId).submit();
            }
        });

        return false;
    }
</script>
@endsection
