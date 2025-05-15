<?php
header('Content-Type: application/json');
require_once '../auth.php';
require_once '../config.php';

try {
    $userId = authenticate();
    $requestId = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
    
    if (!$requestId) {
        throw new Exception('Invalid request ID');
    }

    // Validate user owns this request
    $stmt = $conn->prepare("
        SELECT r.id 
        FROM requests r
        JOIN requestors req ON r.requested_by = req.id
        WHERE r.id = ? AND req.email = (SELECT email FROM ext_users WHERE id = ?)
        AND r.request_status in ('pending', 'rejected')
    ");
    $stmt->bind_param("ii", $requestId, $userId);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        throw new Exception('Request not found or cannot be edited');
    }

    // Get and sanitize input
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $specificFields = filter_input(INPUT_POST, 'specific_fields', FILTER_SANITIZE_STRING);
    $requestPurpose = filter_input(INPUT_POST, 'request_purpose', FILTER_SANITIZE_STRING);

    // Update request
    $updateStmt = $conn->prepare("
        UPDATE requests 
        SET description = ?, specific_fields = ?, request_purpose = ?, request_status = 'resubmitted', tracking_status = 'resubmitted'
        WHERE id = ?
    ");
    $updateStmt->bind_param("sssi", $description, $specificFields, $requestPurpose, $requestId);
    $updateStmt->execute();

    echo json_encode(['success' => true, 'message' => 'Request updated successfully']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}