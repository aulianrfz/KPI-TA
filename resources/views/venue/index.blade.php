<!DOCTYPE html>
<html>
<head>
    <title>Daftar Venue</title>
</head>
<body>
    <h1>Daftar Venue</h1>

    <a href="{{ route('venue.create') }}">Tambah Venue</a>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Nama Venue</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venues as $venue)
                <tr>
                    <td>{{ $venue->name }}</td>
                    <td>
                        <a href="{{ route('venue.edit', $venue) }}">Edit</a>
                        <form action="{{ route('venue.destroy', $venue) }}" method="POST" style="display:inline;">
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
