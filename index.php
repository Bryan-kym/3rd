<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is already logged in
if (isset($_SESSION['authToken'])) {
    header("Location: dashboard.php");
    exit();
}

// Store the intended redirect URL in session
$_SESSION['post_login_redirect'] = 'dashboard.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Redirecting...</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .redirect-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
        }
        .logo {
            width: 100px;
            margin-bottom: 1.5rem;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        p {
            color: #7f8c8d;
            margin-bottom: 2rem;
        }
        .loader {
            display: inline-block;
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1.5rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .login-link {
            display: inline-block;
            margin-top: 1rem;
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        .login-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="redirect-container">
        <!-- Replace with your actual logo -->
        <img src="assets/images/favicon.ico" alt="App Logo" class="logo">
        
        <h1>Kenya Revenue Authority</h1>
        <p>You're being automatically redirected to the login page</p>
        
        <div class="loader"></div>
        
        <p>If you're not redirected automatically, please click below:</p>
        <a href="login.html" class="login-link">Go to Login Page</a>
    </div>

    <!-- JavaScript for smooth redirect -->
    <script>
        // Redirect after 3 seconds
        setTimeout(function() {
            window.location.href = "login.html";
        }, 1000);
        
        // Optional: Check if user is already logged in via localStorage
        if (localStorage.getItem('authToken')) {
            window.location.href = "dashboard.php";
        }
    </script>
</body>
</html>