<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }}</title>
   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->

    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @include('layouts.navbar')

    <style>
        body {
            overflow-x: hidden;
        }
        .sidebar {
            background-color: #f8f9fa;
            padding: 1rem;
            height: 100vh;
            width: 50px; /* <--- diperkecil dari 250px */
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
            position: fixed;
        }

         .custom-card {
        border: none;
        border-radius: 1rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }

        .custom-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .bg-gradient-kategori {
            background: linear-gradient(135deg, #a2c5f5, #5a8dee);
        }

        .bg-gradient-provinsi {
            background: linear-gradient(135deg, #a8e6cf, #56cc9d);
        }

        .bg-gradient-institusi {
            background: linear-gradient(135deg, #ffe0b2, #f5a623);
        }

        .bg-gradient-event {
            background: linear-gradient(135deg, #ffcccc, #ff6f61);
        }
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }

        .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .nav-link.text-dark:hover {
            background-color: #e2e6ea;
            color: #000;
        }

        .nav-link.active {
            background-color: #0d6efd;
            color: #fff !important;
        }

        .nav-link.active i {
            color: #fff;
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
        .pagination .page-link {
            font-size: 0.75rem; /* Lebih kecil */
            padding: 0.3rem 0.6rem; /* Lebih kompak */
        }

        .pagination .page-item.disabled .page-link,
        .pagination .page-item.active .page-link {
            font-weight: 500;
        }

        @media (max-width: 576px) {
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

    </style>
</head>
<body>
    <div class="d-flex flex-column flex-md-row">
        <div class="d-none d-md-block bg-light border-end" style="width: 250px; min-height: 100vh;">
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                    <li class="mb-2"><a href="/dashboardadmin" class="nav-link {{ request()->is('dashboardadmin') ? 'active' : 'text-dark' }}"><i class="bi bi-house-door"></i> Home</a></li>
                    <li class="mb-2"><a href="#" class="nav-link text-dark"><i class="bi bi-person-plus"></i> Pendaftaran</a></li>
                    <li class="mb-2"><a href="/admin/transaksi" class="nav-link {{ request()->is('admin/transaksi') ? 'active' : 'text-dark' }}"><i class="bi bi-receipt"></i> Transaksi</a></li>
                    <li class="mb-2"><a href="/kehadiran" class="nav-link {{ request()->is('kehadiran') ? 'active' : 'text-dark' }}"><i class="bi bi-clipboard-check"></i> Daftar Hadir</a></li>
                    <li class="mb-2"><a href="#" class="nav-link text-dark"><i class="bi bi-bar-chart-line"></i> Laporan Penjualan</a></li>
                    <li class="mb-2"><a href="{{ url('/listcrud') }}" class="nav-link {{ request()->is('listcrud') ? 'active' : 'text-dark' }}"><i class="bi bi-tags"></i> LIst CRUD</a></li>
                    <li class="mb-2"><a href="#" class="nav-link text-dark"><i class="bi bi-calendar-event"></i> Penjadwalan</a></li>
                    <li class="mb-2"><a href="#" class="nav-link text-dark"><i class="bi bi-ui-checks"></i> Kuisioner</a></li>
                    <li class="mb-2"><a href="#" class="nav-link text-dark"><i class="bi bi-people"></i> Juri</a></li>
                    <li class="mb-2"><a href="#" class="nav-link text-dark"><i class="bi bi-geo-alt"></i> Venue</a></li>
                    <li class="mb-2"><a href="#" class="nav-link text-dark"><i class="bi bi-award"></i> Sertifikat</a></li>
                    <li class="mb-2"><a href="#" class="nav-link text-dark"><i class="bi bi-upload"></i> Pengajuan</a></li>
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
