@extends('layouts.apk')

@section('content')
<div class="container">
    <h2>Buat Jadwal Baru - Step 1</h2>

    <form action="{{ route('jadwal.create.step2') }}" method="POST" id="jadwalForm">
        @csrf

        <div class="form-group">
            <label for="nama_jadwal">Nama Jadwal</label>
            <input type="text" name="nama_jadwal" id="nama_jadwal" class="form-control" required>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="tanggal_awal">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" required>
            </div>

            <div class="form-group col-md-6">
                <label for="tanggal_akhir">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control" required>
            </div>
        </div>

        <button type="button" class="btn btn-info mb-3" onclick="generateTanggal()">Generate Tanggal</button>

        <div id="tanggalContainer"></div>

        <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
    </form>

</div>

<script>
    function generateTanggal() {
        const startDate = document.getElementById("tanggal_awal").value;
        const endDate = document.getElementById("tanggal_akhir").value;
        const container = document.getElementById("tanggalContainer");
        container.innerHTML = "";

        if (!startDate || !endDate || startDate > endDate) {
            alert("Tanggal tidak valid.");
            return;
        }

        const start = new Date(startDate);
        const end = new Date(endDate);

        for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            const tanggalStr = d.toISOString().split('T')[0];

            container.innerHTML += `
            <div class="card p-3 mb-2">
                <strong>${tanggalStr}</strong>
                <input type="hidden" name="tanggal[]" value="${tanggalStr}">

                <div class="form-group">
                    <label>Waktu Mulai</label>
                    <input type="time" name="waktu_mulai[]" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Waktu Selesai</label>
                    <input type="time" name="waktu_selesai[]" class="form-control" required>
                </div>
            </div>
        `;
        }
    }
</script>

@endsection