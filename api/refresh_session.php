<?php
require_once '../config.php';
require_once '../auth.php';
require_once '../session_manager.php';

header('Content-Type: application/json');

try {
    // Verify the request has a valid token
    $userId = authenticate();
    $token = $_SESSION['authToken'] ?? (isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '');
    
    // Initialize session manager
    $sessionManager = new SessionManager($conn);
    $newExpiration = $sessionManager->extendSession($token);
    
    echo json_encode([
        'success' => true,
        'message' => 'Session extended',
        'newExpiration' => $newExpiration
    ]);
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>