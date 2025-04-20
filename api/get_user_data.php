<?php
require_once '../config.php';
require_once '../auth.php';

header('Content-Type: application/json');

try {
    $userId = authenticate();
    
    $stmt = $conn->prepare("SELECT surname, other_names AS othernames, email, phone, kra_pin FROM ext_users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    
    if (!$userData) {
        throw new Exception('User not found');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $userData
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => true
    ]);
}