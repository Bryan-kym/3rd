<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
require_once 'session_manager.php';

// Initialize session manager
$sessionManager = new SessionManager($conn);

// Get token from session or headers
$token = $_SESSION['authToken'] ?? (isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3rd Party Data Request</title>
    <link rel="shortcut icon" href="assets/images/kralogol.png" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-red: #d9232e;
            --dark-red: #a51b24;
            --dark-black: #151515;
            --header-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --gradient-angle: 135deg;
        }

        body,
        html {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }

        .container {
            flex: 1;
        }

        /* Enhanced Header with Gradient */
        .navbar {
            background: linear-gradient(var(--gradient-angle), var(--primary-red), var(--dark-black));
            padding: 0.5rem 2rem;
            box-shadow: var(--header-shadow);
            position: relative;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .navbar-container {
            width: 100%;
            position: relative;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .navbar-brand::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -5px;
            width: 100%;
            height: 2px;
            background: white;
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover::after {
            transform: scaleX(1);
        }

        .profile-dropdown {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .profile-dropdown:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .profile-dropdown .user-icon {
            font-size: 1.6rem;
            margin-right: 0.7rem;
            color: white;
        }

        .profile-name {
            font-weight: 500;
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 0.5rem 0;
            margin-top: 10px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .dropdown-item {
            padding: 0.5rem 1.5rem;
            font-weight: 400;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }

        .dropdown-item i {
            width: 24px;
            text-align: center;
            margin-right: 10px;
            color: var(--primary-red);
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--dark-red);
        }

        .dropdown-divider {
            margin: 0.3rem 0;
            border-color: rgba(0, 0, 0, 0.05);
        }

        .logout-spinner {
            display: none;
            border: 2px solid rgba(217, 35, 46, 0.3);
            border-radius: 50%;
            border-top: 2px solid var(--primary-red);
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
            margin-left: 8px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            transform: translateX(200%);
            transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55), opacity 0.3s ease;
            z-index: 1100;
            color: white;
            display: flex;
            align-items: center;
            max-width: 400px;
            opacity: 0;
            backdrop-filter: blur(5px);
        }

        .notification.show {
            transform: translateX(0);
            opacity: 1;
        }

        .notification.success {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.9), rgba(33, 136, 56, 0.9));
            border-left: 4px solid #1e7e34;
        }

        .notification.error {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.9), rgba(200, 35, 51, 0.9));
            border-left: 4px solid #bd2130;
        }

        .notification-close {
            margin-left: 15px;
            cursor: pointer;
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
    <!-- Notification Element -->
    <div class="notification" id="notification">
        <span class="notification-icon" id="notificationIcon"></span>
        <span id="notificationMessage"></span>
        <span class="notification-close" id="notificationClose">&times;</span>
    </div>

    <!-- Enhanced Gradient Header -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid navbar-container">
            <!-- Logo (left) -->
            <a class="d-flex align-items-center" href="dashboard.php" title="Home">
                <img src="assets/images/kralogol.png" alt="Organization Logo" class="logo-img">
            </a>

            <!-- Title (centered) -->
            <div class="d-flex flex-grow-1 justify-content-center">
                <a href="#" class="navbar-brand text-decoration-none">3rd Party Data Request</a>
            </div>

            <!-- Profile Dropdown (right) -->
            <div class="dropdown ms-auto">
                <a class="profile-dropdown text-decoration-none dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle user-icon"></i>
                    <span class="profile-name" id="username"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-home me-2"></i>Home</a></li>
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" id="logoutLink">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                            <span class="logout-spinner" id="logoutSpinner"></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Bootstrap 5 JS Bundle (required for dropdowns) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>