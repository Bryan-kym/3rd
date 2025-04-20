<?php
header('Content-Type: application/json');
require_once 'config.php';

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
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}