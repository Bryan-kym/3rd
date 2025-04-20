<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';
require_once '../session_manager.php';

// Security headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: application/json');

try {
    // Get token from all possible sources
    $token = $_SESSION['authToken'] ?? 
             (isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '');

    // Initialize session manager
    $sessionManager = new SessionManager($conn);

     // Update expires_at and destroy database session if token exists
     if (!empty($token)) {
        //destry existing session in database
        $sessionManager->destroySession($token);
        error_log("User session destroyed for token: " . substr($token, 0, 6) . "...");
    }

    // Clear all session data
    $_SESSION = array();

    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Finally, destroy the session
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    // Clear client-side tokens
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully',
        'redirect' => 'login.html?logout=success'  // Add redirect URL
    ]);
    exit;

} catch (Exception $e) {
    error_log("Logout failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'redirect' => 'login.html?logout=failed'  // Add redirect URL even on failure
    ]);
    exit;
}