<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->

    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8F9FA;
            overflow-x: hidden;
        }

        .main-container {
            display: flex;
            min-height: 100vh;
        }

        /* --- Sidebar --- */
        .sidebar {
            width: 260px;
            background-color: #ffffff;
            padding: 1.5rem 1rem;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            border-right: 1px solid #dee2e6;
            transition: width 0.3s ease-in-out;
            z-index: 1000;
        }

        .sidebar-logo {
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-logo a {
            display: inline-block;
            /* Agar bisa diatur margin auto untuk centering jika diperlukan */
        }

        /* === PERUBAHAN UKURAN LOGO DI SINI === */
        .sidebar-logo img {
            max-height: 50px;
            /* Ukuran logo di sidebar normal */
            width: auto;
            /* Jaga aspek rasio */
            transition: all 0.3s ease-in-out;
        }

        .sidebar .nav-pills .nav-link {
            display: flex;
            align-items: center;
            color: #6c757d;
            font-weight: 500;
            padding: 0.8rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            border-left: 4px solid transparent;
            white-space: nowrap;
        }

        .sidebar .nav-pills .nav-link i {
            font-size: 1.2rem;
            margin-right: 15px;
            width: 20px;
            text-align: center;
            transition: margin 0.3s ease-in-out;
        }

        .sidebar .nav-pills .nav-link:not(.active):hover {
            background-color: #f1f3f5;
            color: #212529;
        }

        .sidebar .nav-pills .nav-link.active {
            background-color: transparent;
            color: #2D60FF !important;
            border-left: 4px solid #2D60FF;
            font-weight: 600;
        }

        .sidebar .nav-pills .nav-link.active i {
            color: #2D60FF;
        }

        /* -- Style untuk Sidebar Mini/Collapsed -- */
        body.sidebar-mini .sidebar {
            width: 80px;
        }

        body.sidebar-mini .sidebar .sidebar-logo img {
            max-height: 45px;
            /* Ukuran logo saat sidebar mini */
            max-width: 50px;
            /* Batasi juga lebarnya agar tidak meluber */
        }

        body.sidebar-mini .sidebar .nav-link span {
            display: none;
        }

        body.sidebar-mini .sidebar .nav-link i {
            margin-right: 0;
        }

        /* --- Main Content & Navbar --- */
        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 0;
            transition: all 0.3s ease-in-out;
            background-color: #F8F9FA;
        }

        body.sidebar-mini .main-content {
            margin-left: 80px;
            width: calc(100% - 80px);
        }

        .top-navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #ffffff;
            padding: 1rem 1.5rem;
            /* Disesuaikan agar cocok dengan card */
            border-radius: 0.75rem;
            /* Disesuaikan agar cocok dengan card */
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            /* Disesuaikan agar cocok dengan card */
        }

        .content-body {
            padding: 2rem;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .navbar-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #343a40;
        }

        /* Ukuran font title disesuaikan kembali */
        .navbar-profile {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .navbar-profile .power-icon {
            font-size: 1.5rem;
            color: #6c757d;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .navbar-profile .power-icon:hover {
            color: #dc3545;
        }

        .navbar-profile .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        #toggle-sidebar-btn {
            font-size: 1.5rem;
            cursor: pointer;
            color: #6c757d;
        }

        /* Style untuk Card Konten Putih */
        .custom-card {
            background-color: #ffffff;
            border: none;
            border-radius: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .custom-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        /* Style untuk Card Gradient */
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

        .bg-gradient-jurusan {
            background: linear-gradient(135deg,rgb(177, 189, 255), #5c6bc0);
        }

        .pagination .page-link {
            font-size: 0.75rem;
            padding: 0.3rem 0.6rem;
        }
    </style>
</head>

<body class="">

    <div class="main-container">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <a href="/dashboardadmin">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Aplikasi">
                </a>
            </div>

            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item mb-2"><a href="/dashboardadmin"
                        class="nav-link {{ request()->is('dashboardadmin') ? 'active' : '' }}"><i
                            class="bi bi-house-door-fill"></i><span>Home</span></a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link"><i
                            class="bi bi-file-earmark-person-fill"></i><span>Pendaftaran</span></a></li>
                <li class="nav-item mb-2"><a href="/admin/transaksi"
                        class="nav-link {{ request()->is('admin/transaksi') ? 'active' : '' }}"><i
                            class="bi bi-receipt"></i><span>Transaksi</span></a></li>
                <li class="nav-item mb-2"><a href="/kehadiran/event"
                        class="nav-link {{ request()->is('kehadiran/event') ? 'active' : '' }}"><i
                            class="bi bi-clipboard-check-fill"></i><span>Daftar Hadir</span></a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link"><i
                            class="bi bi-bar-chart-line-fill"></i><span>Laporan Penjualan</span></a></li>
                <li class="nav-item mb-2"><a href="{{ url('/listcrud') }}"
                        class="nav-link {{ request()->is('listcrud') ? 'active' : '' }}"><i
                            class="bi bi-tags-fill"></i><span>CRUD</span></a></li>
                <li class="nav-item mb-2">
                    <a href="{{ route('jadwal.index') }}"
                        class="nav-link {{ request()->routeIs('jadwal.index') ? 'active' : '' }}">
                        <i class="bi bi-calendar-event-fill"></i>
                        <span>Penjadwalan</span>
                    </a>
                </li>
                <li class="nav-item mb-2"><a href="#" class="nav-link"><i
                            class="bi bi-ui-checks-grid"></i><span>Kuisioner</span></a></li>
                <li class="nav-item mb-2">
                    <a href="{{ route('juri.index') }}"
                        class="nav-link {{ request()->routeIs('juri.index') ? 'active' : '' }}">
                        <i class="bi bi-calendar-event-fill"></i>
                        <span>Juri</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="{{ route('venue.index') }}"
                        class="nav-link {{ request()->routeIs('venue.index') ? 'active' : '' }}">
                        <i class="bi bi-calendar-event-fill"></i>
                        <span>Venue</span>
                    </a>
                </li>
                <li class="nav-item mb-2"><a href="#" class="nav-link"><i
                            class="bi bi-award-fill"></i><span>Sertifikat</span></a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link"><i
                            class="bi bi-box-arrow-in-up"></i><span>Pengajuan</span></a></li>
            </ul>
        </aside>

        <main class="main-content">
            <nav class="top-navbar">
                <div class="navbar-left">
                    <i class="bi bi-list" id="toggle-sidebar-btn"></i>
                    <div class="navbar-title">Kompetisi Pariwisata Indonesia</div>
                </div>
                <div class="navbar-profile">
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link p-0" title="Logout" style="line-height: 1;">
                            <i class="bi bi-power power-icon"></i>
                        </button>
                    </form>
                    <a href="/profile" class="d-flex align-items-center">
                        <img src="https://ui-avatars.com/api/?name={{ Auth::user()->first_name }}+{{ Auth::user()->last_name }}&background=0367A6&color=fff"
                            alt="Profile" class="rounded-circle" width="35" height="35">
                    </a>
                </div>
            </nav>

            <div class="content-body">
                @yield('content')
            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('toggle-sidebar-btn');
            const body = document.body;

            // Inisialisasi state sidebar saat halaman dimuat
            if (localStorage.getItem('sidebarState') === 'mini') {
                body.classList.add('sidebar-mini');
            }

            toggleBtn.addEventListener('click', function () {
                body.classList.toggle('sidebar-mini');

                if (body.classList.contains('sidebar-mini')) {
                    localStorage.setItem('sidebarState', 'mini');
                } else {
                    localStorage.setItem('sidebarState', 'full');
                }
            });
        });
    </script>
</body>

</html>