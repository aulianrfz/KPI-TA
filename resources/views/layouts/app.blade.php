<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGO APP</title>

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ======================================================= */
        /* BAGIAN CSS YANG DIPERBAIKI */
        /* ======================================================= */


        


        a {
            text-decoration: none;
            color: inherit; 
        }

        /* PERBAIKAN: Aturan body sekarang bersih dari position dan padding-bottom */
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
        }
        /* ATURAN LAMA YANG SEHARUSNYA DIHAPUS:
        body {
            ...
            position: relative;
            min-height: 100vh;
            padding-bottom: 500px;
        }
        */

        /* PERBAIKAN: Aturan footer sekarang bersih dari position dan bottom */
        footer {
            background: linear-gradient(90deg, #007BFF, #0056b3);
            color: white;
            width: 100%;
        }
        /* ATURAN LAMA YANG SEHARUSNYA DIHAPUS:
        footer {
            ...
            position: relative;
            bottom: -170;
        }
        */

        footer a {
            color: white;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        .footer-links p {
            margin: 0.25rem 0;
        }
        
        /* ======================================================= */
        /* SEMUA KODE CSS ANDA YANG LAIN DIBAWAH INI (TIDAK ADA YANG DIHAPUS) */
        /* ======================================================= */

        .navbar .input-group input {
            border-radius: 0 5px 5px 0;
        }

        .navbar .input-group .input-group-text {
            border-radius: 5px 0 0 5px;
        }

        .upcoming-events a {
            text-decoration: none; 
            color: inherit; 
        }

        /* ATURAN LAMA YANG SEHARUSNYA DIHAPUS:
        html, body {
            height: 100%;
        }
        */

        /* ATURAN LAMA YANG SEHARUSNYA DIHAPUS:
        #app {
            flex: 1;
        }
        */

        .auth-wrapper {
            max-width: 400px;
            margin: 60px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.15);
        }

        .auth-header {
            background-color: #0367A6;
            padding: 20px;
            text-align: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        .auth-tabs {
            display: flex;
            justify-content: center;
            background-color: #f1f1f1;
            border-bottom: 2px solid #0367A6;
        }

        .auth-tabs a {
            flex: 1;
            text-align: center;
            padding: 12px 0;
            font-weight: bold;
            color: #0367A6;
            border-bottom: 2px solid transparent;
        }

        .auth-tabs a.active {
            background-color: #fff;
            border-bottom: 2px solid #0367A6;
            color: #0367A6;
        }

        .auth-form {
            padding: 25px;
        }

        .auth-form input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        .auth-form button {
            background-color: #0367A6;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
        }

        .auth-form button:hover {
            background-color: #3F9BBF;
        }

        .auth-form p {
            margin-top: 15px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .navbar .input-group {
                width: 100% !important;
            }

            .navbar .d-flex {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>

<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    AOS.init();
</script>

@stack('scripts')

<body class="d-flex flex-column min-vh-100">

    @include('layouts.navbar')

    <main class="flex-grow-1">
        @yield('content')
    </main>

    @include('layouts.footer')

    @yield('scripts')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        AOS.init();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: @json(session('success')),
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan!',
                    text: @json(session('error')),
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Coba Lagi'
                });
            @endif
        });
    </script>
</body>
</html>