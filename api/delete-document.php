<?php
require_once '../config.php';
require_once '../auth.php';

header('Content-Type: application/json');

try {
    $userId = authenticate();
    
    $input = json_decode(file_get_contents('php://input'), true);
    $docId = $input['document_id'] ?? null;
    $requestId = $input['request_id'] ?? null;

    if (!$docId || !$requestId) {
        throw new Exception('Missing required parameters');
    }

    // Verify the document belongs to the user's request
    $stmt = $conn->prepare("
        SELECT rd.id 
        FROM requestors_documents rd
        JOIN requests r ON rd.request_id = r.id
        JOIN requestors req ON r.requested_by = req.id
        JOIN ext_users u ON req.email = u.email
        WHERE rd.id = ? AND r.id = ? AND u.id = ?
    ");
    $stmt->bind_param("iii", $docId, $requestId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Document not found or access denied');
    }

    // Get file path before deleting
    $fileStmt = $conn->prepare("SELECT document_file_path FROM requestors_documents WHERE id = ?");
    $fileStmt->bind_param("i", $docId);
    $fileStmt->execute();
    $fileResult = $fileStmt->get_result();
    $filePath = $fileResult->fetch_assoc()['document_file_path'];

    // Delete from database
    $deleteStmt = $conn->prepare("DELETE FROM requestors_documents WHERE id = ?");
    $deleteStmt->bind_param("i", $docId);
    $deleteStmt->execute();

    // Delete the actual file (optional)
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}