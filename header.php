<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3rd Party Data Request</title>
    <link rel="shortcut icon" href="assets/images/kralogol.png" />
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Font Awesome for icons (profile) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body, html {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background-color: #f8f9fa;
        }
        .container {
            flex: 1;
        }
        .footer {
            background-color: #151515;
            padding: 1rem;
            text-align: center;
            width: 100%;
            position: relative;
            bottom: 0;
            color: white;
        }
        .navbar {
            background-color: #ff0000; /* Red header */
            padding: 0.5rem 1rem;
        }
        .navbar-brand {
            margin: 0 auto; /* Centers the title */
            font-weight: bold;
            font-size: 1.5rem;
        }
        .logo-img {
            height: 40px; /* Adjust logo size */
        }
        .profile-dropdown {
            cursor: pointer;
        }
        .dropdown-menu {
            left: auto !important;
            right: 0 !important;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <!-- Logo (left) -->
            <a class="d-flex align-items-center" href="#">
                <img src="assets/images/kralogol.png" alt="Organization Logo" class="logo-img">
            </a>

            <!-- Title (centered) -->
            <span class="navbar-brand">3rd-party Data Request</span>

            <!-- Profile Dropdown (right) -->
            <div class="dropdown">
                <a class="profile-dropdown d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-2" style="font-size: 1.5rem;"></i>
                    <span id="username">User</span> <!-- Dynamically replace with PHP/JS -->
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Bootstrap 5 JS Bundle (required for dropdowns) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>