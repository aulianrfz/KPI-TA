<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    @include('layouts.navbar')

    <style>
        body {
            overflow-x: hidden;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            width: 250px;
            z-index: 1000;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex flex-column flex-md-row">
        <div class="col-md-2 d-none d-md-block bg-light border-end" style="min-height: 100vh;">
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li><a href="/dashboardadmin" class="nav-link {{ request()->is('dashboardadmin') ? 'active' : 'text-dark' }}"><i class="bi bi-house-door"></i> Home</a></li>
                <li><a href="#" class="nav-link text-dark"><i class="bi bi-person-plus"></i> Pendaftaran</a></li>
                <li><a href="/admin/transaksi" class="nav-link {{ request()->is('admin/transaksi') ? 'active' : 'text-dark' }}"><i class="bi bi-receipt"></i> Transaksi</a></li>
                <li><a href="#" class="nav-link text-dark"><i class="bi bi-clipboard-check"></i> Daftar Hadir</a></li>
                <li><a href="#" class="nav-link text-dark"><i class="bi bi-bar-chart-line"></i> Laporan Penjualan</a></li>
                <li><a href="{{ url('/kategori') }}" class="nav-link {{ request()->is('kategori') ? 'active' : 'text-dark' }}"><i class="bi bi-tags"></i> Kategori</a></li>
                <li><a href="#" class="nav-link text-dark"><i class="bi bi-calendar-event"></i> Penjadwalan</a></li>
                <li><a href="#" class="nav-link text-dark"><i class="bi bi-ui-checks"></i> Kuisioner</a></li>
                <li><a href="#" class="nav-link text-dark"><i class="bi bi-people"></i> Juri</a></li>
                <li><a href="#" class="nav-link text-dark"><i class="bi bi-geo-alt"></i> Venue</a></li>
                <li><a href="#" class="nav-link text-dark"><i class="bi bi-award"></i> Sertifikat</a></li>
                <li><a href="#" class="nav-link text-dark"><i class="bi bi-upload"></i> Pengajuan</a></li>
            </ul>
        </div>
        @yield('content')
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString();
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
