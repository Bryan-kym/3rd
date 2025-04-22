<?php
header('Content-Type: application/json');
require_once '../auth.php';
require_once '../config.php';

try {
    $userId = authenticate();
    
    // Get total requests count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_requests
        FROM requests r
        JOIN requestors req ON r.requested_by = req.id
        WHERE req.email = (SELECT email FROM ext_users WHERE id = ?)
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $totalResult = $stmt->get_result();
    $totalData = $totalResult->fetch_assoc();
    
    // Get counts by statusaas
    $statusStmt = $conn->prepare("
    SELECT 
        SUM(CASE WHEN r.request_status = 'pending' THEN 1 ELSE 0 END) as pending_request,
        SUM(CASE WHEN r.request_status = 'approved' THEN 1 ELSE 0 END) as approved_requests,
        SUM(CASE WHEN r.request_status = 'resolved' THEN 1 ELSE 0 END) as resolved_requests,
        SUM(CASE WHEN r.request_status = 'reviewed' THEN 1 ELSE 0 END) as reviewed_requests,
        SUM(CASE WHEN r.request_status = 'assigned' THEN 1 ELSE 0 END) as assigned_requests,
        SUM(CASE WHEN r.request_status = 'rejected' THEN 1 ELSE 0 END) as rejected_requests,
        SUM(CASE WHEN r.request_status = 'requested' THEN 1 ELSE 0 END) as requested_requests,
        SUM(CASE WHEN r.request_status IN ('pending', 'approved', 'reviewed', 'assigned', 'requested') THEN 1 ELSE 0 END) as pending_requests
    FROM requests r
    JOIN requestors req ON r.requested_by = req.id
    WHERE req.email = (SELECT email FROM ext_users WHERE id = ?)
");
    $statusStmt->bind_param("i", $userId);
    $statusStmt->execute();
    $statusResult = $statusStmt->get_result();
    $statusData = $statusResult->fetch_assoc();
    
    // Get change percentages (example - you'll need to implement your actual change calculation)
    $changeStmt = $conn->prepare("
        SELECT 
            /* These would be your actual change calculations */
            5 as total_change,
            12 as approved_change,
            -3 as pending_change,
            8 as processing_change
        FROM dual
    ");
    $changeStmt->execute();
    $changeResult = $changeStmt->get_result();
    $changeData = $changeResult->fetch_assoc();
    
    // Combine all data
    $stats = array_merge($totalData, $statusData, $changeData);
    
    echo json_encode(['success' => true, ...$stats]);
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}