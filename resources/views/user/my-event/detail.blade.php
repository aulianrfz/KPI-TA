@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        {{-- Sidebar --}}
        <div class="col-md-2 d-none d-md-block bg-light border-end p-3">
            <ul class="nav flex-column mt-4">
                <li class="nav-item mb-3">
                    <a href="{{ route('events.list') }}" class="nav-link text-primary">
                        <i class="bi bi-person-circle me-2"></i> My Categories
                    </a>
                </li>
                <li class="nav-item mb-3">
                    <a href="{{ route('pembayaran.index') }}" class="nav-link text-dark">
                        <i class="bi bi-wallet2 me-2"></i> Pembayaran
                    </a>
                </li>
            </ul>
        </div>

        {{-- Main Content --}}
        <div class="col-md-10">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h4 class="fw-bold mb-4 text-center text-primary">
                        <i class="bi bi-award-fill me-2" style="color: #0367A6"></i>{{ $pendaftar->mataLomba->nama_lomba ?? 'Nama Lomba' }}
                    </h4>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th class="text-start">Nama Peserta</th>
                                    <th>Kuisioner</th>
                                    <th>Kehadiran</th>
                                    <th>Sertifikat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $peserta = $pendaftar->peserta;
                                    $daftarPeserta = collect();

                                    if ($peserta && $peserta->tim->isNotEmpty()) {
                                        $daftarPeserta = $peserta->tim->first()->peserta ?? collect();
                                    } elseif ($peserta) {
                                        $daftarPeserta = collect([$peserta]);
                                    }
                                @endphp

                                @forelse ($daftarPeserta as $anggota)
                                    @php
                                        $jawabanCount = $anggota->jawabanKuisioner->count();
                                        $statusHadir = $anggota->pendaftar?->status_kehadiran ?? null;
                                        $kuisionerSelesai = $kuisionerCount > 0 && $jawabanCount >= $kuisionerCount;
                                    @endphp

                                    <tr class="text-center">
                                        {{-- Nama --}}
                                        <td class="text-start fw-semibold">{{ $anggota->nama_peserta ?? '-' }}</td>

                                        {{-- Kuisioner --}}
                                        <td>
                                            @if ($kuisionerSelesai)
                                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                                                    <i class="bi bi-check-circle me-1"></i> Selesai
                                                </span>
                                            @elseif ($statusHadir === 'Hadir')
                                                <a href="{{ route('kuisioner.isi', ['peserta' => $anggota->id]) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil-square me-1"></i> Isi Kuisioner
                                                </a>
                                            @else
                                                <button class="btn btn-sm btn-outline-secondary" disabled title="Isi kuisioner setelah hadir">
                                                    <i class="bi bi-pencil-square me-1"></i> Isi Kuisioner
                                                </button>
                                            @endif
                                        </td>

                                        {{-- Kehadiran --}}
                                        <td>
                                            @if ($statusHadir === 'Hadir')
                                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                                                    <i class="bi bi-person-check-fill me-1"></i> Hadir
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2">
                                                    <i class="bi bi-person-x-fill me-1"></i> Tidak Hadir
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Sertifikat --}}
                                        <td>
                                            @if ($kuisionerSelesai)
                                                <a href="{{ route('sertifikat.download', $anggota->id) }}" class="btn btn-sm btn-success">
                                                    <i class="bi bi-download me-1"></i> Download Sertifikat
                                                </a>
                                            @else
                                                <button class="btn btn-sm btn-outline-secondary" disabled title="Selesaikan kuisioner">
                                                    <i class="bi bi-lock me-1"></i> Belum Selesai
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada peserta terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 text-end">
                        <a href="{{ route('events.list') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
