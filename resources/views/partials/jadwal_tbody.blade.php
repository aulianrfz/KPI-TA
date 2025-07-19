<tbody>
@foreach($jadwals as $index => $jadwal)
    <tr>
        <td>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}.</td>
        <td>{{ $jadwal->nama_jadwal }}</td>
        <td>{{ $jadwal->tahun }}</td>
        <td>{{ $jadwal->version }}</td>
        <td>
            {{ \Carbon\Carbon::parse($jadwal->created_at)->format('d/m/Y') }}<br>
            <small class="text-muted">{{ \Carbon\Carbon::parse($jadwal->created_at)->format('H:i') }}</small>
        </td>
        <td id="status-{{ $jadwal->id }}">
            {{ $jadwal->status ?? 'Belum Dijadwalkan' }}<br>
            @if($jadwal->status === 'Menunggu')
                <small class="text-muted">Progress: {{ $jadwal->progress }}%</small>
            @endif
        </td>
        <td id="action-{{ $jadwal->id }}">
            @if($jadwal->status === 'Menunggu')
                <button class="btn btn-sm btn-secondary" disabled>Detail</button>
            @elseif($jadwal->status === 'Gagal')
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#gagalModal"
                    data-alasan="{{ $jadwal->alasan_gagal }}">Lihat Alasan</button>
            @else
                <a href="{{ route('jadwal.detail', $jadwal->id) }}" class="btn btn-sm btn-info">Lihat</a>
            @endif
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                data-bs-target="#deleteModal"
                data-action="{{ route('jadwal.destroyJadwal', $jadwal->id) }}">
                Hapus
            </button>
        </td>
    </tr>
@endforeach
</tbody>
