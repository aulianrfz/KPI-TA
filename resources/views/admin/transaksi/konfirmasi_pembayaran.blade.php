@extends('layouts.apk')

@section('title', 'Transaksi') {{-- Mengatur judul halaman di layout --}}

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h4 class="mb-0">Verifikasi Pembayaran</h4>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Form untuk Pencarian dan Pengurutan --}}
        <form method="GET" action="{{ route('transaksi.index') }}" class="mb-4" id="filterForm">
            <div class="row g-2 align-items-center">
                <div class="col-md-5 col-lg-4">
                    <div class="position-relative">
                        <input
                            type="text"
                            name="search"
                            class="form-control rounded-pill ps-5"
                            placeholder="Cari peserta atau institusi..."
                            value="{{ request('search') }}"
                        >
                        <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                    </div>
                </div>
                <div class="col-md-3 col-lg-2">
                    <select
                        name="sort"
                        onchange="document.getElementById('filterForm').submit()"
                        class="form-select rounded-pill"
                    >
                        <option value="">Urutkan</option>
                        <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                        <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </div>
                <div class="col-md-4 col-lg-6 text-md-end">
                    {{-- Tombol aksi akan ditempatkan di sini, di luar form filter --}}
                </div>
            </div>
        </form>

        {{-- Form untuk Aksi Massal (Setujui/Tolak) --}}
        <form method="POST" action="{{ route('admin.transaksi.bulkAction') }}" id="bulk-action-form">
            @csrf
            <div class="d-flex justify-content-end mb-3 gap-2">
                <button type="submit" name="action" value="reject" class="btn btn-sm fw-medium" style="background-color: #FFD6D7; color: #FF1C20; border: 1px solid #FF1C20;">
                    <i class="bi bi-x-circle me-1"></i> Tolak
                </button>
                <button type="submit" name="action" value="approve" class="btn btn-sm fw-medium" style="background-color: #C7F5EE; color: #00B69B; border: 1px solid #00B69B;">
                    <i class="bi bi-check-circle me-1"></i> Setujui
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 3%;"><input type="checkbox" class="form-check-input" id="select-all"></th>
                            <th>Peserta</th>
                            <th>Invoice</th>
                            <th>Institusi</th>
                            <th>Nama Lomba</th>
                            <th>Bukti Pembayaran</th>
                            <th>Status</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transaksi as $item)
                            <tr>
                                <td><input type="checkbox" class="form-check-input" name="ids[]" value="{{ $item->id }}"></td>
                                <td>{{ $item->peserta->nama_peserta ?? '-' }}</td>
                                <td>{{ $item->invoice->id ?? '-' }}</td>
                                <td>{{ $item->peserta->institusi ?? '-' }}</td>
                                <td>{{ $item->peserta->pendaftar->mataLomba->nama_lomba ?? '-' }}</td>
                                <td>
                                    @if($item->bukti_pembayaran)
                                        <a href="{{ asset('storage/' . $item->bukti_pembayaran) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye-fill me-1"></i> Lihat Bukti
                                        </a>
                                    @else
                                        <span class="text-muted">Tidak ada</span>
                                    @endif
                                </td>
                                <td>
                                    @php 
                                        $status = $item->peserta->pendaftar->status ?? 'Pending'; 
                                        $badgeClass = 'bg-secondary-subtle text-secondary-emphasis';
                                        if ($status === 'Disetujui') {
                                            $badgeClass = 'bg-success-subtle text-success-emphasis';
                                        } elseif ($status === 'Ditolak') {
                                            $badgeClass = 'bg-danger-subtle text-danger-emphasis';
                                        }
                                    @endphp
                                    <span class="badge rounded-pill {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                </td>
                                <td>{{ $item->waktu ? \Carbon\Carbon::parse($item->waktu)->isoFormat('D MMM YYYY, HH:mm') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-exclamation-circle fs-3 text-muted mb-2"></i>
                                    <p class="mb-0 text-muted">Belum ada data transaksi yang perlu diverifikasi.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        @if ($transaksi->hasPages())
        <div class="d-flex justify-content-end align-items-center mt-4">
            {{ $transaksi->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div> </div> <script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        if (selectAllCheckbox) {
            selectAllCheckbox.onclick = function() {
                let checkboxes = document.querySelectorAll('input[name="ids[]"]');
                checkboxes.forEach(cb => cb.checked = this.checked);
            }
        }
    });
</script>
@endsection
