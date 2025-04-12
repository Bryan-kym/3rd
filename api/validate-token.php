<?php
header('Content-Type: application/json');
require_once 'config.php';
require_once 'auth.php';

try {
    // Authenticate will throw an exception if token is invalid
    $userId = authenticate();
    
    // Optionally verify additional user status
    $stmt = $conn->prepare("SELECT is_active FROM ext_users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user || !$user['is_active']) {
        throw new Exception('Account not active');
    }
    
    // Token is valid
    echo json_encode([
        'success' => true,
        'userId' => $userId
    ]);
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}