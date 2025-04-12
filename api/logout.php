<?php
// api/logout.php
session_start();
require_once '../config.php';

try {
    // Get token from header
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    
    if ($token) {
        // Delete session from database
        $stmt = $conn->prepare("DELETE FROM ext_sessions WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
    }
    
    // Clear session
    session_unset();
    session_destroy();
    
    // Set logout message
    $_SESSION['auth_message'] = 'You have been successfully logged out.';
    $_SESSION['auth_message_type'] = 'success';
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}