<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>QR Code Pendaftaran</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            color: #333;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .title {
            color: #006699;
            margin-bottom: 20px;
        }
        .qr-image {
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            font-size: 12px;
            color: #777;
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="title">QR Code Pendaftaran Anda</h2>

        <p>Halo <strong>{{ $nama }}</strong>,</p>

        <p>Selamat! Anda telah berhasil terdaftar pada acara <strong>Kompetisi Pariwisata Indonesia</strong> untuk lomba <strong>{{ $nama_lomba }}</strong>.</p>

        <p>Silakan simpan dan tunjukkan file QR Code berikut saat proses registrasi di lokasi acara</p>

        <p>Terima kasih atas partisipasi Anda.</p>

        <div class="footer">
            &copy; {{ date('Y') }} Kompetisi Pariwisata Indonesia. Semua hak dilindungi.
        </div>
    </div>
</body>
</html>
