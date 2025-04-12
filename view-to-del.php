 <?php
 $user= [];
 $user['email'] = 'john@example.com'; // Assign value to 'email'
// require_once 'auth.php';

// try {
//     // Authenticate user (handles redirects internally)
//     $userId = authenticate();
    
//     // Fetch user data with security checks
//     require_once 'config.php';
//     $stmt = $conn->prepare("SELECT email, is_active FROM ext_users WHERE id = ?");
//     $stmt->bind_param("i", $userId);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     $user = $result->fetch_assoc();
    
//     if (!$user) {
//         throw new Exception('User not found');
//     }
    
//     if (!$user['is_active']) {
//         throw new Exception('Account is inactive');
//     }
    
//     // Get token from session or headers
//     $token = isset($_SESSION['authToken']) ? $_SESSION['authToken'] : 
//              (isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '');
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .dashboard-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .user-info {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        #logout {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 16px;
        }
        #logout:hover {
            background: #c82333;
        }
        .loading {
            display: none;
            margin-left: 10px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top: 3px solid white;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
            vertical-align: middle;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="user-info">
            <h1>Welcome, <?php echo htmlspecialchars($user['email']); ?></h1>
            <p>You are successfully logged in to your account.</p>
        </div>
        
        <button id="logout">
            Logout
            <span class="loading" id="logoutSpinner"></span>
        </button>
    </div>

    <script>
        // Store token in localStorage if it came from session
        const token = '<?php echo $token; ?>';
        if (token && !localStorage.getItem('authToken')) {
            localStorage.setItem('authToken', token);
        }

        // Enhanced logout functionality
        document.getElementById('logout').addEventListener('click', async function() {
            const logoutBtn = this;
            const spinner = document.getElementById('logoutSpinner');
            
            try {
                // Show loading state
                logoutBtn.disabled = true;
                spinner.style.display = 'inline-block';
                
                // Clear client-side token immediately
                localStorage.removeItem('authToken');
                
                // Call server-side logout
                const response = await fetch('api/logout.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    throw new Error('Logout failed');
                }
                
                // Redirect to login page
                window.location.href = 'login.php';
            } catch (error) {
                console.error('Logout error:', error);
                // Redirect to login page even if logout failed
                window.location.href = 'login.php';
            } finally {
                logoutBtn.disabled = false;
                spinner.style.display = 'none';
            }
        });

        // Token validation on page load
        window.addEventListener('load', async () => {
            if (!localStorage.getItem('authToken')) {
                // Use session-based redirect if needed
                window.location.href = 'login.php';
                return;
            }
            
            try {
                const response = await fetch('api/validate-token.php', {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('authToken')
                    }
                });
                
                if (!response.ok) {
                    localStorage.removeItem('authToken');
                    window.location.href = 'login.php';
                }
            } catch (error) {
                console.error('Token validation error:', error);
                window.location.href = 'login.php';
            }
        });
    </script>
</body>
</html>
<?php
// } catch (Exception $e) {
//     // Clear any existing tokens on error
//     if (isset($_SESSION['authToken'])) {
//         unset($_SESSION['authToken']);
//     }
    
//     http_response_code(401);
    
//     // For AJAX requests, return JSON
//     if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
//         header('Content-Type: application/json');
//         echo json_encode([
//             'success'=> false, 
//             'message'=> 'Authentication required',
//             'redirect'=> 'login.php'
//         ]);
//     } else {
//         // For regular page loads, redirect to login without error details in URL
//         $_SESSION['auth_error'] = $e->getMessage();
//         header('Location: login.php');
//     }
//     exit;
// }