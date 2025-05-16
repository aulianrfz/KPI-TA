@extends('layouts.apk')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Verifikasi Pembayaran</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row align-items-start mb-3">
        <div class="col-md-6">
            <form method="GET" action="{{ route('transaksi.index') }}" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari nama peserta / institusi" value="{{ request('search') }}">
                <button class="btn btn-primary" type="submit">Cari</button>
            </form>
        </div>

        <div class="col-md-6 text-end">
            <form method="POST" action="{{ route('admin.transaksi.bulkAction') }}" id="bulk-action-form">
                @csrf
                <button type="submit" name="action" value="approve" class="btn btn-success me-1">Setujui</button>
                <button type="submit" name="action" value="reject" class="btn btn-danger">Tolak</button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.transaksi.bulkAction') }}" id="table-form">
        @csrf
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
                    <td><input type="checkbox" name="ids[]" form="bulk-action-form" value="{{ $item->id }}"></td>
                    <td>{{ $item->peserta->nama_peserta ?? '-' }}</td>
                    <td>{{ $item->invoice->jabatan ?? '-' }}</td>
                    <td>{{ $item->peserta->institusi ?? '-' }}</td>
                    <td>{{ $item->peserta->subKategori->nama_lomba ?? '-' }}</td>
                    <td>
                        @if($item->bukti_pembayaran)
                            <a href="{{ asset('storage/' . $item->bukti_pembayaran) }}" target="_blank">Lihat File</a>
                        @else
                            Tidak ada
                        @endif
                    </td>
                    <td>
                        @if($item->status == 'Disetujui')
                            <span class="badge bg-success">Disetujui</span>
                        @elseif($item->status == 'Ditolak')
                            <span class="badge bg-danger">Ditolak</span>
                        @else
                            <span class="badge bg-secondary">Pending</span>
                        @endif
                    </td>
                    <td>{{ $item->waktu }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">Belum ada pembayaran.</td></tr>
            @endforelse
            </tbody>
        </table>
    </form>
</div>

<script>
    document.getElementById('select-all').onclick = function() {
        var checkboxes = document.querySelectorAll('input[name="ids[]"]');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    }
</script>
@endsection
