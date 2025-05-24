@extends('layouts.apk')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Verifikasi Pembayaran</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('transaksi.index') }}" class="mb-3">
        <div class="d-flex justify-content-first align-items-center gap-2 flex-wrap">
            <div class="position-relative" style="width: 350px;">
                <input
                    type="text"
                    name="search"
                    class="form-control rounded-pill ps-5"
                    placeholder="Search berdasarkan peserta atau institusi"
                    value="{{ request('search') }}"
                >
                <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                    <i class="bi bi-search"></i>
                </span>
            </div>

            <div>
                <select
                    name="sort"
                    onchange="this.form.submit()"
                    class="border border-gray-300 rounded-full py-2 px-4 bg-white text-gray-700 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Urutkan</option>
                    <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                    <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Terlama</option>
                </select>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.transaksi.bulkAction') }}" id="bulk-action-form">
        @csrf

        <div class="d-flex justify-content-end mb-3 gap-2">
            <button type="submit" name="action" value="approve" class="btn btn-success" style="background-color: #C7F5EE; color: #00B69B;">
                Setujui
            </button>
            <button type="submit" name="action" value="reject" class="btn btn-danger" style="background-color: #FFD6D7; color: #FF1C20;">
                Tolak
            </button>
        </div>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
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
                        <td><input type="checkbox" name="ids[]" value="{{ $item->id }}"></td>
                        <td>{{ $item->peserta->nama_peserta ?? '-' }}</td>
                        <td>{{ $item->invoice->jabatan ?? '-' }}</td>
                        <td>{{ $item->peserta->institusi ?? '-' }}</td>
                        <td>{{ $item->peserta->pendaftar->mataLomba->nama_lomba ?? '-' }}</td>
                        <td>
                            @if($item->bukti_pembayaran)
                                <a href="{{ asset('storage/' . $item->bukti_pembayaran) }}" target="_blank">Lihat File</a>
                            @else
                                Tidak ada
                            @endif
                        </td>
                        <td>
                            @php $status = $item->peserta->pendaftar->status ?? 'Pending'; @endphp
                            @if($status === 'Disetujui')
                                <span class="badge bg-success">Disetujui</span>
                            @elseif($status === 'Ditolak')
                                <span class="badge bg-danger">Ditolak</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                        <td>{{ $item->waktu }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">Belum ada data pembayaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </form>
    <div class="d-flex justify-content-end align-items-center mt-3 gap-2">
        <span class="small text-muted mb-0">
            Page {{ $transaksi->currentPage() }} of {{ $transaksi->lastPage() }}
        </span>
        @if ($transaksi->onFirstPage())
            <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">‹</span>
        @else
            <a href="{{ $transaksi->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">‹</a>
        @endif
        @if ($transaksi->hasMorePages())
            <a href="{{ $transaksi->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">›</a>
        @else
            <span class="btn btn-sm btn-light disabled" style="pointer-events: none;">›</span>
        @endif
    </div>
</div>

<script>
    document.getElementById('select-all').onclick = function() {
        let checkboxes = document.querySelectorAll('input[name="ids[]"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    }
</script>
@endsection
