<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGO APP</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">


    <style>
        a {
            text-decoration: none;
            color: inherit; 
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
        }

        footer {
            background: linear-gradient(90deg, #007BFF, #0056b3);
            color: white;
        }

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

        html, body {
            height: 100%;
        }


        #app {
            flex: 1;
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
<body>

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
