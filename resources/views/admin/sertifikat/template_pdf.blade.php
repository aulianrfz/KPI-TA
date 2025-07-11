<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }
        .background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
         .nama {
            position: absolute;
            top: {{ $template->posisi_y }}px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 32px;
            font-weight: bold;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <img class="background" src="{{ public_path('storage/' . $template->nama_file) }}" alt="template">
    <div class="nama">{{ $nama_peserta  }}</div>
</body>
</html>
