<!DOCTYPE html>
<html>
<head>
    <title>Edit Venue</title>
</head>
<body>
    <h1>Edit Venue</h1>

    <form action="{{ route('venue.update', $venue) }}" method="POST">
        @csrf
        @method('PUT')

        <label>Nama Venue:</label><br>
        <input type="text" name="name" value="{{ $venue->name }}" required><br><br>

        <button type="submit">Update</button>
    </form>

    <a href="{{ route('venue.index') }}">Kembali</a>
</body>
</html>
