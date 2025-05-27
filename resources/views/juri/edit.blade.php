<!-- resources/views/juri/edit.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Edit Juri</title>
</head>
<body>
    <h1>Edit Juri</h1>

    <form action="{{ route('juri.update', $juri) }}" method="POST">
        @csrf
        @method('PUT')

        <label>Nama:</label><br>
        <input type="text" name="nama" value="{{ $juri->nama }}" required><br><br>

        <label>Jabatan:</label><br>
        <input type="text" name="jabatan" value="{{ $juri->jabatan }}" required><br><br>

        <label>Sub Kategori:</label><br>
        <select name="mata_lomba_id" required>
            <option value="">-- Pilih Sub Kategori --</option>
            @foreach($subKategoris as $sub)
                <option value="{{ $sub->id }}" {{ $juri->mata_lomba_id == $sub->id ? 'selected' : '' }}>
                    {{ $sub->nama_lomba }}
                </option>
            @endforeach
        </select><br><br>

        <button type="submit">Update</button>
    </form>

    <a href="{{ route('juri.index') }}">Kembali</a>
</body>
</html>

{{-- Error Modal --}}
@if(session('error'))
    <div id="errorModal" class="modal-overlay">
        <div class="modal-content">
            <p>{{ session('error') }}</p>
            <button onclick="closeModal()">Tutup</button>
        </div>
    </div>

    <script>
        function closeModal() {
            document.getElementById('errorModal').style.display = 'none';
        }
    </script>

    <style>
        .modal-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .modal-content {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            text-align: center;
            max-width: 400px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .modal-content button {
            margin-top: 15px;
            background-color: #e3342f;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
@endif

