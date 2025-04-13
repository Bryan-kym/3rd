<?php
header('Content-Type: application/json');
require_once '../auth.php';
require_once '../config.php';

try {
    $userId = authenticate();
    $requestId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if (!$requestId) {
        throw new Exception('Invalid request ID');
    }

    $stmt = $conn->prepare("
        SELECT r.*, req.* 
        FROM requests r
        JOIN requestors req ON r.requested_by = req.id
        WHERE r.id = ? AND req.email = (SELECT email FROM ext_users WHERE id = ?)
    ");
    $stmt->bind_param("ii", $requestId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Request not found or access denied');
    }
    
    $requestData = $result->fetch_assoc();
    echo json_encode($requestData);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}