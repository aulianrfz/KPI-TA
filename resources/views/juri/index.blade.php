<!-- resources/views/juri/index.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Juri</title>
</head>
<body>
    <h1>Daftar Juri</h1>

    <a href="{{ route('juri.create') }}">Tambah Juri</a>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Aksi</th>
                <th>Sub Kategori</th>
            </tr>
        </thead>
        <tbody>
            @foreach($juris as $juri)
                <tr>
                    <td>{{ $juri->nama }}</td>
                    <td>{{ $juri->jabatan }}</td>
                    <td>{{ $juri->mataLomba->nama_lomba ?? 'Tidak Diketahui' }}</td>
                    <td>
                        <a href="{{ route('juri.edit', $juri) }}">Edit</a>
                        <form action="{{ route('juri.destroy', $juri) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Yakin ingin hapus?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
