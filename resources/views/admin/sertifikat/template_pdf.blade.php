<!DOCTYPE html>
<html>
<head>
    <style>
        @font-face {
            font-family: 'Pacifico';
            src: url('{{ storage_path("fonts/pacifico-regular.ttf") }}') format('truetype');
        }

        @font-face {
            font-family: 'DancingScript';
            src: url('{{ storage_path("fonts/DancingScript-Regular.ttf") }}') format('truetype');
        }

        @font-face {
            font-family: 'ComicSansMS';
            src: url('{{ storage_path("fonts/ComicSansMS.ttf") }}') format('truetype');
        }

        body {
            margin: 0;
            padding: 0;
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
            font-family: '{{ $template->font_dompdf ?? 'sans-serif' }}';
        }
    </style>
</head>
<body>
    <img class="background" src="{{ public_path('storage/' . $template->nama_file) }}" alt="template">
    <div class="nama">{{ $nama_peserta }}</div>
</body>
</html>
