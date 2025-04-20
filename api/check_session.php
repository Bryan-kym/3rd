<?php
session_start();
require_once '../config.php';
require_once '../session_manager.php';

header('Content-Type: application/json');

try {
    // First try to get token from Authorization header
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    $token = '';
    
    if (!empty($authHeader) && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
    }
    // Fall back to session token
    elseif (!empty($_SESSION['authToken'])) {
        $token = $_SESSION['authToken'];
    }
    
    if (empty($token)) {
        throw new Exception('No session token found in headers or session');
    }

    $sessionManager = new SessionManager($conn);
    $remaining = $sessionManager->getRemainingSessionTime($token);

    echo json_encode([
        'success' => true,
        'remaining' => $remaining,
        'shouldWarn' => $remaining <= 300, // 5 minutes
        'expiresAt' => $conn->query("SELECT expires_at FROM ext_sessions WHERE token = '$token'")->fetch_assoc()['expires_at']
    ]);
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => [
            'headers' => getallheaders(),
            'session' => $_SESSION
        ]
    ]);
}


error_log("Checking session with token: $token");
error_log("Session contents: " . print_r($_SESSION, true));
error_log("Headers: " . print_r(getallheaders(), true));