<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>QR Code Pendaftaran</title>
    <style>
    </style>
</head>
<body>
    <div class="container">
        <h2>Halo {{ $nama }}</h2>
        <p>Terima kasih telah mendaftar.</p>
        <p><strong>Nama Lomba:</strong> {{ $nama_lomba }}</p>
        <p><strong>Kategori:</strong> {{ $kategori }}</p>

        <div class="qr-note">
            Selamat! Anda telah berhasil terdaftar. QR Code Anda telah dikirim di email ini. Silakan unduh dan tunjukkan saat registrasi acara.
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <img src="{{ $message->embed($qr_image_path) }}" alt="QR Code" />
        </div>

        <p>Terima kasih atas partisipasi Anda.</p>
        <p>Salam,<br>Panitia Acara</p>

        <div class="footer">
            &copy; {{ date('Y') }} {{ $nama_event ?? 'Kompetisi Pariwisata Indonesia' }}. Semua hak dilindungi.
        </div>
    </div>
</body>
</html>
