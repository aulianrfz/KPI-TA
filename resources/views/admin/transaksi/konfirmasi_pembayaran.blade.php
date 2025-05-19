@extends('layouts.apk')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Verifikasi Pembayaran</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.transaksi.bulkAction') }}" id="bulk-action-form">
        @csrf
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group w-100">
                    <input type="text" name="search" class="form-control border" placeholder="Cari nama peserta / institusi" style="border-color: #0367A6;" value="{{ request('search') }}">
                    <span class="input-group-text" style="background-color: #0367A6; color: white;"><i class="bi bi-search"></i></span>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <button type="submit" name="action" value="approve" style="background-color: #C7F5EE; color: #00B69B;" class="btn btn-success me-1">Setujui</button>
                <button type="submit" name="action" value="reject" style="background-color: #FFD6D7; color: #FF1C20;" class="btn btn-danger">Tolak</button>
            </div>
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
                        <td>{{ $item->peserta->mataLomba->nama_lomba ?? '-' }}</td>
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
                    <tr><td colspan="8" class="text-center">Belum ada pembayaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </form>
</div>

<script>
    document.getElementById('select-all').onclick = function() {
        let checkboxes = document.querySelectorAll('input[name="ids[]"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    }
</script>
@endsection
