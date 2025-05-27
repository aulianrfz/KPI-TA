<!DOCTYPE html>
<html>
<head>
    <title>Tambah Venue</title>
</head>
<body>
    <h1>Tambah Venue</h1>

    <form action="{{ route('venue.store') }}" method="POST">
        @csrf
        <label>Nama Venue:</label><br>
        <input type="text" name="name" required><br><br>

        <button type="submit">Simpan</button>
    </form>

    <a href="{{ route('venue.index') }}">Kembali</a>
</body>
</html>
