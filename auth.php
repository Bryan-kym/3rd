<?php
function authenticate() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check for token in Authorization header
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    
    // Fallback to session token if not in header
    if (empty($token) && isset($_SESSION['authToken'])) {
        $token = $_SESSION['authToken'];
    }
    
    if (empty($token)) {
        // Store requested URI and auth message in session
        $_SESSION['post_login_redirect'] = $_SERVER['REQUEST_URI'];
        $_SESSION['auth_message'] = 'Please login to continue';
        $_SESSION['auth_message_type'] = 'info';
        
        // For AJAX requests
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            http_response_code(401);
            echo json_encode([
                'success' => false, 
                'message' => 'Unauthorized',
                'redirect' => 'login.html'
            ]);
            exit;
        }
        // Redirect to login without URL parameters
        header('Location: login.html');
        exit;
    }
    
    include 'config.php';
    try {
        // Check token in database
        $stmt = $conn->prepare("SELECT user_id FROM ext_sessions WHERE token = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $session = $result->fetch_assoc();
        
        if (!$session) {
            throw new Exception('Your session has expired. Please login again.');
        }
        
        // Update last accessed time
        $updateStmt = $conn->prepare("UPDATE ext_sessions SET last_accessed = NOW() WHERE token = ?");
        $updateStmt->bind_param("s", $token);
        $updateStmt->execute();
        
        return $session['user_id'];
    } catch (Exception $e) {
        // Clear invalid token
        unset($_SESSION['authToken']);
        
        // Store redirect and message in session
        $_SESSION['post_login_redirect'] = $_SERVER['REQUEST_URI'];
        $_SESSION['auth_message'] = $e->getMessage();
        $_SESSION['auth_message_type'] = 'error';
        
        // For AJAX requests
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            http_response_code(401);
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage(),
                'redirect' => 'login.html'
            ]);
            exit;
        }
        // Redirect to login without URL parameters
        header('Location: login.html');
        exit;
    }
}

/**
 * Gets and clears the authentication message from session
 */
function getAuthMessage() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $message = $_SESSION['auth_message'] ?? null;
    $type = $_SESSION['auth_message_type'] ?? 'info';
    
    // Clear the message after retrieving
    unset($_SESSION['auth_message']);
    unset($_SESSION['auth_message_type']);
    
    return $message ? ['text' => $message, 'type' => $type] : null;
}

/**
 * Gets the post-login redirect URL with validation
 */
function getPostLoginRedirect() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $redirect = $_SESSION['post_login_redirect'] ?? 'dashboard.php';
    unset($_SESSION['post_login_redirect']);
    
    // Validate redirect URL
    $allowedRedirects = [
        '/dashboard.php',
        '/profile.php',
        '/settings.php'
        // Add other allowed paths
    ];
    
    // Ensure the redirect is to the same domain
    $parsed = parse_url($redirect);
    if (isset($parsed['host']) && $parsed['host'] !== $_SERVER['HTTP_HOST']) {
        return 'dashboard.php';
    }
    
    // Check against whitelist
    return in_array($parsed['path'] ?? $redirect, $allowedRedirects) ? $redirect : 'dashboard.php';
}