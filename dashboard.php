<?php
include 'header.php'; 
require_once 'auth.php';
require_once 'config.php';

try {
    // Authenticate user
    $userId = authenticate();
    
    // Fetch user data
    $stmt = $conn->prepare("SELECT email, is_active FROM ext_users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    if (!$user['is_active']) {
        throw new Exception('Account is inactive');
    }
    
    // Get token from session or headers
    $token = isset($_SESSION['authToken']) ? $_SESSION['authToken'] : 
             (isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '');
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
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            height: 100vh;
        }
        .dashboard-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
            margin: 20px;
            height: calc(100% - 40px);
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }
        .user-info {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        #logout, #createRequest {
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 16px;
        }
        #logout {
            background: #dc3545;
        }
        #logout:hover {
            background: #c82333;
        }
        #createRequest {
            background: #007bff;
        }
        #createRequest:hover {
            background: #0069d9;
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
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background: #28a745;
            color: white;
            border-radius: 5px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
            transform: translateX(200%);
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        .notification.show {
            transform: translateX(0);
        }
        .notification.error {
            background: #dc3545;
        }
        .notification.info {
            background: #17a2b8;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Notification element -->
    <div class="notification" id="notification"></div>

    <div class="dashboard-container">
        <div class="user-info">
            <h1>Welcome, <?php echo htmlspecialchars($user['email']); ?></h1>
            <p>You are successfully logged in to your account.</p>
        </div>
        
        <div class="action-buttons">
            <button id="createRequest">
                Create New Request
                <span class="loading" id="createRequestSpinner"></span>
            </button>
            <button id="logout">
                Logout
                <span class="loading" id="logoutSpinner"></span>
            </button>
        </div>
    </div>

    <script>
        // Store token in localStorage if it came from session
        const token = '<?php echo $token; ?>';
        if (token && !localStorage.getItem('authToken')) {
            localStorage.setItem('authToken', token);
        }

        // Show notification function
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = 'notification ' + type;
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Check for session messages
        function checkForSessionMessages() {
            fetch('api/check-messages.php')
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        showNotification(data.message, data.type);
                    }
                })
                .catch(error => console.error('Error checking messages:', error));
        }

        // Create New Request button functionality
        document.getElementById('createRequest').addEventListener('click', function() {
            window.location.href = 'request.php';
        });

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
                
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || 'Logout failed');
                }
                
                // Show notification and redirect
                showNotification('Logout successful! Redirecting to login page...', 'success');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 1500);
            } catch (error) {
                console.error('Logout error:', error);
                showNotification(error.message, 'error');
            } finally {
                logoutBtn.disabled = false;
                spinner.style.display = 'none';
            }
        });

        // Token validation on page load
        window.addEventListener('load', async () => {
            // Check for any session messages first
            checkForSessionMessages();
            
            if (!localStorage.getItem('authToken')) {
                window.location.href = 'login.html';
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
                    window.location.href = 'login.html';
                }
            } catch (error) {
                console.error('Token validation error:', error);
                window.location.href = 'login.html';
            }
        });
    </script>
</body>
</html>
<?php
} catch (Exception $e) {
    // Clear any existing tokens on error
    if (isset($_SESSION['authToken'])) {
        unset($_SESSION['authToken']);
    }
    
    // Store error message in session
    $_SESSION['auth_message'] = $e->getMessage();
    $_SESSION['auth_message_type'] = 'error';
    
    http_response_code(401);
    
    // For AJAX requests, return JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Authentication required',
            'redirect' => 'login.html'
        ]);
    } else {
        // For regular page loads, redirect to login without error details in URL
        $_SESSION['auth_error'] = $e->getMessage();
        header('Location: login.html');
    }
}

include 'footer.php';