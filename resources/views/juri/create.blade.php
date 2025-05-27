<!-- resources/views/juri/create.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Juri</title>
</head>
<body>
    <h1>Tambah Juri</h1>

    <form action="{{ route('juri.store') }}" method="POST">
        @csrf
        <label>Nama:</label><br>
        <input type="text" name="nama" required><br><br>

        <label>Jabatan:</label><br>
        <input type="text" name="jabatan" required><br><br>

        <label>Sub Kategori:</label><br>
        <select name="mata_lomba_id" required>
            <option value="">-- Pilih Sub Kategori --</option>
            @foreach($subKategoris as $sub)
                <option value="{{ $sub->id }}">{{ $sub->nama_lomba }}</option>
            @endforeach
        </select><br><br>


        <button type="submit">Simpan</button>
    </form>

    <a href="{{ route('juri.index') }}">Kembali</a>
</body>
</html>
