<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

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
        }

        .sidebar-logo img {
            max-height: 50px;
            width: auto;
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

        body.sidebar-mini .sidebar {
            width: 80px;
        }

        body.sidebar-mini .sidebar .sidebar-logo img {
            max-height: 45px;
            max-width: 50px;
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
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .content-body {
            padding: 2rem; /* Disesuaikan agar tidak ada padding ganda dari main-content jika tidak perlu */
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

        .edit-container {
        max-width: 600px;
        margin: 40px auto;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #ccc;
        background-color: #fafafa;
        }
        .edit-container h3 {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .edit-container a {
            font-size: 14px;
            text-decoration: none;
            color: #007bff;
        }
        .edit-container a:hover {
            text-decoration: underline;
        }
        .edit-container p {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-size: 14px;
            display: block;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 6px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
        }
        .btn {
            font-size: 14px;
            padding: 5px 12px;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #007bff;
            color: #fff;
            border: none;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
            border: none;
        }
        .btn-primary:hover,
        .btn-secondary:hover {
            opacity: 0.9;
        }
        .small-img {
            margin: 5px 0;
            max-width: 100px;
            display: block;
        }
        .checkbox-label {
            font-size: 14px;
            display: flex;
            align-items: center;
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
                <li class="nav-item mb-2"><a href="/admin/dashboard"
                        class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}"><i
                            class="bi bi-house-door-fill"></i><span>Home</span></a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link {{ request()->is('pendaftaran*') ? 'active' : '' }}"><i class="bi bi-file-earmark-person-fill"></i><span>Pendaftaran</span></a></li>
                <li class="nav-item mb-2"><a href="/admin/transaksi/index"
                        class="nav-link {{ request()->is('admin/transaksi/index*') ? 'active' : '' }}"><i
                            class="bi bi-receipt"></i><span>Transaksi</span></a></li>
                <li class="nav-item mb-2"><a href="/kehadiran/event"
                        class="nav-link {{ request()->is('kehadiran/event*') ? 'active' : '' }}"><i
                            class="bi bi-clipboard-check-fill"></i><span>Daftar Hadir</span></a></li>
                <li class="nav-item mb-2"><a href="/laporan-penjualan/pilih" class="nav-link {{ request()->is('laporan-penjualan/pilih*') ? 'active' : '' }}"><i class="bi bi-bar-chart-line-fill"></i><span>Laporan Penjualan</span></a></li>
                <li class="nav-item mb-2"><a href="{{ url('/listcrud') }}"
                        class="nav-link {{ request()->is('listcrud*') ? 'active' : '' }}"><i
                            class="bi bi-tags-fill"></i><span>CRUD</span></a></li>
                <li class="nav-item mb-2">
                    <a href="{{ route('jadwal.event') }}"
                        class="nav-link {{ request()->routeIs('jadwal.event') || request()->routeIs('jadwal.create') || request()->routeIs('jadwal.edit') ? 'active' : '' }}">
                        <i class="bi bi-calendar-event-fill"></i>
                        <span>Penjadwalan</span>
                    </a>
                </li>
                <li class="nav-item mb-2"><a href="/kuisioner/event" class="nav-link {{ request()->is('kuisioner/event*') ? 'active' : '' }}"><i class="bi bi-ui-checks-grid"></i><span>Kuisioner</span></a></li>
                <li class="nav-item mb-2">
                    <a href="{{ route('juri.index') }}"
                        class="nav-link {{ request()->routeIs('juri.index') || request()->routeIs('juri.create') || request()->routeIs('juri.edit') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> <span>Juri</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="{{ route('venue.index') }}"
                        class="nav-link {{ request()->routeIs('venue.index') || request()->routeIs('venue.create') || request()->routeIs('venue.edit') ? 'active' : '' }}">
                        <i class="bi bi-geo-alt-fill"></i> <span>Venue</span>
                    </a>
                </li>
                <li class="nav-item mb-2"><a href="/sertif" class="nav-link {{ request()->is('sertif*') ? 'active' : '' }}"><i class="bi bi-award-fill"></i><span>Sertifikat</span></a></li>
                <li class="nav-item mb-2"><a href="/pengajuan/admin" class="nav-link {{ request()->is('pengajuan*') ? 'active' : '' }}"><i class="bi bi-box-arrow-in-up"></i><span>Pengajuan</span></a></li>
                @php $role = auth()->user()->role; @endphp
                {{-- menu approve admin (hanya superadmin) --}}
                @if($role === 'superadmin')
                    <li class="nav-item mb-2">
                        <a href="{{ route('superadmin.admin.manage') }}"
                            class="nav-link {{ request()->routeIs('superadmin.admin.manage') ? 'active' : '' }}">
                            <i class="bi bi-person-gear-fill"></i><span>Manage Admin</span>
                        </a>
                    </li>
                @endif
            </ul>
        </aside>

    <main class="main-content">
        <nav class="top-navbar">
            <div class="navbar-left">
                <i class="bi bi-list" id="toggle-sidebar-btn"></i>

                @php
                    use App\Models\Event;
                    use Illuminate\Support\Str;

                    $headerTitle = $title ?? 'Dashboard';

                    function isGeneralRoute(): bool {
                        return request()->is('admin/dashboard') ||
                            request()->is('kehadiran/event*') ||
                            request()->is('pendaftaran/event*') ||
                            request()->is('admin/transaksi/index*') ||
                            request()->is('kuisioner*') ||
                            request()->is('listcrud*') ||
                            request()->is('listevent*') ||
                            request()->is('kategori*') ||
                            request()->is('mataLomba*') ||
                            request()->is('provinsi*') ||
                            request()->is('institusi*') ||
                            request()->is('jurusan*') ||
                            request()->routeIs('jadwal.*') ||
                            request()->routeIs('juri.*') ||
                            request()->routeIs('venue.*') ||
                            request()->is('sertifikat*') ||
                            request()->is('pengajuan*') ||
                            request()->is('laporan/penjualan*');
                    }

                    $selectedEventId = session('selected_event');
                    $event = $selectedEventId && !isGeneralRoute() ? Event::find($selectedEventId) : null;
                    $eventName = $event && $event->nama_event !== '15' ? $event->nama_event : null;


                    if ($eventName) {
                        if (request()->routeIs('dashboard.by-event')) {
                            $headerTitle = 'KPI: ' . $eventName;
                        } elseif (request()->is('kehadiran/event*')) {
                            $headerTitle = 'Kehadiran: ' . $eventName;
                        } elseif (request()->is('pendaftaran*')) {
                            $headerTitle = 'Pendaftaran: ' . $eventName;
                        } elseif (request()->is('admin/transaksi*')) {
                            $headerTitle = 'Transaksi: ' . $eventName;
                        } else {
                            $headerTitle = $eventName;
                        }

                    } else {
                        $routeName = request()->route()->getName();
                        $path = request()->path();

                        if ($path === 'admin/dashboard') {
                            $headerTitle = 'Home';
                        } elseif (str_starts_with($routeName, 'dashboard.by-event')) {
                            $headerTitle = 'Event KPI';
                        } elseif (str_starts_with($path, 'pendaftaran')) {
                            $headerTitle = 'Pendaftaran';
                        } elseif (str_starts_with($path, 'admin/transaksi')) {
                            $headerTitle = 'Transaksi';
                        } elseif (str_starts_with($path, 'kehadiran/event')) {
                            $headerTitle = 'Daftar Hadir';
                        } elseif (str_starts_with($path, 'laporan/penjualan')) {
                            $headerTitle = 'Laporan Penjualan';
                        } elseif (str_starts_with($path, 'listcrud')) {
                            $headerTitle = 'CRUD';
                        } elseif (str_starts_with($path, 'listevent')) {
                            $headerTitle = 'Event';
                        }elseif (str_starts_with($path, 'kategori')) {
                            $headerTitle = 'Kategori';
                        }elseif (str_starts_with($path, 'mataLomba')) {
                            $headerTitle = 'Mata Lomba';
                        }elseif (str_starts_with($path, 'provinsi')) {
                            $headerTitle = 'Provinsi';
                        }elseif (str_starts_with($path, 'institusi')) {
                            $headerTitle = 'Institusi';
                        }elseif (str_starts_with($path, 'jurusan')) {
                            $headerTitle = 'Jurusan';
                        }
                        elseif (Str::startsWith($routeName, 'jadwal.')) {
                            $headerTitle = 'Penjadwalan';
                        } elseif (str_starts_with($path, 'kuisioner')) {
                            $headerTitle = 'Kuisioner';
                        } elseif (Str::startsWith($routeName, 'juri.')) {
                            $headerTitle = 'Juri';
                        } elseif (Str::startsWith($routeName, 'venue.')) {
                            $headerTitle = 'Venue';
                        } elseif (str_starts_with($path, 'sertifikat')) {
                            $headerTitle = 'Sertifikat';
                        } elseif (str_starts_with($path, 'pengajuan')) {
                            $headerTitle = 'Pengajuan';
                        }
                    }
                @endphp

                <div class="navbar-title">{{ $headerTitle }}</div>
            </div>

            <div class="navbar-profile">
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link p-0" title="Logout" style="line-height: 1;">
                        <i class="bi bi-power power-icon"></i>
                    </button>
                </form>
                <a href="/profile" class="d-flex align-items-center">
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->first_name ?? 'User' }}+{{ Auth::user()->last_name ?? '' }}&background=0367A6&color=fff"
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